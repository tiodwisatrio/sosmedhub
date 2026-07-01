@extends('layouts.admin')

@section('title', 'Tambah Paket')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Paket</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.pakets.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Paket <span class="text-danger">*</span></label>
                        <x-admin.input-text name="nama_paket" :value="old('nama_paket')" placeholder="Nama Paket" />
                        @error('nama_paket') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Paket <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="deskripsi_paket" :value="old('deskripsi_paket')" placeholder="Deskripsi Paket" />
                        @error('deskripsi_paket') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Harga Paket <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="harga_paket" :value="old('harga_paket')" placeholder="Harga Paket" />
                        @error('harga_paket') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Paket <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="gambar_paket" :value="old('gambar_paket')" placeholder="Gambar Paket" />
                        @error('gambar_paket') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
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
                    <a href="{{ route('admin.pakets.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
