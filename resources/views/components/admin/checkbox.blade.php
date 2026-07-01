@props(['label' => null, 'name' => '', 'hint' => null, 'checked' => false, 'value' => '1'])

@php
    $errorMsg = $errors->first($name);
    $hasError  = (bool) $errorMsg;
@endphp

<div {{ $attributes->only('class') }}>
    <label class="flex items-start gap-2.5 cursor-pointer group">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $attributes->except(['class', 'checked', 'value']) }}
            class="mt-0.5 w-4 h-4 rounded border-border text-primary focus:ring-primary/20 focus:ring-1 focus:outline-none cursor-pointer"
        >
        @if($label)
            <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors leading-snug">
                {{ $label }}
            </span>
        @endif
    </label>

    @if($hint && !$hasError)
        <p class="mt-1.5 ml-6.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="mt-1.5 ml-6.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif
</div>
