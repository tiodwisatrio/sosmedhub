@props(['label' => null, 'name' => '', 'hint' => null, 'accept' => '*', 'preview' => false, 'multiple' => false])

@php
    $errorMsg = $errors->first($name);
    $hasError  = (bool) $errorMsg;
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
            {{ $label }}
            @if($attributes->get('required'))
                <span class="text-danger ml-0.5">*</span>
            @endif
        </label>
    @endif

    <label
        for="{{ $name }}"
        class="flex flex-col items-center justify-center w-full rounded-md border-2 border-dashed cursor-pointer transition-colors duration-150 py-6 px-4 {{ $hasError ? 'border-danger bg-danger/5 hover:bg-danger/10' : 'border-border bg-slate-50 hover:bg-slate-100 hover:border-primary/40' }}"
    >
        <svg class="w-8 h-8 mb-2 {{ $hasError ? 'text-danger' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
        </svg>
        <p class="text-sm text-slate-500">
            <span class="font-medium {{ $hasError ? 'text-danger' : 'text-primary' }}">Klik untuk upload</span>
        </p>
        @if($hint && !$hasError)
            <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="file"
            class="hidden"
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $attributes->except(['class', 'accept', 'preview', 'multiple']) }}
        >
    </label>

    @if($hasError)
        <p class="mt-1.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif

    @if($preview)
        <div id="preview-{{ $name }}" class="mt-2 hidden">
            <img id="preview-img-{{ $name }}" src="" alt="Preview" class="h-24 w-auto rounded-md object-cover border border-border">
        </div>
        <script>
            document.getElementById('{{ $name }}').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    document.getElementById('preview-img-{{ $name }}').src = ev.target.result;
                    document.getElementById('preview-{{ $name }}').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            });
        </script>
    @endif
</div>
