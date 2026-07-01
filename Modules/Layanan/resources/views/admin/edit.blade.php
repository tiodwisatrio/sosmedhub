@extends('layouts.admin')

@section('title', 'Edit Layanan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Layanan</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.layanans.update', $layanan) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-danger">*</span></label>
                    <x-admin.input-text name="name" :value="old('name', $layanan->name)" placeholder="Nama layanan" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="description" placeholder="Deskripsi layanan...">{{ old('description', $layanan->description) }}</x-admin.textarea>
                    @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    @if ($layanan->image)
                        <div class="mb-3">
                            <p class="text-sm font-medium text-slate-700 mb-1.5">Gambar Saat Ini</p>
                            <img src="{{ Storage::url($layanan->image) }}" alt="{{ $layanan->name }}"
                                class="h-24 w-auto rounded-lg object-cover border border-border">
                        </div>
                    @endif
                    <x-admin.file-upload name="image" label="Ganti Gambar" hint="Biarkan kosong jika tidak ingin mengubah. JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                    @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                    <x-admin.input-number name="urutan" :value="old('urutan', $layanan->urutan)" min="0" />
                    @error('urutan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                    <x-admin.select
                        name="status"
                        :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                        :selected="old('status', (string) $layanan->status)"
                    />
                    @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.layanans.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
