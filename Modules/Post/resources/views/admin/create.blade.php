@extends('layouts.admin')

@section('title', 'Tambah Post')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Post</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-danger">*</span></label>
                    <x-admin.select
                        name="category_id"
                        :options="$categories->pluck('name', 'id')->toArray()"
                        :selected="old('category_id')"
                        placeholder="Pilih kategori..."
                    />
                    @error('category_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul <span class="text-danger">*</span></label>
                    <x-admin.input-text name="title" :value="old('title')" placeholder="Judul post" />
                    @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Konten <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.rich-editor name="content" :value="old('content')" />
                    @error('content') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-admin.file-upload name="image" label="Gambar" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                    @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Penulis <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.input-text name="author" :value="old('author')" placeholder="Nama penulis" />
                    @error('author') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                    <x-admin.select
                        name="status"
                        :options="['1' => 'Published', '0' => 'Draft']"
                        :selected="old('status', '0')"
                    />
                    @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Simpan</x-admin.button>
                    <a href="{{ route('admin.posts.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
