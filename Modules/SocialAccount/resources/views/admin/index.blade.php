@extends('layouts.admin')

@section('title', 'Akun Sosial')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Akun Sosial</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <p class="text-sm text-slate-500">Total: {{ $accounts->total() }} akun</p>
        @can('social-account.create')
            <a href="{{ route('admin.social-accounts.create') }}">
                <x-admin.button>+ Tambah Akun</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-slate-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Akun</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Platform</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Owner</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Token</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($account->avatar_url)
                                        <img src="{{ $account->avatar_url }}" alt="{{ $account->username }}"
                                            class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary font-semibold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($account->username, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $account->display_name ?: $account->username }}</p>
                                        <p class="text-xs text-slate-400">@{{ $account->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ ucfirst($account->platform) }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $account->user?->name }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $statusClass = match ($account->status) {
                                        \Modules\SocialAccount\Models\SocialAccount::STATUS_ACTIVE => 'bg-success-light text-success-text',
                                        \Modules\SocialAccount\Models\SocialAccount::STATUS_EXPIRED => 'bg-warning-light text-warning-text',
                                        default => 'bg-slate-100 text-slate-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-500">
                                {{ $account->token_expires_at ? $account->token_expires_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('social-account.edit')
                                        <a href="{{ route('admin.social-accounts.edit', $account) }}">
                                            <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                        </a>
                                    @endcan
                                    @can('social-account.delete')
                                        @if ($account->isActive())
                                            <form method="POST" action="{{ route('admin.social-accounts.destroy', $account) }}"
                                                onsubmit="return confirm('Putuskan akun {{ $account->username }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-admin.button variant="danger" size="sm" type="submit">Putuskan</x-admin.button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">Belum ada akun sosial.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($accounts->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>
@endsection
