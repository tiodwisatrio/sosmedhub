@extends('layouts.public')

@section('title', 'Penjadwalan Konten Instagram dan Facebook')
@section('description', 'Sosmedhub membantu pemilik usaha menjadwalkan foto, video, dan caption untuk Instagram dan Halaman Facebook, lalu menerbitkannya otomatis pada jam yang dipilih.')

@section('content')
    <section class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto grid max-w-5xl items-center gap-14 md:grid-cols-[1.15fr_1fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Penjadwalan konten Instagram dan Facebook</p>
                <h1 class="mt-5 text-5xl leading-[1.05] md:text-7xl" style="font-family: 'Instrument Serif', serif;">
                    Susun hari ini.<br>Terbit sesuai jadwal.
                </h1>
                <p class="mt-6 max-w-md leading-relaxed text-white/60">
                    Unggah foto atau video, tulis caption, pilih jam terbit dalam WIB. Sosmedhub menerbitkannya ke akun Instagram dan Halaman Facebook usaha Anda secara otomatis, tanpa perlu membuka aplikasi saat waktunya tiba.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @auth
                        {{-- Developer dan user yang sudah disetujui masuk ke dashboard; sisanya melihat status persetujuan. --}}
                        @if (auth()->user()->isDeveloper() || auth()->user()->isApproved())
                            <a href="{{ route('admin.dashboard') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Buka dashboard</a>
                        @else
                            <a href="{{ route('approval.pending') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Lihat status persetujuan</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-full px-5 py-3 text-sm text-white/60 transition hover:text-white">Daftar akun baru</a>
                    @endauth
                </div>
                @guest
                    @unless (config('sosmedhub.registration.auto_approve'))
                        <p class="mt-4 text-xs text-white/35">Akun baru disetujui admin terlebih dahulu.</p>
                    @endunless
                @endguest
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
                        Masuk dengan akun Instagram Business atau Creator, atau pilih Halaman Facebook yang Anda kelola, lewat halaman resmi masing-masing. Kami tidak pernah melihat kata sandi Anda.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Langkah 2</p>
                    <h3 class="mt-2 text-lg font-medium">Susun jadwal</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Pilih akun tujuan dan format Feed, Story, atau Reels, unggah foto atau video, tulis caption, lalu pilih tanggal dan jam dalam WIB. Satu jadwal bisa terbit ke beberapa format sekaligus.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Langkah 3</p>
                    <h3 class="mt-2 text-lg font-medium">Terbit otomatis</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Saat waktunya tiba, postingan terbit di akun Anda. Jika ada format yang gagal, Anda menerima email berisi penyebabnya dan hanya format itu yang diterbitkan ulang saat Anda menjadwalkan ulang.
                    </p>
                </li>
            </ol>
        </div>
    </section>

    @php
        $reelIg = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel'));
        $reelFb = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel', 'facebook'));
        $feedVideo = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('feed'));
    @endphp

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Format yang didukung</p>
            <h2 class="mt-4 max-w-xl text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Satu jadwal, ke platform yang Anda pilih.</h2>

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">
                    <p class="text-lg font-medium">Instagram</p>
                    <p class="mt-1 text-xs text-white/40">Akun Business atau Creator</p>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Feed</dt>
                            <dd class="text-right text-white/55">Foto atau video; carousel campuran hingga 10 item (video maks {{ $feedVideo }})</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Story</dt>
                            <dd class="text-right text-white/55">Foto atau video hingga 60 detik</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Reels</dt>
                            <dd class="text-right text-white/55">Video hingga {{ $reelIg }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">
                    <p class="text-lg font-medium">Facebook</p>
                    <p class="mt-1 text-xs text-white/40">Halaman (Page) yang Anda kelola</p>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Feed</dt>
                            <dd class="text-right text-white/55">Satu foto atau album hingga 10 foto</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Story</dt>
                            <dd class="text-right text-white/55">Foto atau video hingga 60 detik</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Reels</dt>
                            <dd class="text-right text-white/55">Video hingga {{ $reelFb }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Data Anda</p>
            <h2 class="mt-4 max-w-xl text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Anda yang memegang kendali.</h2>

            <ul class="mt-12 grid gap-8 md:grid-cols-3">
                <li>
                    <h3 class="text-lg font-medium">Tanpa kata sandi</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Anda masuk lewat halaman resmi Instagram dan Facebook. Kami tidak pernah menerima kata sandi akun media sosial Anda.</p>
                </li>
                <li>
                    <h3 class="text-lg font-medium">Hanya yang Anda buat</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Sosmedhub hanya menerbitkan postingan yang Anda jadwalkan sendiri. Kami tidak membaca pesan, komentar, atau daftar pengikut Anda.</p>
                </li>
                <li>
                    <h3 class="text-lg font-medium">Putus dan hapus kapan saja</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Token akses disimpan terenkripsi dan dihapus saat Anda memutus akun. Penghapusan seluruh data bisa diminta kapan saja.</p>
                </li>
            </ul>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-[1fr_1.2fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Izin Instagram dan Facebook</p>
                <h2 class="mt-4 text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Hanya yang dibutuhkan untuk menerbitkan.</h2>
            </div>

            <div class="space-y-8">
                <p class="text-[10px] uppercase tracking-[0.28em] text-white/35">Instagram</p>
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_basic</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Membaca username, tipe akun, dan foto profil, supaya akun yang benar tampil di daftar Anda.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_content_publish</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Menerbitkan foto, video, dan caption yang Anda jadwalkan ke akun Anda (Feed, Story, dan Reels). Hanya untuk postingan yang Anda buat sendiri di Sosmedhub.</p>
                </div>

                <p class="border-t border-white/10 pt-6 text-[10px] uppercase tracking-[0.28em] text-white/35">Facebook (Halaman)</p>
                <div>
                    <p class="font-mono text-sm text-white">pages_show_list</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Menampilkan daftar Halaman yang Anda kelola, supaya Anda bisa memilih satu untuk dihubungkan.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">pages_read_engagement</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Membaca data dasar Halaman (nama dan foto) serta token Halaman yang diperlukan untuk menerbitkan, dan status pemrosesan video.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">pages_manage_posts</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Menerbitkan foto, video, dan caption yang Anda jadwalkan ke Halaman Anda (Feed, Story, dan Reels). Hanya untuk postingan yang Anda buat sendiri di Sosmedhub.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">business_management</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Menampilkan Halaman yang dimiliki Business Portfolio Anda, yang tidak muncul tanpa izin ini.</p>
                </div>
                <p class="border-t border-white/10 pt-6 text-sm leading-relaxed text-white/55">
                    Anda bisa memutus akun kapan saja dari menu Akun Sosial atau dari pengaturan Instagram dan Facebook.
                    Selengkapnya ada di <a href="{{ route('data-deletion') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">halaman penghapusan data</a>
                    dan <a href="{{ route('privacy') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">kebijakan privasi</a>.
                </p>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Siap menjadwalkan postingan pertama?</h2>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                @auth
                    @if (auth()->user()->isDeveloper() || auth()->user()->isApproved())
                        <a href="{{ route('admin.dashboard') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Buka dashboard</a>
                    @else
                        <a href="{{ route('approval.pending') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Lihat status persetujuan</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-full px-5 py-3 text-sm text-white/60 transition hover:text-white">Daftar akun baru</a>
                @endauth
            </div>
            <p class="mt-8 text-sm text-white/45">Pertanyaan? Hubungi @include('public._contact').</p>
        </div>
    </section>
@endsection
