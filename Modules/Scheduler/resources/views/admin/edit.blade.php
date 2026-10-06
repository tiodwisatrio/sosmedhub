@extends('layouts.admin')

@section('title', 'Ubah Postingan')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Ubah Postingan</h1>
@endsection

@section('content')
    @php($publications = $post->publications)

    @if ($post->needsAttention())
        <div class="mb-4 rounded-md border border-danger/30 bg-danger-light px-4 py-3 text-sm text-danger-text">
            <p class="font-medium">
                {{ $post->status === \Modules\Scheduler\Models\ScheduledPost::STATUS_PARTIAL ? 'Sebagian format sudah terbit, sebagian gagal.' : 'Postingan ini gagal terbit.' }}
            </p>

            @if ($publications->isNotEmpty())
                <ul class="mt-1 space-y-0.5">
                    @foreach ($publications as $publication)
                        <li>
                            <span class="font-medium">{{ $publication->label() }}:</span>
                            {{ $publication->isFailed() ? ($publication->error_message ?: 'Gagal.') : $publication->statusLabel() }}
                        </li>
                    @endforeach
                </ul>
            @elseif ($post->error_message)
                <p class="mt-1">{{ $post->error_message }}</p>
            @endif

            <p class="mt-1">Periksa isinya, pilih waktu terbit yang baru, lalu simpan. Hanya format yang gagal yang diterbitkan ulang.</p>
        </div>
    @elseif ($post->status === \Modules\Scheduler\Models\ScheduledPost::STATUS_DRAFT)
        <div class="mb-4 rounded-md border border-border bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Ini draf hasil duplikasi. Atur waktu terbit lalu simpan agar masuk antrean.
        </div>
    @endif

    <x-scheduler::composer :post="$post" :social-accounts="$socialAccounts" />
@endsection
