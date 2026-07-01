@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'submit',
])

@php
    $variants = [
        'primary'   => 'bg-primary hover:bg-primary-hover text-white shadow-sm',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700',
        'danger'    => 'bg-danger hover:bg-danger-hover text-white shadow-sm',
        'outline'   => 'border border-border hover:bg-slate-50 text-slate-700',
        'ghost'     => 'hover:bg-slate-100 text-slate-600',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-2.5 text-sm',
    ];

    $baseClass = 'inline-flex items-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed';
    $class = $baseClass . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $class]) }}
>
    {{ $slot }}
</button>
