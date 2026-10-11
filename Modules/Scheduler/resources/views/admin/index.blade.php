@extends('layouts.admin')

@section('title', __('Penjadwalan'))

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">{{ __('Penjadwalan') }}</h1>
@endsection

@section('content')
    @php
        $blockEnd = $blockStart->copy()->addDays(27);
        // Nama hari singkat mengikuti bahasa aktif; blok selalu dimulai hari Senin.
        $dayNames = collect(range(0, 6))
            ->map(fn ($offset) => $blockStart->copy()->addDays($offset)->locale(app()->getLocale())->translatedFormat('D'))
            ->all();

        // Kelas ditulis lengkap agar terbaca oleh Tailwind. Nama warna dari CalendarEvent::HUES.
        $eventPalette = [
            'red' => ['chip' => 'bg-red-100 text-red-700', 'dot' => 'bg-red-500', 'text' => 'text-red-600', 'cell' => 'bg-red-50/70'],
            'blue' => ['chip' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-500', 'text' => 'text-blue-600', 'cell' => 'bg-blue-50/70'],
            'green' => ['chip' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500', 'text' => 'text-green-600', 'cell' => 'bg-green-50/70'],
            'purple' => ['chip' => 'bg-purple-100 text-purple-700', 'dot' => 'bg-purple-500', 'text' => 'text-purple-600', 'cell' => 'bg-purple-50/70'],
            'orange' => ['chip' => 'bg-orange-100 text-orange-700', 'dot' => 'bg-orange-500', 'text' => 'text-orange-600', 'cell' => 'bg-orange-50/70'],
            'pink' => ['chip' => 'bg-pink-100 text-pink-700', 'dot' => 'bg-pink-500', 'text' => 'text-pink-600', 'cell' => 'bg-pink-50/70'],
        ];
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_20rem] gap-6 items-start mb-8"
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
                if (! confirm(@js(__('Batalkan postingan ini?')))) {
                    event.preventDefault();
                }
            },
            confirmDelete(event) {
                if (! confirm(@js(__('Hapus postingan ini?')))) {
                    event.preventDefault();
                }
            },
        }"
    >

        {{-- Kartu kalender --}}
        <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden min-w-0">

        {{-- ================================================================
             Toolbar blok 4 minggu
             ================================================================ --}}
        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-3 px-3 sm:px-6 py-3 sm:py-3.5 border-b border-border">
            <div class="flex items-center gap-1">
                <a href="{{ route('admin.scheduled-posts.index', ['week' => $previousBlock->toDateString()]) }}"
                    title="{{ __('Blok sebelumnya') }}"
                    class="p-2 rounded-lg border border-border text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </a>
                <a href="{{ route('admin.scheduled-posts.index', ['week' => $nextBlock->toDateString()]) }}"
                    title="{{ __('Blok berikutnya') }}"
                    class="p-2 rounded-lg border border-border text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            </div>

            <a href="{{ route('admin.scheduled-posts.index', ['week' => $currentBlock->toDateString()]) }}"
                title="{{ __('Kembali ke hari ini') }}"
                class="text-sm sm:text-base font-semibold text-slate-800 text-center hover:text-primary transition-colors">
                {{ $blockStart->copy()->addWeeks(2)->locale(app()->getLocale())->translatedFormat('F Y') }}
            </a>

            <div class="flex items-center justify-end">
                @can('scheduler.create')
                    <a href="{{ route('admin.scheduled-posts.create') }}" class="inline-flex" aria-label="{{ __('Buat Postingan') }}" title="{{ __('Buat Postingan') }}">
                        <x-admin.button>
                            <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span class="hidden sm:inline">{{ __('Buat Postingan') }}</span>
                        </x-admin.button>
                    </a>
                @endcan
            </div>
        </div>

        {{-- ================================================================
             Grid 7 kolom x 4 baris
             ================================================================ --}}
        <div class="overflow-x-auto scrollbar-none">
            <div class="min-w-[720px] p-3">

                {{-- Baris kepala hari --}}
                <div class="grid grid-cols-7 gap-2 mb-2">
                    @foreach ($dayNames as $index => $name)
                        <div class="px-2 py-1 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider {{ $index === 5 || $index === 6 ? 'text-rose-500' : 'text-primary' }}">
                                {{ $name }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- 4 baris minggu --}}
                @foreach ($weeks as $days)
                    <div class="grid grid-cols-7 gap-2 {{ !$loop->last ? 'mb-2' : '' }}">
                        @foreach ($days as $cell)
                            @php
                                $isWeekend = $cell['date']->dayOfWeekIso >= 6;
                                $events = $eventsByDay->get($cell['date']->toDateString(), collect());
                                $mark = $events->isNotEmpty() ? $eventPalette[$events->first()->hue] : null;
                            @endphp
                            <div class="group/date flex flex-col min-h-[6.5rem] sm:min-h-[9rem] rounded-xl border transition-all duration-150 hover:shadow-dropdown
                                {{ $cell['isToday'] ? 'bg-primary-light/30 border-primary/50' : 'border-border/70 '.($mark ? $mark['cell'] : ($isWeekend ? 'bg-slate-50/70' : 'bg-white')) }}">
                                {{-- Kepala tanggal --}}
                                <div class="flex items-center gap-1.5 px-2.5 pt-2">
                                    <span class="inline-flex items-center justify-center text-sm font-semibold tabular-nums
                                        {{ $cell['isToday'] ? 'min-w-6 h-6 px-1 rounded-full bg-primary text-white' : ($mark ? $mark['text'] : 'text-slate-700') }}">
                                        {{ $cell['date']->format('j') }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-400">
                                        {{ $cell['date']->copy()->locale(app()->getLocale())->translatedFormat('M') }}
                                    </span>
                                </div>

                                {{-- Hari libur dan hari besar --}}
                                @foreach ($events as $event)
                                    <p title="{{ __($event->name) }}"
                                        class="mx-2 mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold leading-tight truncate {{ $eventPalette[$event->hue]['chip'] }}">
                                        {{ $event->name }}
                                    </p>
                                @endforeach

                                {{-- Baki konten tanggal --}}
                                <div class="flex flex-col flex-1 min-h-0 p-2 space-y-1.5">
                                    @forelse ($cell['posts'] as $post)
                                        <button type="button" @click="openPost({{ $post->id }})"
                                            class="relative block w-full flex-1 min-h-[3.5rem] sm:min-h-[4.75rem] text-left rounded-lg border border-border/60 bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 hover:border-primary/40 hover:shadow-dropdown transition-all duration-150 cursor-pointer">
                                            @if ($post->thumbnail_path)
                                                <img src="{{ \Modules\Scheduler\Services\MediaDisk::url($post->thumbnail_path) }}" alt="{{ __('Foto postingan') }}"
                                                    class="absolute inset-0 w-full h-full object-cover">
                                            @elseif ($post->has_video_only)
                                                <div class="absolute inset-0 flex items-center justify-center bg-slate-800 text-white/70">
                                                    <x-heroicon-o-film class="w-6 h-6" />
                                                </div>
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

                                            <x-scheduler::format-badges :post="$post" size="sm" class="absolute left-1.5 top-1.5 max-w-[58%]" />

                                            <span class="absolute right-1.5 top-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-white/90 text-primary backdrop-blur-sm">
                                                <span class="w-1 h-1 rounded-full bg-primary"></span>
                                                {{ __('Terjadwal') }}
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
                                            title="{{ __('Jadwalkan pada :date', ['date' => $cell['date']->copy()->locale(app()->getLocale())->translatedFormat('j M Y')]) }}"
                                            class="flex flex-1 min-h-[2rem] sm:min-h-[3rem] items-center justify-center rounded-lg border border-dashed border-border text-slate-300
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

        </div>

        {{-- ================================================================
             Panel status postingan
             ================================================================ --}}
        <aside class="space-y-6">
            <div class="bg-primary text-white rounded-2xl shadow-card px-5 py-6 text-center">
                <p class="text-base font-semibold text-white">{{ $monthLabel }}</p>
                <p class="mt-1 text-6xl font-bold tabular-nums">{{ $monthContentCount }}</p>
                <p class="mt-1 text-sm font-medium text-white/80">{{ __('Total Postingan') }}</p>
            </div>

            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border">
                    <h2 class="text-base font-semibold text-slate-800">{{ __('Status Postingan') }}</h2>
                </div>
                @php
                    $statusRows = [
                        ['key' => 'scheduled', 'label' => __('Terjadwal'), 'color' => 'bg-primary'],
                        ['key' => 'published', 'label' => __('Terbit'), 'color' => 'bg-success'],
                        ['key' => 'publishing', 'label' => __('Menerbitkan'), 'color' => 'bg-warning'],
                        ['key' => 'partial', 'label' => __('Sebagian terbit'), 'color' => 'bg-purple-500'],
                        ['key' => 'failed', 'label' => __('Gagal'), 'color' => 'bg-danger'],
                    ];
                @endphp
                <ul class="px-5 py-2">
                    @foreach ($statusRows as $row)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <span class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <span class="w-2 h-2 rounded-full {{ $row['color'] }}"></span>
                                {{ $row['label'] }}
                            </span>
                            <span class="inline-flex items-center justify-center min-w-7 px-2 py-0.5 rounded-full text-xs font-semibold tabular-nums text-white {{ $row['color'] }}">
                                {{ $statusCounts[$row['key']] ?? 0 }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border">
                    <h2 class="text-base font-semibold text-slate-800">{{ __('Hari Penting :year', ['year' => $eventsYear]) }}</h2>
                </div>
                @if ($yearEvents->isEmpty())
                    <p class="px-5 py-4 text-sm text-slate-400">{{ __('Belum ada hari penting untuk tahun :year.', ['year' => $eventsYear]) }}</p>
                @else
                    {{-- Daftar setahun penuh; saat dibuka, gulir ke hari penting terdekat. --}}
                    <ul class="px-5 py-2 max-h-[28rem] overflow-y-auto"
                        x-data
                        x-init="$nextTick(() => { const next = $el.querySelector('[data-next]'); if (next) $el.scrollTop = next.offsetTop - $el.offsetTop; })">
                        @php $nextMarked = false; @endphp
                        @foreach ($yearEvents as $event)
                            @php
                                $today = now(\Modules\Scheduler\Models\ScheduledPost::WIB)->startOfDay();
                                // Lewat setelah hari terakhirnya; tombol tambah ke hari pertama (atau hari ini bila sudah berjalan).
                                $isPast = $event->end_date->lt($today);
                                $linkDate = $event->date->lt($today) ? $today : $event->date;
                                $color = $eventPalette[$event->hue];
                                $start = $event->date->copy()->locale(app()->getLocale());
                                $end = $event->end_date->copy()->locale(app()->getLocale());
                                $dateLabel = match (true) {
                                    $start->isSameDay($end) => $start->translatedFormat('l, d F Y'),
                                    $start->month === $end->month => $start->translatedFormat('l, d').' – '.$end->translatedFormat('l, d F Y'),
                                    default => $start->translatedFormat('d F').' – '.$end->translatedFormat('d F Y'),
                                };
                            @endphp
                            @php
                                $isNext = ! $isPast && ! $nextMarked;
                                $nextMarked = $nextMarked || $isNext;
                            @endphp
                            <li @if ($isNext) data-next @endif class="flex items-start gap-3 py-2.5 {{ $isPast ? 'opacity-50' : '' }}">
                                <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-full {{ $color['dot'] }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-slate-700 leading-snug">{{ __($event->name) }}</p>
                                    <p class="text-xs font-medium {{ $color['text'] }}">{{ $dateLabel }}</p>
                                </div>
                                @if (! $isPast)
                                    @can('scheduler.create')
                                        <a href="{{ route('admin.scheduled-posts.create', ['date' => $linkDate->toDateString()]) }}"
                                            title="{{ __('Buat postingan untuk :name', ['name' => __($event->name)]) }}"
                                            class="mt-0.5 p-1 rounded-md text-slate-400 hover:text-primary hover:bg-slate-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                        </a>
                                    @endcan
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </aside>

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
                class="relative w-full sm:max-w-lg max-h-[92vh] overflow-y-auto bg-card rounded-t-2xl sm:rounded-2xl border border-border shadow-dropdown"
                @click.outside="close()">
                <template x-if="selected">
                    <div>
                        <div class="relative h-56 sm:h-72 bg-slate-100">
                            <template x-if="selected.media.length > 0 && selected.media[mediaIndex].type !== 'video'">
                                <img :src="selected.media[mediaIndex].url" alt="{{ __('Media postingan') }}"
                                    class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            <template x-if="selected.media.length > 0 && selected.media[mediaIndex].type === 'video'">
                                <video :src="selected.media[mediaIndex].url + '#t=0.1'" controls muted playsinline preload="metadata"
                                    x-on:loadedmetadata="$el.currentTime = 0.1" class="absolute inset-0 w-full h-full object-contain bg-black"></video>
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

                            <span x-show="selected.media.length > 0"
                                class="absolute top-2.5 left-3 px-2 py-0.5 rounded-full bg-slate-900/60 text-white text-[11px] font-semibold tabular-nums"
                                x-text="selected.media.length > 0 ? (selected.media[mediaIndex].format + (selected.media.length > 1 ? ' · ' + (mediaIndex + 1) + ' / ' + selected.media.length : '')) : ''"></span>
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
                            <p x-show="selected.uses_caption" class="text-[15px] text-slate-700 leading-relaxed whitespace-pre-line" x-text="selected.caption"></p>
                            <p x-show="! selected.uses_caption" class="text-sm text-slate-400">{{ __('Story tidak memakai caption.') }}</p>

                            <dl class="mt-4 pt-4 border-t border-border space-y-3 text-[15px]">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">{{ __('Format') }}</dt>
                                    <dd class="flex flex-wrap justify-end gap-1">
                                        <template x-for="format in selected.formats" :key="format.key">
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                                :class="{ 'bg-slate-100 text-slate-700': format.key === 'feed', 'bg-fuchsia-100 text-fuchsia-800': format.key === 'story', 'bg-sky-100 text-sky-800': format.key === 'reel' }"
                                                x-text="format.label"></span>
                                        </template>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">{{ __('Jadwal') }}</dt>
                                    <dd class="font-medium text-slate-700" x-text="selected.scheduled_at"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">{{ __('Status') }}</dt>
                                    <dd>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-primary-light text-primary">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                            <span x-text="selected.status"></span>
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">{{ __('Dibuat') }}</dt>
                                    <dd class="font-medium text-slate-700" x-text="selected.created_at"></dd>
                                </div>
                            </dl>

                            <div class="mt-5 flex flex-wrap items-center gap-2.5">
                                @can('scheduler.edit')
                                    <a :href="'/admin/scheduled-posts/' + selected.id + '/edit'"
                                        class="inline-flex items-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 px-4 py-2 text-sm border border-border hover:bg-slate-50 text-slate-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                    </svg>
                                    {{ __('Ubah') }}
                                </a>
                                    <form :action="'/admin/scheduled-posts/' + selected.id + '/cancel'" method="POST" @submit="confirmCancel($event)">
                                        @csrf
                                        <input type="hidden" name="_method" value="PATCH">
                                        <x-admin.button variant="danger">
                                            {{ __('Batalkan') }}
                                        </x-admin.button>
                                    </form>
                                @endcan
                                @can('scheduler.create')
                                    <form :action="'/admin/scheduled-posts/' + selected.id + '/duplicate'" method="POST">
                                        @csrf
                                        <x-admin.button variant="outline" type="submit">{{ __('Duplikat') }}</x-admin.button>
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
                                            {{ __('Hapus') }}
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
        @if ($failedCount > 0 || $partialCount > 0)
            @php
                $bold = fn ($n) => '<span class="font-semibold">'.e($n).'</span>';
                $summary = match (true) {
                    $failedCount > 0 && $partialCount > 0 => __(':failed postingan gagal terbit, dan :partial postingan terbit sebagian. Perlu dijadwalkan ulang.', ['failed' => $bold($failedCount), 'partial' => $bold($partialCount)]),
                    $failedCount > 0 => __(':failed postingan gagal terbit dan perlu dijadwalkan ulang.', ['failed' => $bold($failedCount)]),
                    default => __(':partial postingan terbit sebagian. Perlu dijadwalkan ulang.', ['partial' => $bold($partialCount)]),
                };
            @endphp
            <p class="text-sm text-danger-text">{!! $summary !!}</p>
        @else
            <p class="text-sm text-slate-500">{{ __('Postingan yang sudah terbit, gagal, atau dibatalkan ada di halaman Riwayat.') }}</p>
        @endif

        <a href="{{ route('admin.post-history.index', $failedCount > 0 ? ['status' => 'failed'] : ($partialCount > 0 ? ['status' => 'partial'] : [])) }}"
            class="inline-flex items-center gap-1.5 font-medium rounded-md px-4 py-2 text-sm border border-border hover:bg-slate-50 text-slate-700">
            {{ $failedCount > 0 ? __('Lihat yang gagal') : ($partialCount > 0 ? __('Lihat yang sebagian') : __('Buka Riwayat')) }}
        </a>
    </div>
@endsection
