@extends('layouts.admin')

@section('title', 'Ubah Postingan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Ubah Postingan</h1>
@endsection

@php
    $editWib = ($post->scheduled_at->isPast() ? now()->addHour() : $post->scheduled_at)
        ->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('Y-m-d\TH:i');
    $minWib = now()->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('Y-m-d\TH:i');

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
            scheduled_at: @js(old('scheduled_at', $editWib)),
            maxChars: 2200,
            maxFiles: 10,
            existing: @js($existingMediaList),
            mediaFiles: [],
            mediaPreviews: [],
            removedIds: [],
            currentIndex: 0,

            toInput(d) {
                const p = (n) => String(n).padStart(2, '0');
                return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
            },
            pad(n) {
                return String(n).padStart(2, '0');
            },
            parseAt(value) {
                const d = new Date(value.replace('T', ' '));
                return isNaN(d) ? null : d;
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
            get previewList() {
                return [...this.existing.map((item) => item.url), ...this.mediaPreviews];
            },
            pickTime(time) {
                const [h, m] = time.split(':').map(Number);
                const base = this.parseAt(this.scheduled_at || '') || new Date();
                const d = new Date(base.getFullYear(), base.getMonth(), base.getDate(), h, m, 0, 0);
                if (d <= new Date()) d.setDate(d.getDate() + 1);
                this.scheduled_at = this.toInput(d);
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
                            <div>
                                <div class="flex items-baseline justify-between">
                                    <label for="scheduled_at" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Tanggal & Jam Terbit <span class="text-danger ml-0.5">*</span>
                                    </label>
                                </div>
                                <x-admin.input-text
                                    name="scheduled_at"
                                    type="datetime-local"
                                    x-model="scheduled_at"
                                    min="{{ $minWib }}"
                                />
                            </div>

                            <div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['06:00' => 'Pagi', '09:00' => 'Jam 9', '12:00' => 'Siang', '16:00' => 'Sore', '19:00' => 'Malam', '22:00' => 'Akhir malam'] as $time => $label)
                                        <button type="button" @click="pickTime('{{ $time }}')"
                                            class="px-3 py-1.5 rounded-lg border border-border text-xs font-medium text-slate-600 hover:border-primary/40 hover:text-primary hover:bg-primary-light/30 transition-colors duration-150">
                                            {{ $label }} · {{ $time }}
                                        </button>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-slate-400">
                                    Jika jam preset sudah lewat, tanggal otomatis mundur ke hari berikutnya.
                                </p>
                            </div>
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
                        {{-- Pratinjau ala Instagram --}}
                        <div>
                            <div class="relative mx-auto w-full max-w-[360px] px-3">
                                {{-- Frame iPhone Pro: elemen dekoratif, preview tetap interaktif. --}}
                                <span aria-hidden="true" class="absolute -left-0.5 top-28 h-8 w-1 rounded-l bg-slate-500 shadow-sm"></span>
                                <span aria-hidden="true" class="absolute -left-0.5 top-40 h-14 w-1 rounded-l bg-slate-500 shadow-sm"></span>
                                <span aria-hidden="true" class="absolute -right-0.5 top-36 h-16 w-1 rounded-r bg-slate-500 shadow-sm"></span>
                                <div class="relative aspect-[9/19.5] rounded-[3.2rem] border-[7px] border-[#1c1c1e] bg-[#1c1c1e] p-[3px] shadow-[0_18px_40px_rgba(15,23,42,0.28)]">
                                    <span aria-hidden="true" class="absolute left-1/2 top-2 z-20 h-6 w-24 -translate-x-1/2 rounded-full bg-[#050505]"></span>
                                    <span aria-hidden="true" class="absolute left-[calc(50%-2.1rem)] top-[0.8rem] z-30 h-1.5 w-1.5 rounded-full bg-slate-700 ring-1 ring-slate-800"></span>
                                    <div class="relative mx-auto h-full rounded-[2.8rem] border border-border bg-white pt-7 overflow-hidden shadow-card">
                                {{-- Chrome aplikasi Instagram dalam light theme. --}}
                                <div class="absolute inset-x-0 top-0 z-10 flex h-7 items-center justify-between bg-white px-6 text-[10px] font-semibold text-slate-900">
                                    <span>11:18</span>
                                    <div class="flex items-center gap-1.5"><svg class="h-3.5 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M1 16h2V9H1v7Zm4 0h2V6H5v10Zm4 0h2V3H9v13Zm4 0h2V1h-2v15Zm4 0h2V0h-2v16Z"/></svg><svg class="h-3.5 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3 9.5a13.5 13.5 0 0 1 18 0M6.5 13a8.5 8.5 0 0 1 11 0M10 16.5a3.5 3.5 0 0 1 4 0"/></svg><span class="h-2.5 w-5 rounded-sm border border-slate-700 p-px"><span class="block h-full w-3 rounded-[1px] bg-slate-800"></span></span></div>
                                </div>
                                <div class="flex h-12 items-center justify-between border-b border-border/70 px-4 text-slate-900"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg><p class="text-base font-semibold">Postingan</p><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 10.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm6.5 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 1 0 0-3Zm6.5 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 1 0 0-3Z"/></svg></div>
                                <div class="flex items-center gap-2.5 border-b border-border/70 px-4 py-3">
                                    @if ($siteLogoUrl)
                                        <img src="{{ $siteLogoUrl }}" alt="Logo {{ $appName }}"
                                            class="w-8 h-8 rounded-full object-cover border border-border">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-primary-light grid place-items-center text-white text-[11px] font-bold">
                                            {{ $appInitial }}
                                        </div>
                                    @endif
                                    <p class="truncate text-[13px] font-semibold text-slate-900">{{ $previewAccountUsername }}</p>
                                    <span class="ml-auto flex w-5 flex-col items-end gap-1" aria-hidden="true">
                                        <span class="block h-0.5 w-5 rounded-full bg-slate-800"></span>
                                        <span class="block h-0.5 w-3.5 rounded-full bg-slate-800"></span>
                                    </span>
                                </div>

                                <div class="relative aspect-square bg-slate-100">
                                    <img x-show="previewList.length > 0" :src="previewList[currentIndex]" alt="Foto postingan"
                                        class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 grid place-items-center text-slate-300" x-show="previewList.length === 0">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                        </svg>
                                    </div>

                                    <div class="absolute inset-0 flex items-center justify-between px-2" x-show="previewList.length > 1">
                                        <button type="button" @click="currentIndex = (currentIndex - 1 + previewList.length) % previewList.length"
                                            class="p-1.5 rounded-full bg-slate-900/50 text-white hover:bg-slate-900/75 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="currentIndex = (currentIndex + 1) % previewList.length"
                                            class="p-1.5 rounded-full bg-slate-900/50 text-white hover:bg-slate-900/75 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <span x-show="previewList.length > 1"
                                        class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full bg-slate-900/60 text-white text-[10px] font-semibold tabular-nums"
                                        x-text="(currentIndex + 1) + ' / ' + previewList.length"></span>
                                </div>

                                <div class="px-4 py-3">
                                    <div class="flex items-center gap-3.5 text-slate-900">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-label="Suka"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                                        <svg class="h-6 w-6 -rotate-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-label="Komentar"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7A8.38 8.38 0 0 1 4 11.5a8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-label="Repost"><path stroke-linecap="round" stroke-linejoin="round" d="m17 2 4 4-4 4M3 11V9a3 3 0 0 1 3-3h15M7 22l-4-4 4-4m14-1v2a3 3 0 0 1-3 3H3"/></svg>
                                        <svg class="h-6 w-6 -rotate-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-label="Bagikan"><path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4L22 2Z"/><path stroke-linecap="round" d="M22 2 11 13"/></svg>
                                        <svg class="ml-auto h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-label="Simpan"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.75A2.25 2.25 0 0 1 8.25 1.5h7.5A2.25 2.25 0 0 1 18 3.75V22.5L12 18l-6 4.5V3.75Z"/></svg>
                                    </div>

                                    <div class="mt-2.5 flex items-center gap-2 text-[11px] text-slate-700">
                                        @if ($siteLogoUrl)
                                            <img src="{{ $siteLogoUrl }}" alt="{{ $appName }}" class="h-5 w-5 rounded-full object-cover" />
                                        @else
                                            <span class="grid h-5 w-5 place-items-center rounded-full bg-primary-light text-[8px] font-bold text-primary">{{ $appInitial }}</span>
                                        @endif
                                        <p>Disukai oleh <span class="font-semibold text-slate-900">{{ $previewAccountUsername }}</span> dan <span class="font-semibold text-slate-900">21 lainnya</span></p>
                                    </div>

                                    <p class="mt-2 text-[13px] text-slate-600">
                                        <span class="font-semibold text-slate-800">{{ $appName }}</span>
                                        <span x-text="caption" x-show="caption" class="whitespace-pre-line"></span>
                                        <span x-show="! caption" class="text-slate-400">Belum ada caption…</span>
                                    </p>

                                    <p class="mt-2 text-[11px] text-slate-400" x-text="schedulePreview"></p>
                                </div>
                                    <nav class="absolute inset-x-0 bottom-0 flex h-[5.5rem] items-start justify-between border-t border-slate-200 bg-white px-7 pt-3 text-slate-800">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.15" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10.5 9-7.5 9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-9Z"/></svg>
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path stroke-linecap="round" d="m16 16 4 4"/></svg>
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="4"/><path stroke-linecap="round" d="m9 9 6 3-6 3V9Z"/></svg>
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-label="Aktivitas"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                                        @if ($siteLogoUrl)
                                            <img src="{{ $siteLogoUrl }}" alt="Profil {{ $appName }}" class="h-7 w-7 rounded-full border-2 border-rose-400 object-cover p-0.5" />
                                        @else
                                            <div class="grid h-7 w-7 place-items-center rounded-full border-2 border-rose-400 bg-primary-light text-[9px] font-bold text-primary">{{ $appInitial }}</div>
                                        @endif
                                    </nav>
                                    </div>
                                </div>
                                <span aria-hidden="true" class="absolute bottom-4 left-1/2 z-20 h-1 w-24 -translate-x-1/2 rounded-full bg-slate-900/70"></span>
                            </div>
                        </div>

                        {{-- Ringkasan --}}
                        <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-border">
                                <h3 class="text-sm font-semibold text-slate-800">Ringkasan jadwal</h3>
                            </div>
                            <dl class="p-5 space-y-3.5 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Media</dt>
                                    <dd class="font-medium text-slate-700" x-text="currentCount === 0 ? 'Tidak ada foto' : (currentCount + ' foto')"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Caption</dt>
                                    <dd class="font-medium text-slate-700" x-text="caption.length + ' karakter'"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Terbit</dt>
                                    <dd class="font-medium text-slate-700 text-right" x-text="schedulePreview"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 pt-2 border-t border-border/70">
                                    <dt class="text-slate-400">Tujuan</dt>
                                    <dd class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 448 512">
                                            <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/>
                                        </svg>
                                        {{ $previewAccountUsername }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
