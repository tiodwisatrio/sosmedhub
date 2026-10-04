@extends('layouts.admin')

@section('title', 'Penjadwalan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Penjadwalan</h1>
@endsection

@section('content')
    @php
        $blockEnd = $blockStart->copy()->addDays(27);
        $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    @endphp

    <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden mb-8"
        x-data="{
            posts: @js($postsForModal),
            open: false,
            selected: null,
            mediaIndex: 0,
            openPost(id) {
                this.selected = this.posts.find(post => post.id === id) ?? null;
                this.mediaIndex = 0;
                this.open = this.selected !== null;
            },
            close() {
                this.open = false;
            },
            confirmCancel(event) {
                if (! confirm('Batalkan postingan ini?')) {
                    event.preventDefault();
                }
            },
            confirmDelete(event) {
                if (! confirm('Hapus postingan ini?')) {
                    event.preventDefault();
                }
            },
        }"
    >

        {{-- ================================================================
             Toolbar blok 4 minggu
             ================================================================ --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-b border-border">
            <div class="flex items-center gap-1">
                <a href="{{ route('admin.scheduled-posts.index', ['week' => $previousBlock->toDateString()]) }}"
                    title="Blok sebelumnya"
                    class="p-2 rounded-lg border border-border text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </a>
                <a href="{{ route('admin.scheduled-posts.index', ['week' => $nextBlock->toDateString()]) }}"
                    title="Blok berikutnya"
                    class="p-2 rounded-lg border border-border text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>

                <div class="mx-2 pl-2 border-l border-border">
                    <p class="text-sm font-semibold text-slate-800 leading-tight">
                        {{ $blockStart->format('d M') }} — {{ $blockEnd->format('d M Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.scheduled-posts.index', ['week' => $currentBlock->toDateString()]) }}"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium border border-border text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors {{ $blockStart->isSameDay($currentBlock) ? 'opacity-40 pointer-events-none' : '' }}">
                    Hari Ini
                </a>
            </div>

            <div class="flex items-center gap-3">
                @can('scheduler.create')
                    <a href="{{ route('admin.scheduled-posts.create') }}" class="inline-flex">
                        <x-admin.button>
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Jadwalkan
                        </x-admin.button>
                    </a>
                @endcan
            </div>
        </div>

        {{-- ================================================================
             Grid 7 kolom x 4 baris
             ================================================================ --}}
        <div class="overflow-x-auto scrollbar-none">
            <div class="min-w-[980px]">

                {{-- Baris kepala hari --}}
                <div class="grid grid-cols-7 divide-x divide-border border-b border-border bg-slate-50/80">
                    @foreach ($dayNames as $index => $name)
                        <div class="px-3 py-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wider {{ $index === 5 || $index === 6 ? 'text-slate-400' : 'text-slate-500' }}">
                                {{ $name }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- 4 baris minggu --}}
                @foreach ($weeks as $days)
                    <div class="grid grid-cols-7 divide-x divide-border {{ !$loop->last ? 'border-b border-border' : '' }}">
                        @foreach ($days as $cell)
                            @php
                                $isWeekend = $cell['date']->dayOfWeekIso >= 6;
                            @endphp
                            <div class="group/date flex flex-col min-h-[13rem] transition-colors
                                {{ $cell['isToday'] ? 'bg-primary-light/25 ring-1 ring-inset ring-primary/40' : ($isWeekend ? 'bg-slate-50/50' : 'bg-white') }}">
                                {{-- Kepala tanggal --}}
                                <div class="flex items-center justify-between px-3 pt-2.5 pb-2
                                    border-b {{ $cell['isToday'] ? 'border-primary/25' : 'border-border/80' }}">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-semibold tabular-nums {{ $cell['isToday'] ? 'text-primary' : 'text-slate-800' }}">
                                            {{ $cell['date']->format('d') }}
                                        </span>
                                        <span class="text-[10px] font-medium uppercase tracking-wide {{ $isWeekend ? 'text-slate-400' : 'text-slate-400' }}">
                                            {{ $cell['date']->format('M') }}
                                        </span>
                                    </div>
                                    @if ($cell['isToday'])
                                        <span class="px-1.5 py-0.5 rounded-full bg-primary text-white text-[9px] font-semibold">Hari Ini</span>
                                    @endif
                                </div>

                                {{-- Baki konten tanggal --}}
                                <div class="flex flex-col flex-1 min-h-0 p-1.5 space-y-1.5">
                                    @forelse ($cell['posts'] as $post)
                                        <button type="button" @click="openPost({{ $post->id }})"
                                            class="relative block w-full flex-1 min-h-[5.5rem] text-left rounded-lg border border-border bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 hover:border-primary/40 hover:shadow-dropdown transition-all duration-150 cursor-pointer">
                                            @if ($post->thumbnail_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($post->thumbnail_path))
                                                <img src="{{ Storage::url($post->thumbnail_path) }}" alt="Foto postingan"
                                                    class="absolute inset-0 w-full h-full object-cover">
                                            @else
                                                <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                    </svg>
                                                </div>
                                            @endif

                                            <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-900/75 text-white text-[10px] font-semibold tabular-nums backdrop-blur-sm">
                                                {{ $post->scheduled_at->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->format('H:i') }}
                                            </span>

                                            <span class="absolute right-1.5 top-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-white/90 text-primary backdrop-blur-sm">
                                                <span class="w-1 h-1 rounded-full bg-primary"></span>
                                                Terjadwal
                                            </span>

                                            @if ($post->media->count() > 1)
                                                <span class="absolute bottom-1.5 right-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-900/75 text-white text-[10px] font-semibold tabular-nums backdrop-blur-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                    </svg>
                                                    {{ $post->media->count() }}
                                                </span>
                                            @endif
                                        </button>
                                    @empty
                                        <a href="{{ route('admin.scheduled-posts.create', ['date' => $cell['date']->toDateString()]) }}"
                                            title="Jadwalkan pada {{ $cell['date']->format('d M Y') }}"
                                            class="flex flex-1 min-h-[4rem] items-center justify-center rounded-lg border border-dashed border-border text-slate-300
                                                opacity-0 group-hover/date:opacity-100 group-hover/date:border-primary/40 group-hover/date:text-primary
                                                focus:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                        </a>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kaki kartu: legenda --}}
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 sm:px-6 py-3 border-t border-border bg-slate-50/60">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Status</span>
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-primary"></span> Terjadwal
            </span>
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-warning"></span> Menerbitkan
            </span>
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-success"></span> Terbit
            </span>
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-danger"></span> Gagal
            </span>
        </div>

        {{-- ================================================================
             Modal detail postingan
             ================================================================ --}}
        <div x-show="open" x-cloak @keydown.escape.window="close()"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4">
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-900/50" @click="close()"></div>

            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full sm:max-w-lg bg-card rounded-t-2xl sm:rounded-2xl border border-border shadow-dropdown overflow-hidden"
                @click.outside="close()">
                <template x-if="selected">
                    <div>
                        <div class="relative h-72 bg-slate-100">
                            <template x-if="selected.media.length > 0">
                                <img :src="selected.media[mediaIndex]" alt="Foto postingan"
                                    class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            <template x-if="selected.media.length === 0">
                                <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                </div>
                            </template>

                            <div class="absolute inset-0 flex items-center justify-between" x-show="selected.media.length > 1">
                                <button type="button" @click="mediaIndex = (mediaIndex - 1 + selected.media.length) % selected.media.length"
                                    class="ml-3 p-1.5 rounded-full bg-slate-900/50 text-white hover:bg-slate-900/75 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                                    </svg>
                                </button>
                                <button type="button" @click="mediaIndex = (mediaIndex + 1) % selected.media.length"
                                    class="mr-3 p-1.5 rounded-full bg-slate-900/50 text-white hover:bg-slate-900/75 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </button>
                            </div>

                            <span x-show="selected.media.length > 1"
                                class="absolute top-2.5 left-3 px-1.5 py-0.5 rounded-full bg-slate-900/60 text-white text-[11px] font-semibold tabular-nums"
                                x-text="(mediaIndex + 1) + ' / ' + selected.media.length"></span>
                            <span class="absolute bottom-2.5 left-3 px-1.5 py-0.5 rounded bg-slate-900/75 text-white text-xs font-semibold tabular-nums"
                                x-text="selected.scheduled_at"></span>
                            <button type="button" @click="close()"
                                class="absolute top-2.5 right-2.5 p-1.5 rounded-full bg-slate-900/60 text-white hover:bg-slate-900/80 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="p-5">
                            <p class="text-[15px] text-slate-700 leading-relaxed whitespace-pre-line" x-text="selected.caption"></p>

                            <dl class="mt-4 pt-4 border-t border-border space-y-3 text-[15px]">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Jadwal</dt>
                                    <dd class="font-medium text-slate-700" x-text="selected.scheduled_at"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-primary-light text-primary">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                            <span x-text="selected.status"></span>
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Dibuat</dt>
                                    <dd class="font-medium text-slate-700" x-text="selected.created_at"></dd>
                                </div>
                            </dl>

                            <div class="mt-5 flex items-center gap-2.5">
                                @can('scheduler.edit')
                                    <a :href="'/admin/scheduled-posts/' + selected.id + '/edit'"
                                        class="inline-flex items-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 px-4 py-2 text-sm border border-border hover:bg-slate-50 text-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                    </svg>
                                    Ubah
                                </a>
                                    <form :action="'/admin/scheduled-posts/' + selected.id + '/cancel'" method="POST" @submit="confirmCancel($event)">
                                        @csrf
                                        <input type="hidden" name="_method" value="PATCH">
                                        <x-admin.button variant="danger">
                                            Batalkan
                                        </x-admin.button>
                                    </form>
                                @endcan
                                @can('scheduler.create')
                                    <form :action="'/admin/scheduled-posts/' + selected.id + '/duplicate'" method="POST">
                                        @csrf
                                        <x-admin.button variant="outline" type="submit">Duplikat</x-admin.button>
                                    </form>
                                @endcan
                                @can('scheduler.delete')
                                    <form :action="'/admin/scheduled-posts/' + selected.id" method="POST" @submit="confirmDelete($event)" class="ml-auto">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <x-admin.button variant="outline" type="submit">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                            Hapus
                                        </x-admin.button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ================================================================
         Ringkasan riwayat
         ================================================================ --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-card rounded-2xl shadow-card border border-border px-6 py-4 mb-8">
        @if ($failedCount > 0)
            <p class="text-sm text-danger-text">
                <span class="font-semibold">{{ $failedCount }}</span> postingan gagal terbit dan perlu dijadwalkan ulang.
            </p>
        @else
            <p class="text-sm text-slate-500">Postingan yang sudah terbit, gagal, atau dibatalkan ada di halaman Riwayat.</p>
        @endif

        <a href="{{ route('admin.post-history.index', $failedCount > 0 ? ['status' => 'failed'] : []) }}"
            class="inline-flex items-center gap-1.5 font-medium rounded-md px-4 py-2 text-sm border border-border hover:bg-slate-50 text-slate-700">
            {{ $failedCount > 0 ? 'Lihat yang gagal' : 'Buka Riwayat' }}
        </a>
    </div>
@endsection
