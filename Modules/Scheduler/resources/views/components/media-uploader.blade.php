@props(['format'])

{{--
    Unggahan media satu format. Tampil hanya bila formatnya dipilih. Berisi media yang sudah ada
    (saat mengubah), media baru, tombol tambah, dan pesan galat dari browser maupun server.
    Dipakai di dalam x-data "postComposer".
--}}

@php
    $field = ['feed' => 'media', 'story' => 'media_story', 'reel' => 'media_reel'][$format];
    $title = ['feed' => 'Media Feed', 'story' => 'Media Story', 'reel' => 'Video Reels'][$format];
    $add = ['feed' => 'Tambah foto', 'story' => 'Tambah foto/video', 'reel' => 'Pilih video'][$format];
    $reelMax = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel'));
    $hint = [
        'feed' => 'JPEG · maks 8 MB per foto · hingga 10 foto (carousel). Rasio 4:5 sampai 1,91:1.',
        'story' => 'Foto JPEG atau video MP4/MOV · hingga 10 item, masing-masing jadi satu Story. Video 3-60 detik, maks 100 MB. Rasio 9:16 disarankan. Story tidak memakai caption.',
        'reel' => 'Satu video MP4/MOV (H.264 atau HEVC, audio AAC) · 3 detik sampai '.$reelMax.' · maks 300 MB. Rasio 9:16 disarankan.',
    ][$format];

    $serverErrors = collect($errors->get($field))
        ->merge(collect($errors->get($field.'.*'))->flatten())
        ->flatten()
        ->unique()
        ->values()
        ->all();
@endphp

