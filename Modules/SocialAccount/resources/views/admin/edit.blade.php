@extends('layouts.admin')

@section('title', 'Edit Akun Sosial')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Akun Sosial</h1>
@endsection

@section('content')
    <div class="bg-card rounded-xl shadow-card border border-border p-6">
        <form method="POST" action="{{ route('admin.social-accounts.update', $account) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('social-account::admin._form')

            <div class="flex items-center gap-3 pt-2">
                <x-admin.button type="submit">Perbarui</x-admin.button>
                <a href="{{ route('admin.social-accounts.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>
@endsection
