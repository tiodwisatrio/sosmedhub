@extends('layouts.admin')

@section('title', 'Jadwalkan Postingan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Jadwalkan Postingan</h1>
@endsection

@php
    $minWib = \Modules\Scheduler\Models\ScheduledPost::nextSlot()->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('Y-m-d\TH:i');
    $requestedDate = (string) request('date');
    $defaultDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)
        ? \Illuminate\Support\Carbon::parse($requestedDate, \Modules\Scheduler\Models\ScheduledPost::WIB)
            ->setTime(9, 0)
            ->format('Y-m-d\TH:i')
        : $minWib;
    $initialAt = (string) old('scheduled_at', $defaultDate);
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
    $selectedSocialAccountId = old('social_account_id', $socialAccounts->first()?->id);
    $selectedSocialAccount = $socialAccounts->firstWhere('id', (int) $selectedSocialAccountId);
    $previewAccountName = $selectedSocialAccount?->display_name ?: $selectedSocialAccount?->username ?: $appName;
    $previewAccountUsername = $selectedSocialAccount?->username ?: $instaName;
@endphp

@section('content')
    <div
        x-data="{
            caption: @js(old('caption', '')),
            schedDate: @js($initDate),
            schedHour: @js($initHour),
            schedMinute: @js($initMinute),
            maxChars: 2200,
            maxFiles: 10,
            mediaFiles: [],
            mediaPreviews: [],
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
            get previewList() {
                return this.mediaPreviews;
            },
            get photoCountLabel() {
                return this.mediaFiles.length === 0 ? 'Belum dipilih' : `${this.mediaFiles.length} foto`;
            },
            get schedulePreview() {
                const d = this.parseAt(this.scheduled_at || '');
                if (! d) return 'Belum diatur';
                const tgl = d.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                });
                return `${tgl} · ${this.pad(d.getHours())}:${this.pad(d.getMinutes())} WIB`;
            },
            handleFiles(event) {
                const incoming = Array.from(event.target.files || []);
                const slot = this.maxFiles - this.mediaFiles.length;
                if (slot <= 0) return;
                this.mediaFiles = [...this.mediaFiles, ...incoming].slice(0, this.maxFiles);
                this.syncInput();
            },
            syncInput() {
                const dt = new DataTransfer();
                this.mediaFiles.forEach((file) => dt.items.add(file));
                this.$refs.mediaInput.files = dt.files;
                this.mediaPreviews = this.mediaFiles.map((file) => URL.createObjectURL(file));
                this.currentIndex = Math.min(this.currentIndex, this.mediaFiles.length - 1);
            },
            removeMedia(index) {
                if (this.mediaPreviews[index]) URL.revokeObjectURL(this.mediaPreviews[index]);
                this.mediaFiles.splice(index, 1);
                this.syncInput();
            },
        }"
    >
        <div class="mx-auto">
            <form method="POST" action="{{ route('admin.scheduled-posts.store') }}" enctype="multipart/form-data"
                class="grid lg:grid-cols-12 gap-6 items-start">
                @csrf

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
                            <span class="text-xs text-slate-400">Instagram</span>
                        </div>

                        <div class="p-5 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Akun Tujuan <span class="text-danger ml-0.5">*</span>
                                </label>
                                @if ($socialAccounts->isEmpty())
                                    <div class="rounded-lg border border-warning/30 bg-warning-light px-4 py-3 text-sm text-warning-text">
                                        Belum ada akun sosial aktif. Tambahkan akun dulu sebelum menjadwalkan postingan.
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
                                    <span class="block text-sm font-medium text-slate-700 mb-1.5">Foto <span class="text-danger ml-0.5">*</span></span>
                                    <span class="text-xs tabular-nums text-slate-400" x-text="mediaFiles.length + ' / ' + maxFiles + ' foto'"></span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <template x-for="(preview, index) in mediaPreviews" :key="index">
                                        <div class="relative aspect-square rounded-lg border border-border bg-slate-100 overflow-hidden group/photos">
                                            <img :src="preview" alt="Pratinjau foto carousel" class="absolute inset-0 w-full h-full object-cover">
                                            <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-slate-900/70 text-white text-[10px] font-medium tabular-nums"
                                                x-text="index + 1"></span>
                                            <button type="button" @click="removeMedia(index)"
                                                class="absolute top-1 right-1 p-1 rounded-full bg-slate-900/70 text-white hover:bg-danger transition-colors"
                                                title="Hapus foto">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>

                                    <label @change="handleFiles" x-show="mediaFiles.length < maxFiles"
                                        class="flex flex-col items-center justify-center aspect-square rounded-lg border-2 border-dashed border-border bg-slate-50/50 cursor-pointer transition-colors duration-150 hover:border-primary/40 hover:bg-primary-light/20">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                        <span class="mt-1 text-xs text-slate-400">Tambah foto</span>
                                        <input x-ref="mediaInput" type="file" name="media[]" accept="image/jpeg" multiple
                                            :required="mediaFiles.length === 0" class="sr-only" />
                                    </label>
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    JPEG · maks 8 MB per foto · hingga 10 foto (carousel Instagram).
                                </p>

                                @error('media')
                                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                @enderror
                                @error('media.*')
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
                        <x-admin.button type="submit" :disabled="$socialAccounts->isEmpty()">Jadwalkan Terbit</x-admin.button>
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
