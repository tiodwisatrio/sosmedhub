@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Profil Saya</h1>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <livewire:profile.update-profile-information-form />
        </div>

        @unless (auth()->user()->isDeveloper())
            <div class="bg-card rounded-xl shadow-card border border-border p-6">
                <livewire:profile.delete-user-form />
            </div>
        @endunless
    </div>
@endsection
