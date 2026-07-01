@props(['label' => null, 'name' => '', 'hint' => null, 'options' => [], 'selected' => null, 'placeholder' => 'Pilih...'])

@php
    $errorMsg = $errors->first($name);
    $hasError  = (bool) $errorMsg;
    $inputClass = 'w-full rounded-md border px-3 py-2 text-sm text-slate-800 bg-white focus:outline-none focus:ring-1 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-50 '
        . ($hasError
            ? 'border-danger focus:border-danger focus:ring-danger/20'
            : 'border-border focus:border-primary focus:ring-primary/20');
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1.5">
            {{ $label }}
            @if($attributes->get('required'))
                <span class="text-danger ml-0.5">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->except(['class', 'options', 'selected', 'placeholder'])->merge(['class' => $inputClass]) }}
    >
        @if($placeholder)
            <option value="" disabled {{ !$selected ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $optLabel)
            <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="mt-1.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif
</div>
