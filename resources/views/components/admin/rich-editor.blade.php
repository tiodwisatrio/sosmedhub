@props(['label' => null, 'name' => '', 'hint' => null, 'value' => ''])

@php
    $errorMsg = $errors->first($name);
    $hasError  = (bool) $errorMsg;
    $editorId  = 'editor-' . $name;
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

    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        class="rounded-md border {{ $hasError ? 'border-danger' : 'border-border' }} bg-white text-sm text-slate-800 w-full"
    >{{ old($name, $value) }}</textarea>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="mt-1.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
@endonce

@push('scripts')
<script>
    tinymce.init({
        selector: '#{{ $editorId }}',
        height: 260,
        menubar: false,
        license_key: 'gpl',
        plugins: 'lists link',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | blockquote link',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
    });
</script>
@endpush
