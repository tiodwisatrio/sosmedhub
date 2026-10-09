@props(['post' => null, 'socialAccounts'])

{{--
    Form buat dan ubah jadwal: akun tujuan, format (Feed, Story, Reels), media per format,
    caption, waktu terbit, dan pratinjau. Satu komponen untuk dua halaman supaya tidak diduplikasi.
--}}

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Modules\Scheduler\Models\ScheduledPost;
    use Modules\Scheduler\Services\VideoSpec;

    $isEdit = $post !== null;
    // Instagram lebih dulu, lalu Facebook; di dalam platform urut nama. Akun pertama menjadi pilihan awal.
    $socialAccounts = $socialAccounts
        ->sortBy(fn ($account) => [$account->platform === 'instagram' ? 0 : 1, strtolower((string) $account->username)])
        ->values();
    $minWib = ScheduledPost::nextSlot()->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i');

    if ($isEdit) {
        // Waktu yang sudah lewat atau di luar slot diganti slot terdekat berikutnya.
        $editAt = $post->scheduled_at->isPast()
            ? ScheduledPost::nextSlot()
            : (ScheduledPost::isOnSlot($post->scheduled_at)
                ? $post->scheduled_at
                : ScheduledPost::nextSlot($post->scheduled_at->copy()->subMinute()));
        $defaultAt = $editAt->copy()->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i');
    } else {
        $requestedDate = (string) request('date');
        $defaultAt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)
            ? Carbon::parse($requestedDate, ScheduledPost::WIB)->setTime(9, 0)->format('Y-m-d\TH:i')
            : $minWib;
    }

    $initialAt = (string) old('scheduled_at', $defaultAt);
    if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $initialAt)) {
        $initialAt = $minWib;
    }
    [$initDate, $initTime] = explode('T', $initialAt);
    [$initHour, $initMinute] = explode(':', $initTime);

    $appName = $siteSetting->app_name ?? config('app.name');
    $siteLogo = $siteSetting->icon ?? $siteSetting->logo_atas ?? null;
    $siteLogoUrl = $siteLogo ? Storage::url($siteLogo) : null;
    $instaName = $siteSetting->instagram_nama ?? 'Instagram';
    $appInitial = strtoupper(Str::substr($appName, 0, 1));
    $selectedSocialAccountId = old('social_account_id', $post?->social_account_id ?? $socialAccounts->first()?->id);
    $selectedSocialAccount = $socialAccounts->firstWhere('id', (int) $selectedSocialAccountId) ?: $post?->socialAccount;
    $previewAccountUsername = $selectedSocialAccount?->username ?: $instaName;

    // Format: nilai lama (setelah validasi gagal) diutamakan; format yang sudah terbit terkunci.
    $locked = $isEdit ? $post->publications->where('status', 'published')->pluck('format')->values()->all() : [];
    $formatsOld = old('formats');
    $formats = is_array($formatsOld)
        ? array_values(array_intersect(ScheduledPost::FORMATS, array_map('strval', $formatsOld)))
        : ($isEdit ? $post->formats() : [ScheduledPost::FORMAT_FEED]);
    $formats = array_values(array_unique([...($formats ?: [ScheduledPost::FORMAT_FEED]), ...$locked]));
    usort($formats, fn ($a, $b) => array_search($a, ScheduledPost::FORMATS) <=> array_search($b, ScheduledPost::FORMATS));

    $reelPublication = $isEdit ? $post->publications->firstWhere('format', ScheduledPost::FORMAT_REEL) : null;
    $shareToFeed = old('share_to_feed') !== null ? (bool) old('share_to_feed') : ($reelPublication?->share_to_feed ?? true);

    $existing = ['feed' => [], 'story' => [], 'reel' => []];
    foreach ($isEdit ? $post->media : [] as $item) {
        if ($item->media_path && Storage::disk('public')->exists($item->media_path)) {
            $existing[in_array($item->format, ScheduledPost::FORMATS, true) ? $item->format : 'feed'][] = [
                'id' => $item->id,
                'url' => Storage::url($item->media_path),
                'type' => $item->isVideo() ? 'video' : 'image',
            ];
        }
    }

    $composerConfig = [
        'accountId' => (string) $selectedSocialAccountId,
        // Data akun untuk pratinjau; berubah mengikuti kartu akun yang dipilih.
        'accounts' => $socialAccounts->mapWithKeys(fn ($account) => [(string) $account->id => [
            'platform' => $account->platform,
            'name' => $account->platform === 'facebook' ? ($account->display_name ?: $account->username) : $account->username,
            'avatar' => $account->avatar_url,
        ]])->all(),
        'fallbackName' => $previewAccountUsername,
        'formats' => $formats,
        'locked' => $locked,
        'shareToFeed' => $shareToFeed,
        'caption' => old('caption', $post?->caption ?? ''),
        'schedDate' => $initDate,
        'schedHour' => $initHour,
        'schedMinute' => $initMinute,
        'existing' => $existing,
        'limits' => [
            'photoMb' => (int) config('scheduler.video.photo_max_mb', 8),
            'videoMinSeconds' => (int) config('scheduler.video.min_seconds', 3),
            'feed' => ['max' => 10],
            'story' => ['max' => 10, 'videoSeconds' => VideoSpec::maxSeconds('story'), 'videoMb' => (int) config('scheduler.video.story_max_mb', 100)],
            'reel' => ['max' => 1, 'videoSeconds' => VideoSpec::maxSeconds('reel'), 'videoMb' => (int) config('scheduler.video.reel_max_mb', 300)],
        ],
    ];

    $action = $isEdit ? route('admin.scheduled-posts.update', $post) : route('admin.scheduled-posts.store');
