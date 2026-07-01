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

    <div
        id="{{ $editorId }}"
        class="rounded-md border {{ $hasError ? 'border-danger' : 'border-border' }} bg-white text-sm text-slate-800 min-h-[180px] focus-within:ring-1 {{ $hasError ? 'focus-within:ring-danger/20' : 'focus-within:ring-primary/20' }} transition-colors"
    >{!! old($name, $value) !!}</div>

    <input type="hidden" name="{{ $name }}" id="input-{{ $name }}" value="{{ old($name, $value) }}">

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="mt-1.5 text-xs text-danger">{{ $errorMsg }}</p>
    @endif
</div>

@once
    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/classic/ckeditor.js"></script>
    @endpush
@endonce

@push('scripts')
<script>
    ClassicEditor
        .create(document.getElementById('{{ $editorId }}'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'underline', '|',
                      'bulletedList', 'numberedList', '|', 'blockQuote', 'link', '|', 'undo', 'redo'],
        })
        .then(editor => {
            editor.model.document.on('change:data', () => {
                document.getElementById('input-{{ $name }}').value = editor.getData();
            });
        });
</script>
@endpush
