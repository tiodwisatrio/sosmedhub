@extends('layouts.public')

@section('title', 'Penghapusan Data')
@section('description', 'Cara memutus akun Instagram dan meminta penghapusan data Anda dari Sosmedhub.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Terakhir diperbarui 5 Oktober 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Penghapusan Data</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                Anda bisa menghentikan akses Sosmedhub ke akun Instagram Anda, dan meminta seluruh data Anda dihapus. Ada tiga cara, dari yang paling cepat.
            </p>

            <div class="legal mt-4">
                <h2>1. Cabut izin di Instagram</h2>
                <p>Ini langsung menghentikan akses Sosmedhub, tanpa perlu menunggu kami.</p>
                <ol>
                    <li>Buka Instagram, lalu <strong>Pengaturan</strong> (Settings).</li>
                    <li>Pilih <strong>Aplikasi dan situs web</strong> (Apps and websites).</li>
                    <li>Cari Sosmedhub, lalu pilih <strong>Hapus</strong> (Remove).</li>
                </ol>
                <p>Setelah izin dicabut, Sosmedhub tidak bisa lagi menerbitkan ke akun Anda.</p>

                <h2>2. Putuskan akun dari Sosmedhub</h2>
                <ol>
                    <li>Masuk ke Sosmedhub, lalu buka menu <strong>Akun Sosial</strong>.</li>
                    <li>Pilih akun Instagram yang ingin diputus, lalu hapus koneksinya.</li>
                </ol>
                <p>Token akses akun itu langsung dihapus dari sistem kami, dan postingan terjadwal untuk akun tersebut tidak akan terbit.</p>

                <h2>3. Minta penghapusan seluruh data</h2>
                <p>Kirim permintaan ke @include('public._contact') dari alamat email yang Anda pakai untuk mendaftar, dengan subjek <strong>Hapus Data Sosmedhub</strong>. Cantumkan username Instagram yang pernah Anda hubungkan, bila ada.</p>
                <p>Kami akan memverifikasi bahwa permintaan datang dari pemilik akun, lalu menghapus:</p>
                <ul>
                    <li>akun Sosmedhub Anda (nama, email, nomor telepon);</li>
                    <li>data dan token akses akun Instagram yang terhubung;</li>
                    <li>semua jadwal postingan, caption, dan foto yang Anda unggah.</li>
                </ul>
                <p>Kami berupaya menyelesaikan permintaan dalam waktu paling lama 30 hari dan memberi tahu Anda lewat email setelah selesai.</p>

                <h2>Yang tidak dihapus</h2>
                <p>Postingan yang sudah terbit di Instagram tetap ada di akun Anda dan dikelola sendiri oleh Anda lewat Instagram. Sosmedhub tidak menghapusnya.</p>

                <p class="mt-10 border-t border-white/10 pt-6">Cara kami mengumpulkan dan memakai data dijelaskan di <a href="{{ route('privacy') }}">kebijakan privasi</a>.</p>
            </div>
        </div>
    </article>
@endsection
