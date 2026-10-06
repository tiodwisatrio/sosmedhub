@extends('layouts.admin')

@section('title', 'Akun Sosial')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Akun Sosial</h1>
@endsection

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-slate-500">
        Hubungkan akun media sosial yang akan dipakai untuk menerbitkan postingan terjadwal.
    </p>

    <div class="grid items-start gap-5 md:grid-cols-2 xl:grid-cols-3">
        <x-social-account::platform-card
            platform="instagram"
            label="Instagram"
            :accounts="$accountsByPlatform->get('instagram', collect())"
            :connect-url="route('admin.social-accounts.instagram.redirect')"
            :show-owner="$showOwner"
            available />

        <x-social-account::platform-card
            platform="facebook"
            label="Facebook"
            description="Halaman Facebook"
            :accounts="$accountsByPlatform->get('facebook', collect())" />

        <x-social-account::platform-card
            platform="threads"
            label="Threads"
            description="Akun Threads"
            :accounts="$accountsByPlatform->get('threads', collect())" />
    </div>
@endsection