<div x-show="has('{{ $format }}')" x-cloak class="rounded-lg border border-border p-4" data-format-section="{{ $format }}">
    <div class="flex items-baseline justify-between">
        <span class="block text-sm font-medium text-slate-700 mb-2">{{ $title }}</span>
        <span class="text-xs tabular-nums text-slate-400" x-text="countFor('{{ $format }}') + ' / ' + capacity('{{ $format }}')"></span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <template x-for="entry in entries('{{ $format }}')" :key="entry.key">
            <div class="group/tile relative aspect-square overflow-hidden rounded-lg border bg-slate-100 transition-shadow duration-150"
                data-media-tile :data-key="entry.key"
                :class="[
                    entry.kind === 'new' ? 'border-primary/40' : 'border-border',
                    canReorder('{{ $format }}') ? 'cursor-grab active:cursor-grabbing' : '',
                    drag && drag.key === entry.key ? 'opacity-40' : '',
                    drag && drag.format === '{{ $format }}' && drag.key !== entry.key && over === entry.key ? 'ring-2 ring-primary ring-offset-2' : '',
                ]"
                :draggable="canReorder('{{ $format }}') ? 'true' : 'false'"
                @dragstart="dragStart('{{ $format }}', entry.key, $event)"
                @dragover="dragOver('{{ $format }}', entry.key, $event)"
                @dragleave="over === entry.key && (over = null)"
                @drop.prevent="drop('{{ $format }}', entry.key)"
                @dragend="endDrag()">
                <template x-if="entry.type === 'video'">
                    <video :src="entry.url + '#t=0.1'" preload="metadata" muted playsinline draggable="false" x-on:loadedmetadata="$el.currentTime = 0.1"
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover"></video>
                </template>
                <template x-if="entry.type !== 'video'">
                    <img :src="entry.url" :alt="entry.kind === 'new' ? 'Media baru' : 'Media yang sudah ada'" draggable="false"
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover">
                </template>
                <span x-show="entry.type === 'video'" class="pointer-events-none absolute inset-0 grid place-items-center text-white/90" aria-hidden="true">
                    <x-heroicon-s-play class="h-8 w-8 drop-shadow" />
                </span>

                <span class="pointer-events-none absolute left-1 top-1 inline-flex items-center gap-1 rounded bg-slate-900/70 px-1.5 py-0.5 text-[10px] font-medium text-white">
                    <span class="tabular-nums" x-text="entry.position + 1"></span>
                    <span x-show="entry.kind === 'new'" class="rounded bg-primary px-1 text-[9px] leading-tight">Baru</span>
                </span>

                <button type="button" x-show="! isLocked('{{ $format }}')"
                    @click="entry.kind === 'existing' ? removeExisting('{{ $format }}', entry.id) : removeNew('{{ $format }}', entry.key)"
                    class="absolute right-1 top-1 rounded-full bg-slate-900/70 p-1 text-white transition-colors hover:bg-danger" title="Hapus media ini" aria-label="Hapus media ini">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>

                <div x-show="canReorder('{{ $format }}')" x-cloak
                    class="absolute inset-x-1 bottom-1 flex justify-between transition-opacity focus-within:opacity-100 sm:opacity-0 sm:group-hover/tile:opacity-100">
                    <button type="button" @click="shift('{{ $format }}', entry.key, -1)" :disabled="entry.position === 0" aria-label="Geser ke kiri" title="Geser ke kiri"
                        class="rounded-full bg-slate-900/70 p-1 text-white hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-white disabled:opacity-30">
                        <x-heroicon-o-chevron-left class="h-3 w-3" />
                    </button>
                    <button type="button" @click="shift('{{ $format }}', entry.key, 1)" :disabled="entry.position === order['{{ $format }}'].length - 1" aria-label="Geser ke kanan" title="Geser ke kanan"
                        class="rounded-full bg-slate-900/70 p-1 text-white hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-white disabled:opacity-30">
                        <x-heroicon-o-chevron-right class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </template>

        <label x-show="! isLocked('{{ $format }}') && countFor('{{ $format }}') < capacity('{{ $format }}')"
            @change="addFiles('{{ $format }}', $event)"
            class="relative flex flex-col items-center justify-center aspect-square rounded-lg border-2 border-dashed border-border bg-slate-50/50 cursor-pointer transition-colors duration-150 hover:border-primary/40 hover:bg-primary-light/20 focus-within:ring-2 focus-within:ring-primary/30">
            <x-heroicon-o-arrow-up-tray class="h-6 w-6 text-slate-300" />
            <span class="mt-1 px-1 text-center text-xs text-slate-400">{{ $add }}</span>
            <input type="file" name="{{ $field }}[]" data-media-input="{{ $format }}"
                :accept="accepts('{{ $format }}')" {{ $format === 'reel' ? '' : 'multiple' }}
                :disabled="! has('{{ $format }}')" class="sr-only">
        </label>
    </div>

    <template x-for="token in orderTokens('{{ $format }}')" :key="'order-' + token">
        <input type="hidden" name="order[{{ $format }}][]" :value="token" :disabled="! has('{{ $format }}') || isLocked('{{ $format }}')">
    </template>

    <p x-show="canReorder('{{ $format }}')" x-cloak class="mt-2 text-xs text-slate-500" data-reorder-hint>
        Seret media untuk mengatur urutan, atau pakai tombol panah.
        {{ ['feed' => 'Foto pertama menjadi sampul carousel.', 'story' => 'Urutan ini juga urutan tayang Story.', 'reel' => ''][$format] }}
    </p>

    <p x-show="isLocked('{{ $format }}')" x-cloak class="mt-2 text-xs text-success-text">
        Format ini sudah terbit, jadi medianya tidak bisa diubah.
    </p>

    <p class="mt-2 text-xs text-slate-400">{{ $hint }}</p>

    <p x-show="checking['{{ $format }}']" x-cloak class="mt-1.5 text-xs text-slate-500" role="status">Memeriksa file…</p>

    <p x-show="problems['{{ $format }}']" x-cloak x-text="problems['{{ $format }}']" role="alert" class="mt-1.5 text-xs text-danger"></p>

    @foreach ($serverErrors as $message)
        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
    @endforeach
</div>
