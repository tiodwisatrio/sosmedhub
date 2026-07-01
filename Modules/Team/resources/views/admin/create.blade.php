@extends('layouts.admin')

@section('title', 'Tambah Tim')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Tim</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.teams.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-danger">*</span></label>
                    <x-admin.input-text name="name" :value="old('name')" placeholder="Nama tim" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description') }}</x-admin.textarea>
                    @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.select
                        name="category_id"
                        :options="$categories->pluck('name', 'id')->toArray()"
                        :selected="old('category_id')"
                        placeholder="— Pilih Kategori —"
                    />
                    @error('category_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    @if ($categories->isEmpty())
                        <p class="mt-1 text-xs text-slate-400">
                            Belum ada kategori tim.
                            <a href="{{ route('admin.categories.create', ['type' => 'team']) }}" class="text-primary hover:underline">Buat kategori tim</a>
                        </p>
                    @endif
                </div>

                <div>
                    <x-admin.file-upload name="image" label="Gambar" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                    @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="keterangan" placeholder="Keterangan tambahan...">{{ old('keterangan') }}</x-admin.textarea>
                    @error('keterangan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
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
                    <a href="{{ route('admin.teams.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
