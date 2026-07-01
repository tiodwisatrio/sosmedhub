@extends('layouts.admin')

@section('title', 'Kategori ' . ucfirst($type))

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Kategori {{ ucfirst($type) }}</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <!-- <p class="text-sm text-slate-500">Total: {{ $categories->total() }} kategori</p> -->
        <x-admin.search :action="route('admin.categories.index', ['type' => $type])" placeholder="Cari nama kategori..." />
        @can('category.create')
            <a href="{{ route('admin.categories.create', ['type' => $type]) }}">
                <x-admin.button>+ Tambah Kategori</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Slug</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Deskripsi</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                        <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $category->slug }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ Str::limit($category->description, 60) }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('category.edit')
                                    <a href="{{ route('admin.categories.edit', $category) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('category.delete')
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                                @cannot('category.edit')
                                    @cannot('category.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            Belum ada kategori {{ $type }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($categories->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
