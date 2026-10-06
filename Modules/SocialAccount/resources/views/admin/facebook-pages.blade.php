@extends('layouts.admin')

@section('title', 'Pilih Halaman Facebook')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Pilih Halaman Facebook</h1>
@endsection

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-slate-500">
        Akun Facebook kamu mengelola beberapa Halaman. Pilih Halaman yang akan dipakai untuk menerbitkan postingan terjadwal.
    </p>

    @if ($errors->any())
        <div class="mb-4 max-w-2xl rounded-md border border-danger/30 bg-danger-light px-4 py-3 text-sm text-danger-text">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.social-accounts.facebook.connect') }}"
        class="max-w-2xl rounded-xl border border-border bg-card shadow-card">
        @csrf

        <ul class="divide-y divide-border">
            @foreach ($pages as $page)
                <li>
                    <label class="flex cursor-pointer items-center gap-3 px-5 py-4 hover:bg-slate-50">
                        <input type="checkbox" name="pages[]" value="{{ $page['id'] }}"
                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                            @checked(in_array($page['id'], old('pages', [])))>

                        @if ($page['avatar_url'])
                            <img src="{{ $page['avatar_url'] }}" alt="Foto Halaman {{ $page['name'] }}" loading="lazy" referrerpolicy="no-referrer"
                                class="h-11 w-11 shrink-0 rounded-full border border-border object-cover">
                        @else
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-primary-light text-sm font-semibold text-primary">
                                {{ strtoupper(mb_substr($page['name'], 0, 2)) }}
                            </span>
                        @endif

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-slate-800">{{ $page['name'] }}</span>
                            <span class="block truncate text-xs text-slate-400">ID {{ $page['id'] }}</span>
                        </span>

                        @if (in_array($page['id'], $connected, true))
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">Sudah terhubung</span>
                        @endif
                    </label>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-4">
            <a href="{{ route('admin.social-accounts.index') }}"
                class="inline-flex items-center rounded-md border border-border px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit"
                class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover">Hubungkan</button>
        </div>
    </form>
@endsection
