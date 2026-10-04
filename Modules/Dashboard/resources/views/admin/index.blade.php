@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Dashboard</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">
                        Selamat datang, {{ Auth::user()->name }}!
                    </h2>
                </div>
            </div>

            <p class="text-slate-600 leading-relaxed">
                Anda berhasil masuk ke panel Sosmedhub. Langkah berikutnya adalah menghubungkan akun Instagram Business dan menyiapkan antrian konten pertama.
            </p>

            
        </div>
    </div>
@endsection
