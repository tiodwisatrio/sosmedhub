@extends('layouts.admin')

@section('title', 'Edit Hero')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Hero</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.heroes.update', $hero) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul Hero <span class="text-danger">*</span></label>
                        <x-admin.input-text name="judul_hero" :value="old('judul_hero', $hero->judul_hero)" placeholder="Judul Hero" />
                        @error('judul_hero') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Hero <span class="text-danger">*</span></label>
                        <x-admin.textarea name="deskripsi_hero">{{ old('deskripsi_hero', $hero->deskripsi_hero) }}</x-admin.textarea>
                        @error('deskripsi_hero') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Button Hero <span class="text-danger">*</span></label>
                        <x-admin.input-text name="button_hero" :value="old('button_hero', $hero->button_hero)" placeholder="Button Hero" />
                        @error('button_hero') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.heroes.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
