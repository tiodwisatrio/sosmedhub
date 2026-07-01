@extends('layouts.admin')

@section('title', 'Paket')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Paket</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.pakets.index')" placeholder="Cari Paket..." />
        @can('paket.create')
            <a href="{{ route('admin.pakets.create') }}">
                <x-admin.button>+ Tambah Paket</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama Paket</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Deskripsi Paket</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Harga Paket</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Gambar Paket</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Urutan</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($pakets as $paket)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $paket->nama_paket }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $paket->deskripsi_paket }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $paket->harga_paket }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $paket->gambar_paket }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $paket->urutan }}</td>
                        <td class="px-6 py-3">
                            @if ($paket->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('paket.edit')
                                    <a href="{{ route('admin.pakets.edit', $paket) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('paket.delete')
                                    <form method="POST" action="{{ route('admin.pakets.destroy', $paket) }}"
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
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($pakets->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $pakets->links() }}
            </div>
        @endif
    </div>
@endsection
