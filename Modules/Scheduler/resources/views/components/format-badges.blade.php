@props(['post', 'status' => false, 'size' => 'md'])

{{--
    Lencana format (Feed, Story, Reels) sebuah jadwal. Dengan status=true, tiap lencana diberi
    titik warna sesuai hasil penerbitan formatnya (terbit, gagal, atau menunggu).
--}}

@php
    $publications = $post->relationLoaded('publications') ? $post->publications : $post->publications()->get();
    $byFormat = $publications->keyBy('format');
    $formats = $post->formats();

    $palette = [
        'feed' => 'bg-slate-100 text-slate-700',
        'story' => 'bg-fuchsia-100 text-fuchsia-800',
        'reel' => 'bg-sky-100 text-sky-800',
    ];
    $dot = [
        'published' => 'bg-success',
        'failed' => 'bg-danger',
        'publishing' => 'bg-warning',
        'pending' => 'bg-slate-300',
    ];
    $text = $size === 'sm' ? 'text-[9px] px-1.5 py-0.5' : 'text-[11px] px-2 py-0.5';
@endphp

<span {{ $attributes->class(['inline-flex flex-wrap items-center gap-1']) }} data-format-badges>
    @foreach ($formats as $format)
        @php($publication = $byFormat->get($format))
        <span class="inline-flex items-center gap-1 rounded-full font-semibold {{ $text }} {{ $palette[$format] }}"
            @if ($status && $publication) title="{{ \Modules\Scheduler\Models\ScheduledPost::formatLabel($format) }}: {{ $publication->statusLabel() }}" @endif>
            @if ($status && $publication)
                <span class="h-1.5 w-1.5 rounded-full {{ $dot[$publication->status] ?? $dot['pending'] }}" aria-hidden="true"></span>
            @endif
            {{ \Modules\Scheduler\Models\ScheduledPost::formatLabel($format) }}
        </span>
    @endforeach
</span>
