@props(['label' => null, 'name' => '', 'hint' => null, 'options' => [], 'selected' => null])

@php
    $errorMsg = $errors->first($name);
    $hasError  = (bool) $errorMsg;
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <p class="block text-sm font-medium text-slate-700 mb-2">
            {{ $label }}
            @if($attributes->get('required'))
                <span class="text-danger ml-0.5">*</span>
            @endif
        </p>
    @endif

    <div class="flex flex-wrap gap-x-6 gap-y-2">
        @foreach($options as $value => $optLabel)
            <label class="flex items-center gap-2 cursor-pointer group">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $value }}"
                    {{ old($name, $selected) == $value ? 'checked' : '' }}
                    {{ $attributes->except(['class', 'options', 'selected']) }}
                    class="w-4 h-4 border-border text-primary focus:ring-primary/20 focus:ring-1 focus:outline-none cursor-pointer"
                >
                <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors">{{ $optLabel }}</span>
            </label>
        @endforeach
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="mt-1.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif
</div>
