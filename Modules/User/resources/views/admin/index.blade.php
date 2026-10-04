@extends('layouts.admin')

@section('title', 'Pengguna')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Pengguna</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <p class="text-sm text-slate-500">Total: {{ $users->total() }} pengguna</p>
        @can('user.create')
            <a href="{{ route('admin.users.create') }}">
                <x-admin.button>+ Tambah Pengguna</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Email</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Role</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status Login</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Approval</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Bergabung</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if ($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                        class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-slate-800">{{ $user->name }}</p>
                                    @if ($user->phone)
                                        <p class="text-xs text-slate-400">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            @php $role = $user->roles->first() @endphp
                            @if ($role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-light text-primary">
                                    {{ $role->name }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if ($user->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-400">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $approvalClass = match ($user->approval_status) {
                                    \App\Models\User::APPROVAL_APPROVED => 'bg-success-light text-success-text',
                                    \App\Models\User::APPROVAL_REJECTED => 'bg-danger-light text-danger-text',
                                    \App\Models\User::APPROVAL_SUSPENDED => 'bg-warning-light text-warning-text',
                                    default => 'bg-slate-100 text-slate-500',
                                };

                                $approvalLabel = match ($user->approval_status) {
                                    \App\Models\User::APPROVAL_APPROVED => 'Approved',
                                    \App\Models\User::APPROVAL_REJECTED => 'Rejected',
                                    \App\Models\User::APPROVAL_SUSPENDED => 'Suspended',
                                    default => 'Pending',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $approvalClass }}">
                                {{ $approvalLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('user.edit')
                                    @if (! $user->isApproved())
                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.button size="sm" type="submit">Approve</x-admin.button>
                                        </form>
                                    @endif
                                    @if ($user->isApproved())
                                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}"
                                            onsubmit="return confirm('Suspend pengguna {{ $user->name }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.button variant="outline" size="sm" type="submit">Suspend</x-admin.button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('user.delete')
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                        </form>
                                    @endif
                                @endcan
                                @cannot('user.edit')
                                    @cannot('user.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
