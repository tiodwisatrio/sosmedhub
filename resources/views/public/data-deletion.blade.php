@extends('layouts.public')

@section('title', 'Penghapusan Data')
@section('description', 'Cara memutus akun Instagram dan Halaman Facebook serta meminta penghapusan data Anda dari Sosmedhub.')

@section('content')
    <article class="px-5 pb-24 pt-36 sm:px-6 md:pt-44 lg:px-24">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-[#1c1c1c]/40">Terakhir diperbarui 20 Oktober 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl font-display font-bold">Penghapusan Data</h1>
            <p class="mt-6 leading-relaxed text-[#1c1c1c]/60">
                Anda bisa menghentikan akses Sosmedhub ke akun Instagram dan Halaman Facebook Anda, dan meminta seluruh data Anda dihapus. Ada empat cara, dari yang paling cepat.
            </p>

            <div class="legal mt-4">
                <h2>1. Cabut izin di Instagram atau Facebook</h2>
                <p>Ini langsung menghentikan akses Sosmedhub, tanpa perlu menunggu kami.</p>
                <p><strong>Instagram</strong></p>
                <ol>
                    <li>Buka Instagram, lalu <strong>Pengaturan</strong> (Settings).</li>
                    <li>Pilih <strong>Aplikasi dan situs web</strong> (Apps and websites).</li>
                    <li>Cari Sosmedhub, lalu pilih <strong>Hapus</strong> (Remove).</li>
                </ol>
                <p><strong>Facebook</strong></p>
                <ol>
                    <li>Buka Facebook, lalu <strong>Pengaturan dan privasi</strong> &rarr; <strong>Pengaturan</strong> (Settings).</li>
                    <li>Pilih <strong>Aplikasi dan situs web</strong> (Apps and websites). Bila Sosmedhub tidak ada di sana, cek <strong>Integrasi bisnis</strong> (Business Integrations).</li>
                    <li>Cari Sosmedhub, lalu pilih <strong>Hapus</strong> (Remove).</li>
                </ol>
                <p>Setelah izin dicabut, Sosmedhub tidak bisa lagi menerbitkan ke akun atau Halaman Anda.</p>

                <h2>2. Putuskan akun dari Sosmedhub</h2>
                <ol>
                    <li>Masuk ke Sosmedhub, lalu buka menu <strong>Akun Sosial</strong>.</li>
                    <li>Pilih akun Instagram atau Halaman Facebook yang ingin diputus, lalu hapus koneksinya.</li>
                </ol>
                <p>Token akses akun itu langsung dihapus dari sistem kami, dan postingan terjadwal untuk akun tersebut tidak akan terbit.</p>

                <h2>3. Hapus akun Anda di Sosmedhub</h2>
                <ol>
                    <li>Masuk ke Sosmedhub, lalu buka <strong>Profil Saya</strong>.</li>
                    <li>Di bagian <strong>Hapus Akun</strong>, pilih <strong>Hapus Akun</strong> dan masukkan kata sandi Anda untuk mengonfirmasi.</li>
                </ol>
                <p>Akun dan seluruh data Anda langsung dihapus permanen: data dan token akun Instagram dan Halaman Facebook yang terhubung, semua jadwal postingan, caption, serta foto dan video yang Anda unggah.</p>

                <h2>4. Minta penghapusan lewat email</h2>
                <p>Bila Anda tidak bisa masuk ke akun, kirim permintaan ke @include('public._contact') dari alamat email yang Anda pakai untuk mendaftar, dengan subjek <strong>Hapus Data Sosmedhub</strong>. Cantumkan username Instagram atau nama Halaman Facebook yang pernah Anda hubungkan, bila ada.</p>
                <p>Kami akan memverifikasi bahwa permintaan datang dari pemilik akun, lalu menghapus:</p>
                <ul>
                    <li>akun Sosmedhub Anda (nama, email, nomor telepon);</li>
                    <li>data dan token akses akun Instagram dan Halaman Facebook yang terhubung;</li>
                    <li>semua jadwal postingan, caption, serta foto dan video yang Anda unggah.</li>
                </ul>
                <p>Kami berupaya menyelesaikan permintaan dalam waktu paling lama 30 hari dan memberi tahu Anda lewat email setelah selesai.</p>

                <h2>Yang tidak dihapus</h2>
                <p>Postingan yang sudah terbit di Instagram atau Halaman Facebook tetap ada di akun Anda dan dikelola sendiri oleh Anda lewat platform tersebut. Sosmedhub tidak menghapusnya.</p>

                <p class="mt-10 border-t border-[#1c1c1c]/10 pt-6">Cara kami mengumpulkan dan memakai data dijelaskan di <a href="{{ route('privacy') }}">kebijakan privasi</a>.</p>
            </div>
            </div>
        </div>
    </article>
@endsection
