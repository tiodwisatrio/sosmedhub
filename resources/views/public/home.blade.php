@extends('layouts.public')

@section('title', 'Penjadwalan Konten Instagram')
@section('description', 'Sosmedhub membantu pemilik usaha menjadwalkan foto dan caption Instagram, lalu menerbitkannya otomatis pada jam yang dipilih.')

@section('content')
    <section class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto grid max-w-5xl items-center gap-14 md:grid-cols-[1.15fr_1fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Penjadwalan konten Instagram</p>
                <h1 class="mt-5 text-5xl leading-[1.05] md:text-7xl" style="font-family: 'Instrument Serif', serif;">
                    Susun hari ini.<br>Terbit sesuai jadwal.
                </h1>
                <p class="mt-6 max-w-md leading-relaxed text-white/60">
                    Unggah foto, tulis caption, pilih jam terbit dalam WIB. Sosmedhub menerbitkannya ke akun Instagram usaha Anda secara otomatis, tanpa perlu membuka aplikasi saat waktunya tiba.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <a href="{{ route('login') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-full px-5 py-3 text-sm text-white/60 transition hover:text-white">Daftar akun baru</a>
                </div>
                <p class="mt-4 text-xs text-white/35">Akun baru disetujui admin terlebih dahulu.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5" aria-label="Contoh tampilan jadwal">
                <p class="mb-4 text-[10px] uppercase tracking-[0.28em] text-white/35">Contoh tampilan</p>
                <ul class="divide-y divide-white/10">
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">Promo akhir pekan, diskon 20% semua menu</p>
                            <p class="mt-0.5 text-xs text-white/40">Sab, 09.00 WIB</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-400/10 px-2.5 py-1 text-xs text-amber-300">Terjadwal</span>
                    </li>
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">Menu baru: es kopi gula aren</p>
                            <p class="mt-0.5 text-xs text-white/40">Jum, 17.30 WIB</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-400/10 px-2.5 py-1 text-xs text-amber-300">Terjadwal</span>
                    </li>
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">Terima kasih sudah mampir minggu ini</p>
                            <p class="mt-0.5 text-xs text-white/40">Kam, 12.00 WIB</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs text-emerald-300">Terbit</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Cara kerja</p>
            <h2 class="mt-4 max-w-lg text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Tiga langkah, lalu biarkan jalan.</h2>

            <ol class="mt-14 grid gap-10 md:grid-cols-3">
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Langkah 1</p>
                    <h3 class="mt-2 text-lg font-medium">Hubungkan akun</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Masuk dengan akun Instagram Business atau Creator lewat halaman resmi Instagram. Kami tidak pernah melihat kata sandi Anda.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Langkah 2</p>
                    <h3 class="mt-2 text-lg font-medium">Susun jadwal</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Unggah foto JPEG, tulis caption, lalu pilih tanggal dan jam dalam WIB. Satu postingan bisa memuat sampai 10 foto sebagai carousel.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Langkah 3</p>
                    <h3 class="mt-2 text-lg font-medium">Terbit otomatis</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Saat waktunya tiba, postingan terbit di akun Anda. Jika gagal, Anda menerima email berisi penyebabnya dan bisa menjadwalkan ulang dengan satu klik.
                    </p>
                </li>
            </ol>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-[1fr_1.2fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Izin Instagram</p>
                <h2 class="mt-4 text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Hanya yang dibutuhkan untuk menerbitkan.</h2>
            </div>

            <div class="space-y-8">
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_basic</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Membaca username, tipe akun, dan foto profil, supaya akun yang benar tampil di daftar Anda.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_content_publish</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Menerbitkan foto dan caption yang Anda jadwalkan ke akun Anda. Hanya untuk postingan yang Anda buat sendiri di Sosmedhub.</p>
                </div>
                <p class="border-t border-white/10 pt-6 text-sm leading-relaxed text-white/55">
                    Anda bisa memutus akun kapan saja dari menu Akun Sosial atau dari pengaturan Instagram.
                    Selengkapnya ada di <a href="{{ route('data-deletion') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">halaman penghapusan data</a>
                    dan <a href="{{ route('privacy') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">kebijakan privasi</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
