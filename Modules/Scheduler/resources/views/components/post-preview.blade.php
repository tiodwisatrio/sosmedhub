@props([
    'username',
    'appName',
    'appInitial',
    'logoUrl' => null,
])

{{--
    Pratinjau ala Instagram dan ringkasan jadwal. Dipakai halaman buat dan ubah postingan.

    Komponen ini membaca state Alpine dari elemen induknya, jadi harus berada di dalam x-data
    yang menyediakan: previewList (array URL foto), currentIndex, caption, schedulePreview,
    dan photoCountLabel (teks jumlah foto untuk ringkasan).
--}}

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
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}"
                    class="w-8 h-8 rounded-full object-cover border border-border">
            @else
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-primary-light grid place-items-center text-white text-[11px] font-bold">
                    {{ $appInitial }}
                </div>
            @endif
            <p class="truncate text-[13px] font-semibold text-slate-900">{{ $username }}</p>
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
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-5 w-5 rounded-full object-cover" />
                @else
                    <span class="grid h-5 w-5 place-items-center rounded-full bg-primary-light text-[8px] font-bold text-primary">{{ $appInitial }}</span>
                @endif
                <p>Disukai oleh <span class="font-semibold text-slate-900">{{ $username }}</span> dan <span class="font-semibold text-slate-900">21 lainnya</span></p>
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
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Profil {{ $appName }}" class="h-7 w-7 rounded-full border-2 border-rose-400 object-cover p-0.5" />
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
            <dd class="font-medium text-slate-700" x-text="photoCountLabel"></dd>
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
                {{ $username }}
            </dd>
        </div>
    </dl>
</div>
