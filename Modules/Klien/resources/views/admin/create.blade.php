@extends('layouts.admin')

@section('title', 'Tambah Klien')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Klien</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.kliens.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Klien <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="nama_klien" :value="old('nama_klien')" placeholder="Nama Klien" />
                        @error('nama_klien') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Logo Klien <span class="text-danger">*</span></label>
                        <x-admin.file-upload name="logo_klien" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                        @error('logo_klien') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                <x-admin.input-number name="urutan" :value="old('urutan', 0)" min="0" />
                @error('urutan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select name="status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" :selected="old('status', '1')" />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Simpan</x-admin.button>
                    <a href="{{ route('admin.kliens.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