@endphp

<x-scheduler::composer-script />

<div x-data="postComposer(@js($composerConfig))">
    <div class="mx-auto">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid lg:grid-cols-12 gap-6 items-start">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            {{-- Kolom kiri: pengaturan konten --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-border bg-white">
                        <div class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary text-white text-[11px] font-semibold">1</span>
                            <h2 class="text-sm font-semibold text-slate-800">Konten</h2>
                        </div>
                        @if ($isEdit)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-light text-primary">
                                <span class="w-1 h-1 rounded-full bg-primary"></span> {{ $post->statusLabel() }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400" x-text="isFacebook ? 'Facebook' : 'Instagram'">Instagram</span>
                        @endif
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Akun tujuan --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Akun Tujuan <span class="text-danger ml-0.5">*</span>
                            </label>
                            @if ($socialAccounts->isEmpty())
                                <div class="rounded-lg border border-warning/30 bg-warning-light px-4 py-3 text-sm text-warning-text">
                                    Belum ada akun sosial aktif. Tambahkan akun dulu sebelum menjadwalkan postingan.
                                </div>
                            @else
                                <div class="grid gap-2 sm:grid-cols-2" role="radiogroup" aria-label="Akun tujuan">
                                    @foreach ($socialAccounts as $account)
                                        @php
                                            $isFacebookAccount = $account->platform === 'facebook';
                                            $accountName = $isFacebookAccount ? ($account->display_name ?: $account->username) : '@'.$account->username;
                                        @endphp
                                        <label class="relative flex cursor-pointer items-center gap-3 rounded-lg border px-3.5 py-3 transition-colors duration-150 focus-within:ring-2 focus-within:ring-primary/30"
                                            :class="accountId === '{{ $account->id }}' ? 'border-primary bg-primary-light/40' : 'border-border bg-white hover:border-primary/40 hover:bg-slate-50'">
                                            <input type="radio" name="social_account_id" value="{{ $account->id }}" x-model="accountId" class="sr-only">

                                            <span class="relative shrink-0">
                                                @if ($account->avatar_url)
                                                    <img src="{{ $account->avatar_url }}" alt="" class="h-10 w-10 rounded-full object-cover bg-slate-100" referrerpolicy="no-referrer">
                                                @else
                                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-500">
                                                        {{ strtoupper(Str::substr(ltrim($accountName, '@'), 0, 1)) }}
                                                    </span>
                                                @endif
                                                <x-social-account::brand-icon :platform="$account->platform" size="sm" class="!h-[18px] !w-[18px] !rounded-full absolute -bottom-1 -right-1 ring-2 ring-white [&_svg]:!h-2.5 [&_svg]:!w-2.5" />
                                            </span>

                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-slate-800">{{ $accountName }}</span>
                                                <span class="block text-xs text-slate-500">{{ $isFacebookAccount ? 'Facebook Page' : 'Instagram' }}</span>
                                            </span>

                                            <span x-show="accountId === '{{ $account->id }}'" x-cloak
                                                class="absolute right-2.5 top-2.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-white" aria-hidden="true">
                                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('social_account_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        {{-- Format --}}
                        <x-scheduler::format-picker />

                        {{-- Media per format --}}
                        <div class="space-y-4">
                            <x-scheduler::media-uploader format="feed" />
                            <x-scheduler::media-uploader format="story" />
                            <x-scheduler::media-uploader format="reel" />

                            @if ($isEdit)
                                <template x-for="removedId in removedIds" :key="removedId">
                                    <input type="hidden" name="remove_media[]" :value="removedId">
                                </template>
                            @endif
                        </div>

                        {{-- Caption --}}
                        <div>
                            <div class="flex items-baseline justify-between">
                                <label for="caption" class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Caption <span x-show="has('feed')" x-cloak class="text-danger ml-0.5">*</span>
                                </label>
                                <span x-show="usesCaption" class="text-xs tabular-nums" :class="counterClass" x-text="remaining + ' karakter tersisa'"></span>
                            </div>

                            <div x-show="usesCaption">
                                <x-admin.textarea
                                    name="caption"
                                    rows="6"
                                    x-model="caption"
                                    placeholder="Tulis caption yang akan menjadi teks postingan…"
                                ></x-admin.textarea>
                                <p class="mt-1.5 text-xs text-slate-400" x-show="has('story')" x-cloak>
                                    Caption dipakai untuk Feed dan Reels. Story tidak mendukung caption.
                                </p>
                            </div>

                            <p x-show="! usesCaption" x-cloak class="rounded-lg border border-border bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                Story tidak memakai caption. Bila ingin ada teks di Story, tulis langsung pada gambar atau videonya.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Penjadwalan --}}
                <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-border bg-white">
                        <div class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary text-white text-[11px] font-semibold">2</span>
                            <h2 class="text-sm font-semibold text-slate-800">Penjadwalan</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Waktu Indonesia Barat
                        </span>
                    </div>

                    <div class="p-5 space-y-5">
                        <x-scheduler::schedule-picker :min="$minWib" />
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="flex items-center gap-3">
                    <x-admin.button type="submit" :disabled="$socialAccounts->isEmpty()">{{ $isEdit ? 'Simpan Perubahan' : 'Jadwalkan Terbit' }}</x-admin.button>
                    <a href="{{ route('admin.scheduled-posts.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </div>

            {{-- Kolom kanan: pratinjau dan ringkasan --}}
            <div class="lg:col-span-5">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <x-scheduler::post-preview
                        :username="$previewAccountUsername"
                        :app-name="$appName"
                        :app-initial="$appInitial"
                        :logo-url="$siteLogoUrl" />
                </div>
            </div>
        </form>
    </div>
</div>
