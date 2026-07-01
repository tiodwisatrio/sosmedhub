@extends('layouts.admin')

@section('title', 'Role & Akses')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Role & Akses</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <p class="text-sm text-slate-500">Total: {{ $roles->count() }} role</p>
        @can('role.create')
            <a href="{{ route('admin.roles.create') }}">
                <x-admin.button>+ Tambah Role</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama Role</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Pengguna</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($roles as $role)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $role->name }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $role->users_count }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('role.edit')
                                    <a href="{{ route('admin.roles.edit', $role) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('role.delete')
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                        onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                                @cannot('role.edit')
                                    @cannot('role.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-slate-400">Belum ada role.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
