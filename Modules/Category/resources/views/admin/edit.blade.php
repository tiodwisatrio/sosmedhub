@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Kategori {{ ucfirst($category->type) }}</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $category->type }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                    <x-admin.input-text name="name" :value="old('name', $category->name)" placeholder="Nama kategori" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                    <x-admin.input-text name="slug" :value="old('slug', $category->slug)" placeholder="slug-kategori" />
                    @error('slug') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description', $category->description) }}</x-admin.textarea>
                    @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.categories.index', ['type' => $category->type]) }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
