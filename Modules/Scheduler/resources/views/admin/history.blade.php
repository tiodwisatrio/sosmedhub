@extends('layouts.admin')

@section('title', 'Riwayat')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Riwayat</h1>
@endsection

@section('content')
    @php
        $tabs = [
            '' => 'Semua',
            \Modules\Scheduler\Models\ScheduledPost::STATUS_PUBLISHED => 'Terbit',
            \Modules\Scheduler\Models\ScheduledPost::STATUS_FAILED => 'Gagal',
            \Modules\Scheduler\Models\ScheduledPost::STATUS_CANCELLED => 'Dibatalkan',
            \Modules\Scheduler\Models\ScheduledPost::STATUS_DRAFT => 'Draf',
        ];
    @endphp

    <div class="flex flex-wrap items-center gap-1.5 mb-4">
        @foreach ($tabs as $value => $label)
            @php($active = ($filters['status'] ?? '') === $value)
            <a href="{{ route('admin.post-history.index', array_filter(['status' => $value, 'account' => $filters['account'], 'q' => $filters['q']])) }}"
                class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm transition-colors
                    {{ $active ? 'bg-primary text-primary-text' : 'bg-card border border-border text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
                <span class="text-xs {{ $active ? 'text-primary-text/80' : 'text-slate-400' }}">{{ $counts[$value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.post-history.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        @if ($filters['status'])
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif

        <input id="history-search" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari caption"
            class="w-full sm:w-64 rounded-md border border-border bg-card px-3 py-2 text-sm text-slate-700 focus:border-primary focus:ring-primary/30">

        @if ($accounts->count() > 1)
            <select id="history-account" name="account" onchange="this.form.submit()"
                class="rounded-md border border-border bg-card px-3 py-2 pr-8 text-sm text-slate-700 focus:border-primary focus:ring-primary/30">
                <option value="">Semua akun</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected((string) $filters['account'] === (string) $account->id)>{{ '@'.$account->username }}</option>
                @endforeach
            </select>
        @endif

        <x-admin.button variant="outline" type="submit">Cari</x-admin.button>

        @if ($filters['status'] || $filters['account'] || $filters['q'])
            <a href="{{ route('admin.post-history.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        @endif
    </form>

    <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden mb-8">
        @forelse ($historyPosts as $post)
            <div class="flex items-center gap-4 px-6 py-4 border-b border-border last:border-0">
                <div class="flex-shrink-0">
                    @if ($post->thumbnail_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($post->thumbnail_path))
                        <img src="{{ Storage::url($post->thumbnail_path) }}" alt="Foto postingan"
                            class="w-12 h-12 rounded-lg object-cover border border-border">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 border border-border">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-800 truncate">
                        {{ \Illuminate\Support\Str::limit(strip_tags($post->caption), 90) }}
                    </p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                        <p class="text-xs text-slate-400">{{ $post->formattedScheduledAt() }} WIB</p>
                        @if ($post->socialAccount)
                            <p class="text-xs text-slate-400">{{ '@'.$post->socialAccount->username }}</p>
                        @endif
                        @if ($post->status === \Modules\Scheduler\Models\ScheduledPost::STATUS_FAILED && $post->error_message)
                            <p class="text-xs text-danger truncate">{{ $post->error_message }}</p>
                        @endif
                    </div>
                </div>

                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0
                    {{ match ($post->status) {
                        \Modules\Scheduler\Models\ScheduledPost::STATUS_PUBLISHED => 'bg-success-light text-success-text',
                        \Modules\Scheduler\Models\ScheduledPost::STATUS_FAILED => 'bg-danger-light text-danger-text',
                        \Modules\Scheduler\Models\ScheduledPost::STATUS_CANCELLED => 'bg-slate-100 text-slate-400',
                        default => 'bg-warning-light text-warning-text',
                    } }}">
                    {{ $post->statusLabel() }}
                </span>

                @can('scheduler.edit')
                    @if ($post->canBeEdited())
                        <a href="{{ route('admin.scheduled-posts.edit', $post) }}"
                            class="inline-flex items-center flex-shrink-0 font-medium rounded-md px-3 py-1.5 text-xs border border-border hover:bg-slate-50 text-slate-700">
                            {{ $post->isFailed() ? 'Jadwalkan Ulang' : 'Atur Jadwal' }}
                        </a>
                    @endif
                @endcan

                @can('scheduler.create')
                    <form method="POST" action="{{ route('admin.scheduled-posts.duplicate', $post) }}" class="flex-shrink-0">
                        @csrf
                        <x-admin.button variant="outline" size="sm" type="submit">Duplikat</x-admin.button>
                    </form>
                @endcan

                @can('scheduler.delete')
                    <form method="POST" action="{{ route('admin.scheduled-posts.destroy', $post) }}" class="flex-shrink-0"
                        onsubmit="return confirm('Hapus postingan ini?')">
                        @csrf
                        @method('DELETE')
                        <x-admin.button variant="outline" size="sm" type="submit">Hapus</x-admin.button>
                    </form>
                @endcan
            </div>
        @empty
            <p class="px-6 py-12 text-center text-slate-400 text-sm">
                {{ $filters['status'] || $filters['account'] || $filters['q'] ? 'Tidak ada postingan yang cocok dengan filter ini.' : 'Belum ada riwayat postingan.' }}
            </p>
        @endforelse

        @if ($historyPosts->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $historyPosts->links() }}
            </div>
        @endif
    </div>
@endsection
