@extends('layouts.admin')

@section('title', 'Tambah Keunggulan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Keunggulan</h1>
@endsection

@section('content')
    <div class="bg-card rounded-xl shadow-card border border-border p-6">
        <form method="POST" action="{{ route('admin.keunggulans.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-danger">*</span></label>
                <x-admin.input-text name="name" :value="old('name')" placeholder="Nama keunggulan" />
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description') }}</x-admin.textarea>
                @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-admin.file-upload name="image" label="Gambar" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan <span class="text-danger">*</span></label>
                <x-admin.input-text name="urutan" :value="old('urutan', 1)" placeholder="Urutan keunggulan" type="number" min="1" />
                @error('urutan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select
                    name="status"
                    :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                    :selected="old('status', '1')"
                />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-admin.button type="submit">Simpan</x-admin.button>
                <a href="{{ route('admin.keunggulans.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>
@endsection