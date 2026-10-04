@extends('layouts.admin')

@section('title', 'Ubah Postingan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Ubah Postingan</h1>
@endsection

@php
    // Waktu yang sudah lewat atau di luar slot diganti slot terdekat berikutnya.
    $editAt = $post->scheduled_at->isPast()
        ? \Modules\Scheduler\Models\ScheduledPost::nextSlot()
        : (\Modules\Scheduler\Models\ScheduledPost::isOnSlot($post->scheduled_at)
            ? $post->scheduled_at
            : \Modules\Scheduler\Models\ScheduledPost::nextSlot($post->scheduled_at->copy()->subMinute()));
    $editWib = $editAt->copy()->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('Y-m-d\TH:i');
    $minWib = \Modules\Scheduler\Models\ScheduledPost::nextSlot()->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('Y-m-d\TH:i');
    $initialAt = (string) old('scheduled_at', $editWib);
    if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $initialAt)) {
        $initialAt = $minWib;
    }
    [$initDate, $initTime] = explode('T', $initialAt);
    [$initHour, $initMinute] = explode(':', $initTime);

    $appName = $siteSetting->app_name ?? config('app.name');
    $siteLogo = $siteSetting->icon ?? $siteSetting->logo_atas ?? null;
    $siteLogoUrl = $siteLogo ? \Illuminate\Support\Facades\Storage::url($siteLogo) : null;
    $instaName = $siteSetting->instagram_nama ?? 'Instagram';
    $appInitial = strtoupper(\Illuminate\Support\Str::substr($appName, 0, 1));
    $socialAccountOptions = $socialAccounts->mapWithKeys(fn ($account) => [$account->id => $account->label()])->toArray();
    $selectedSocialAccountId = old('social_account_id', $post->social_account_id);
    $selectedSocialAccount = $socialAccounts->firstWhere('id', (int) $selectedSocialAccountId) ?: $post->socialAccount;
    $previewAccountName = $selectedSocialAccount?->display_name ?: $selectedSocialAccount?->username ?: $appName;
    $previewAccountUsername = $selectedSocialAccount?->username ?: $instaName;
@endphp

@php
    $existingMediaList = $post->media
        ->filter(fn ($item) => \Illuminate\Support\Facades\Storage::disk('public')->exists($item->media_path))
        ->map(fn ($item) => ['id' => $item->id, 'url' => \Illuminate\Support\Facades\Storage::url($item->media_path)])
        ->values()
        ->all();
@endphp

