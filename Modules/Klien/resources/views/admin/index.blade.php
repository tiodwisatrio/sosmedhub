@extends('layouts.admin')

@section('title', 'Klien')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Klien</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.kliens.index')" placeholder="Cari Klien..." />
        @can('klien.create')
            <a href="{{ route('admin.kliens.create') }}">
                <x-admin.button>+ Tambah Klien</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama Klien</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Logo Klien</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Urutan</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($kliens as $klien)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $klien->nama_klien }}</td>
                        <td class="px-6 py-3">@if ($klien->logo_klien)<img src="{{ Storage::url($klien->logo_klien) }}" class="w-10 h-10 rounded-lg object-cover border border-border">@else<span class="text-slate-300">—</span>@endif</td>
                        <td class="px-6 py-3 text-slate-500">{{ $klien->urutan }}</td>
                        <td class="px-6 py-3">
                            @if ($klien->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('klien.edit')
                                    <a href="{{ route('admin.kliens.edit', $klien) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('klien.delete')
                                    <form method="POST" action="{{ route('admin.kliens.destroy', $klien) }}"
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

        @if ($kliens->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $kliens->links() }}
            </div>
        @endif
    </div>
@endsection
