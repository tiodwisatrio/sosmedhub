@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Banner</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Banner <span class="text-danger">*</span></label>
                        <x-admin.input-text name="nama_banner" :value="old('nama_banner', $banner->nama_banner)" placeholder="Nama Banner" />
                        @error('nama_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Banner <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.textarea name="deskripsi_banner">{{ old('deskripsi_banner', $banner->deskripsi_banner) }}</x-admin.textarea>
                        @error('deskripsi_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Banner <span class="text-slate-400 font-normal">(opsional)</span></label>
                        @if ($banner->gambar_banner)<div class="mb-3"><img src="{{ Storage::url($banner->gambar_banner) }}" class="h-24 w-auto rounded-lg object-cover border border-border"></div>@endif
                        <x-admin.file-upload name="gambar_banner" hint="Biarkan kosong jika tidak ingin mengubah. Maks. 2MB" accept="image/*" :preview="true" />
                        @error('gambar_banner') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select name="status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" :selected="old('status', (string) $banner->status)" />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.banners.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