@section('content')
    @if ($post->isFailed())
        <div class="mb-4 rounded-md border border-danger/30 bg-danger-light px-4 py-3 text-sm text-danger-text">
            <p class="font-medium">Postingan ini gagal terbit.</p>
            @if ($post->error_message)
                <p class="mt-1">{{ $post->error_message }}</p>
            @endif
            <p class="mt-1">Periksa isinya, pilih waktu terbit yang baru, lalu simpan untuk menjadwalkan ulang.</p>
        </div>
    @elseif ($post->status === \Modules\Scheduler\Models\ScheduledPost::STATUS_DRAFT)
        <div class="mb-4 rounded-md border border-border bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Ini draf hasil duplikasi. Atur waktu terbit lalu simpan agar masuk antrean.
        </div>
    @endif

    <div
        x-data="{
            caption: @js(old('caption', $post->caption)),
            schedDate: @js($initDate),
            schedHour: @js($initHour),
            schedMinute: @js($initMinute),
            maxChars: 2200,
            maxFiles: 10,
            existing: @js($existingMediaList),
            mediaFiles: [],
            mediaPreviews: [],
            removedIds: [],
            currentIndex: 0,

            pad(n) {
                return String(n).padStart(2, '0');
            },
            parseAt(value) {
                const d = new Date(value.replace('T', ' '));
                return isNaN(d) ? null : d;
            },
            get scheduled_at() {
                return this.schedDate ? `${this.schedDate}T${this.schedHour}:${this.schedMinute}` : '';
            },
            get remaining() {
                return this.maxChars - (this.caption?.length || 0);
            },
            get counterClass() {
                if (this.remaining < 0) return 'text-danger font-semibold';
                if (this.remaining <= 200) return 'text-warning font-semibold';
                return 'text-slate-400';
            },
            get schedulePreview() {
                const d = this.parseAt(this.scheduled_at || '');
                if (! d) return 'Belum diatur';
                const tgl = d.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                });
                return `${tgl} · ${this.pad(d.getHours())}:${this.pad(d.getMinutes())} WIB`;
            },
            get currentCount() {
                return this.existing.length + this.mediaFiles.length;
            },
            get photoCountLabel() {
                return this.currentCount === 0 ? 'Tidak ada foto' : `${this.currentCount} foto`;
            },
            get previewList() {
                return [...this.existing.map((item) => item.url), ...this.mediaPreviews];
            },
            handleFiles(event) {
                const incoming = Array.from(event.target.files || []);
                const slot = this.maxFiles - this.currentCount;
                if (slot <= 0) return;
                this.mediaFiles = [...this.mediaFiles, ...incoming].slice(0, slot);
                this.syncInput();
            },
            syncInput() {
                const dt = new DataTransfer();
                this.mediaFiles.forEach((file) => dt.items.add(file));
                this.$refs.mediaInput.files = dt.files;
                this.mediaPreviews = this.mediaFiles.map((file) => URL.createObjectURL(file));
                this.clampIndex();
            },
            removeExisting(index) {
                const item = this.existing[index];
                if (! item) return;
                this.removedIds.push(item.id);
                this.existing.splice(index, 1);
                this.clampIndex();
            },
            removeNew(index) {
                if (this.mediaPreviews[index]) URL.revokeObjectURL(this.mediaPreviews[index]);
                this.mediaFiles.splice(index, 1);
                this.syncInput();
            },
            clampIndex() {
                this.currentIndex = Math.min(this.currentIndex, Math.max(this.previewList.length - 1, 0));
            },
        }"
    >
        <div class="mx-auto">
            <form method="POST" action="{{ route('admin.scheduled-posts.update', $post) }}" enctype="multipart/form-data"
                class="grid lg:grid-cols-12 gap-6 items-start">
                @csrf
                @method('PUT')

                {{-- ============================================================
                     Kolom kiri: pengaturan konten
                     ============================================================ --}}
                <div class="lg:col-span-7 space-y-6">
                    {{-- Card 1: Konten --}}
                    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-border bg-white">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary text-white text-[11px] font-semibold">1</span>
                                <h2 class="text-sm font-semibold text-slate-800">Konten</h2>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-light text-primary">
                                <span class="w-1 h-1 rounded-full bg-primary"></span> Terjadwal
                            </span>
                        </div>

                        <div class="p-5 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Akun Tujuan <span class="text-danger ml-0.5">*</span>
                                </label>
                                @if ($socialAccounts->isEmpty())
                                    <div class="rounded-lg border border-warning/30 bg-warning-light px-4 py-3 text-sm text-warning-text">
                                        Belum ada akun sosial aktif.
                                    </div>
                                @else
                                    <x-admin.select
                                        name="social_account_id"
                                        :options="$socialAccountOptions"
                                        :selected="$selectedSocialAccountId"
                                        placeholder="Pilih akun Instagram"
                                    />
                                @endif
                                @error('social_account_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Media --}}
                            <div>
                                <div class="flex items-baseline justify-between">
                                    <span class="block text-sm font-medium text-slate-700 mb-1.5">Foto</span>
                                    <span class="text-xs tabular-nums text-slate-400" x-text="currentCount + ' / ' + maxFiles + ' foto'"></span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <template x-for="(item, index) in existing" :key="item.id">
                                        <div class="relative aspect-square rounded-lg border border-border bg-slate-100 overflow-hidden">
                                            <img :src="item.url" alt="Foto existing" class="absolute inset-0 w-full h-full object-cover">
                                            <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/70 text-white text-[10px] font-medium tabular-nums"
                                                x-text="index + 1"></span>
                                            <button type="button" @click="removeExisting(index)"
                                                class="absolute top-1 right-1 p-1 rounded-full bg-slate-900/70 text-white hover:bg-danger transition-colors"
                                                title="Hapus foto ini">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-for="(preview, index) in mediaPreviews" :key="'new-' + index">
                                        <div class="relative aspect-square rounded-lg border border-primary/40 bg-slate-100 overflow-hidden">
                                            <img :src="preview" alt="Foto baru" class="absolute inset-0 w-full h-full object-cover">
                                            <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/70 text-white text-[10px] font-medium tabular-nums"
                                                x-text="(existing.length + index + 1)"></span>
                                            <button type="button" @click="removeNew(index)"
                                                class="absolute top-1 right-1 p-1 rounded-full bg-slate-900/70 text-white hover:bg-danger transition-colors"
                                                title="Hapus foto baru">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>

                                    <label @change="handleFiles" x-show="currentCount < maxFiles"
                                        class="flex flex-col items-center justify-center aspect-square rounded-lg border-2 border-dashed border-border bg-slate-50/50 cursor-pointer transition-colors duration-150 hover:border-primary/40 hover:bg-primary-light/20">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                        <span class="mt-1 text-xs text-slate-400">Tambah foto</span>
                                        <input x-ref="mediaInput" type="file" name="media[]" accept="image/jpeg" multiple
                                            class="sr-only" />
                                    </label>
                                </div>

                                <template x-for="removedId in removedIds" :key="removedId">
                                    <input type="hidden" name="remove_media[]" :value="removedId">
                                </template>

                                <p class="mt-2 text-xs text-slate-400">
                                    Format JPEG · maks 8 MB per foto · hingga 10 foto (carousel Instagram).
                                </p>

                                @error('media')
                                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                @enderror
                                @error('remove_media')
                                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Caption --}}
                            <div>
                                <div class="flex items-baseline justify-between">
                                    <label for="caption" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Caption <span class="text-danger ml-0.5">*</span>
                                    </label>
                                    <span class="text-xs tabular-nums" :class="counterClass" x-text="remaining + ' karakter tersisa'"></span>
                                </div>
                                <x-admin.textarea
                                    name="caption"
                                    rows="6"
                                    x-model="caption"
                                    placeholder="Tulis caption yang akan menjadi teks postingan Instagram…"
                                ></x-admin.textarea>
                                @unless($errors->has('caption'))
                                @endunless
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Penjadwalan --}}
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
                        <x-admin.button type="submit" :disabled="$socialAccounts->isEmpty()">Simpan Perubahan</x-admin.button>
                        <a href="{{ route('admin.scheduled-posts.index') }}">
                            <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                        </a>
                    </div>
                </div>

                {{-- ============================================================
                     Kolom kanan: pratinjau & ringkasan
                     ============================================================ --}}
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
@endsection
