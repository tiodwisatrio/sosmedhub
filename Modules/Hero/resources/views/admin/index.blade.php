@extends('layouts.admin')

@section('title', 'Hero')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Hero</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.heroes.index')" placeholder="Cari Hero..." />
        @can('hero.create')
            <a href="{{ route('admin.heroes.create') }}">
                <x-admin.button>+ Tambah Hero</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">No</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Judul Hero</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Deskripsi Hero</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Button Hero</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($heroes as $hero)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $hero->judul_hero }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ Str::limit(strip_tags($hero->deskripsi_hero), 50) }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $hero->button_hero }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('hero.edit')
                                    <a href="{{ route('admin.heroes.edit', $hero) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('hero.delete')
                                    <form method="POST" action="{{ route('admin.heroes.destroy', $hero) }}"
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
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($heroes->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $heroes->links() }}
            </div>
        @endif
    </div>
@endsection
