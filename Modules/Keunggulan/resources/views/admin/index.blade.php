@extends('layouts.admin')

@section('title', 'Keunggulan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Keunggulan</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.keunggulans.index')" placeholder="Cari nama keunggulan..." />
        @can('keunggulan.create')
            <a href="{{ route('admin.keunggulans.create') }}">
                <x-admin.button>+ Tambah Keunggulan</x-admin.button>
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
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Urutan</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($keunggulans as $keunggulan)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $keunggulan->name }}</td>
                        <td class="px-6 py-3 text-slate-400">{{ $keunggulan->urutan }}</td>
                        <td class="px-6 py-3">
                            @if ($keunggulan->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('keunggulan.edit')
                                    <a href="{{ route('admin.keunggulans.edit', $keunggulan) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('keunggulan.delete')
                                    <form method="POST" action="{{ route('admin.keunggulans.destroy', $keunggulan) }}"
                                        onsubmit="return confirm('Hapus keunggulan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                                @cannot('keunggulan.edit')
                                    @cannot('keunggulan.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data keunggulan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($keunggulans->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $keunggulans->links() }}
            </div>
        @endif
    </div>
@endsection