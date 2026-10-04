@extends('layouts.admin')

@section('title', 'Tambah Akun Sosial')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Akun Sosial</h1>
@endsection

@section('content')
    <div class="bg-card rounded-xl shadow-card border border-border p-6">
        <div class="mb-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1">Hubungkan otomatis</h2>
            <p class="text-sm text-slate-500 mb-4">Login sekali dengan akun Instagram bisnis, semua data akun akan diisi otomatis.</p>
            <a href="{{ route('admin.social-accounts.instagram.redirect', old('user_id') ? ['owner_id' => old('user_id')] : []) }}"
               class="inline-flex items-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 px-4 py-2 text-sm bg-primary hover:bg-primary-hover text-white shadow-sm">
                Hubungkan Instagram
            </a>
        </div>

        <hr class="border-border mb-6">

        <form method="POST" action="{{ route('admin.social-accounts.store') }}" class="space-y-5">
            @csrf

            @include('social-account::admin._form')

            <div class="flex items-center gap-3 pt-2">
                <x-admin.button type="submit">Simpan</x-admin.button>
                <a href="{{ route('admin.social-accounts.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>
@endsection
