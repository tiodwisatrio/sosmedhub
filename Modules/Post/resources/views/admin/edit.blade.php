@extends('layouts.admin')

@section('title', 'Edit Post')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Post</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-danger">*</span></label>
                    <x-admin.select
                        name="category_id"
                        :options="$categories->pluck('name', 'id')->toArray()"
                        :selected="old('category_id', (string) $post->category_id)"
                        placeholder="Pilih kategori..."
                    />
                    @error('category_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul <span class="text-danger">*</span></label>
                    <x-admin.input-text name="title" :value="old('title', $post->title)" placeholder="Judul post" />
                    @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Konten <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.textarea name="content" placeholder="Isi konten post...">{{ old('content', $post->content) }}</x-admin.textarea>
                    @error('content') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    @if ($post->image)
                        <div class="mb-3">
                            <p class="text-sm font-medium text-slate-700 mb-1.5">Gambar Saat Ini</p>
                            <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}"
                                class="h-24 w-auto rounded-lg object-cover border border-border">
                        </div>
                    @endif
                    <x-admin.file-upload name="image" label="Ganti Gambar" hint="Biarkan kosong jika tidak ingin mengubah. JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                    @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Penulis <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <x-admin.input-text name="author" :value="old('author', $post->author)" placeholder="Nama penulis" />
                    @error('author') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                    <x-admin.select
                        name="status"
                        :options="['1' => 'Published', '0' => 'Draft']"
                        :selected="old('status', (string) $post->status)"
                    />
                    @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.posts.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
