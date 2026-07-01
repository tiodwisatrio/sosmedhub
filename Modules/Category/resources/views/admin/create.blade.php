@extends('layouts.admin')

@section('title', 'Tambah Kategori ' . ucfirst($type))

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Kategori {{ ucfirst($type) }}</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                    <x-admin.input-text name="name" :value="old('name')" placeholder="Nama kategori" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                    <x-admin.input-text name="slug" :value="old('slug')" placeholder="slug-kategori" />
                    @error('slug') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description') }}</x-admin.textarea>
                    @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Simpan</x-admin.button>
                    <a href="{{ route('admin.categories.index', ['type' => $type]) }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('[name=name]').addEventListener('input', function () {
            const slug = document.querySelector('[name=slug]');
            if (!slug.dataset.edited) {
                slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
        document.querySelector('[name=slug]').addEventListener('input', function () {
            this.dataset.edited = '1';
        });
    </script>
@endsection
