@extends('layouts.admin')

@section('title', 'Banner')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Banner</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.banners.index')" placeholder="Cari Banner..." />
        @can('banner.create')
            <a href="{{ route('admin.banners.create') }}">
                <x-admin.button>+ Tambah Banner</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama Banner</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Deskripsi Banner</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Gambar Banner</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($banners as $banner)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $banner->nama_banner }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ Str::limit(strip_tags($banner->deskripsi_banner), 50) }}</td>
                        <td class="px-6 py-3">@if ($banner->gambar_banner)<img src="{{ Storage::url($banner->gambar_banner) }}" class="w-10 h-10 rounded-lg object-cover border border-border">@else<span class="text-slate-300">—</span>@endif</td>
                        <td class="px-6 py-3">
                            @if ($banner->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('banner.edit')
                                    <a href="{{ route('admin.banners.edit', $banner) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('banner.delete')
                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}"
                                        onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($banners->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $banners->links() }}
            </div>
        @endif
    </div>
@endsection
