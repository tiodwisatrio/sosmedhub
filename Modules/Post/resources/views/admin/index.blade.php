@extends('layouts.admin')

@section('title', 'Post')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Post</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.posts.index')" placeholder="Cari judul post..." />
        @can('post.create')
            <a href="{{ route('admin.posts.create') }}">
                <x-admin.button>+ Tambah Post</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Gambar</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Judul</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Kategori</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Penulis</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($posts as $post)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3">
                            @if ($post->image)
                                <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}"
                                    class="w-10 h-10 rounded-lg object-cover border border-border">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-border flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-800">{{ $post->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $post->slug }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-500">
                            {{ $post->category?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-slate-500">
                            {{ $post->author ?? '—' }}
                        </td>
                        <td class="px-6 py-3">
                            @if ($post->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Published</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('post.edit')
                                    <a href="{{ route('admin.posts.edit', $post) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('post.delete')
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                                        onsubmit="return confirm('Hapus post ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                                @cannot('post.edit')
                                    @cannot('post.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data post.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($posts->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
