@extends('layouts.admin')

@section('title', 'Tambah Banner')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Banner</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Banner <span class="text-danger">*</span></label>
                        <x-admin.input-text name="nama_banner" :value="old('nama_banner')" placeholder="Nama Banner" />
                        @error('nama_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Banner <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.textarea name="deskripsi_banner">{{ old('deskripsi_banner') }}</x-admin.textarea>
                        @error('deskripsi_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Banner <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.file-upload name="gambar_banner" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                        @error('gambar_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select name="status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" :selected="old('status', '1')" />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Simpan</x-admin.button>
                    <a href="{{ route('admin.banners.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
