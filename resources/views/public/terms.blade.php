@extends('layouts.public')

@section('title', 'Ketentuan Layanan')
@section('description', 'Aturan pemakaian Sosmedhub untuk Instagram dan Halaman Facebook: akun, konten, penerbitan otomatis, dan batasan layanan.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Terakhir diperbarui 11 Oktober 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Ketentuan Layanan</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                Dengan mendaftar dan memakai Sosmedhub, Anda menyetujui ketentuan berikut. Jika tidak setuju, mohon jangan memakai layanan ini.
            </p>

            <div class="legal mt-4">
                <h2>Tentang layanan</h2>
                <p>Sosmedhub membantu Anda menjadwalkan foto, video, dan caption, lalu menerbitkannya ke akun Instagram atau Halaman (Page) Facebook Anda pada waktu yang dipilih. Sosmedhub bukan produk Meta, Instagram, atau Facebook dan tidak berafiliasi dengan ketiganya.</p>

                <h2>Akun Anda</h2>
                <ul>
                    <li>Pendaftaran akun baru perlu disetujui admin. Kami dapat menolak, menangguhkan, atau menutup akun yang melanggar ketentuan ini.</li>
                    <li>Anda bertanggung jawab menjaga kerahasiaan kata sandi dan atas semua aktivitas di akun Anda.</li>
                    <li>Akun Instagram yang dihubungkan harus bertipe Business atau Creator, dan harus milik Anda atau Anda berwenang mengelolanya.</li>
                    <li>Facebook yang didukung adalah Halaman (Page), bukan profil pribadi. Halaman yang dihubungkan harus Halaman yang Anda kelola atau berwenang Anda kelola.</li>
                </ul>

                <h2>Konten Anda</h2>
                <ul>
                    <li>Foto, video, dan caption tetap milik Anda. Anda memberi kami izin memprosesnya hanya untuk menyimpan jadwal dan menerbitkannya ke Instagram atau Facebook atas permintaan Anda.</li>
                    <li>Anda bertanggung jawab atas isi yang Anda terbitkan. Konten harus mematuhi hukum yang berlaku serta ketentuan dan pedoman komunitas Instagram dan Facebook.</li>
                    <li>Anda menjamin memiliki hak atas foto, video, dan teks yang Anda unggah.</li>
                </ul>

                <h2>Penerbitan otomatis</h2>
                <ul>
                    <li>Postingan terbit setelah waktu yang Anda pilih, biasanya dalam satu menit. Kami tidak menjamin ketepatan waktu atau keberhasilan penerbitan, karena bergantung pada layanan Instagram dan Facebook.</li>
                    <li>Penerbitan dapat gagal, misalnya karena koneksi akun berakhir, media tidak sesuai syarat platform, atau Instagram atau Facebook menolak permintaan. Anda akan menerima email berisi penyebabnya dan dapat menjadwalkan ulang.</li>
                    <li>Foto harus berformat JPEG. Video harus berformat MP4 atau MOV (H.264 atau HEVC dengan audio AAC). Batas durasi video: Story maksimal 60 detik; Reels maksimal {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel')) }} di Instagram dan {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel', 'facebook')) }} di Facebook; video di dalam carousel Feed Instagram maksimal {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('feed')) }}.</li>
                    <li>Feed Instagram boleh berisi campuran foto dan video, sedangkan Feed Facebook hanya foto. Instagram dan Facebook membatasi jumlah postingan per akun dalam 24 jam, dan batas itu berlaku juga untuk Sosmedhub.</li>
                    <li>Story tidak mendukung caption, stiker, atau tautan lewat penerbitan otomatis. Teks yang ingin tampil di Story harus sudah ada pada gambar atau videonya.</li>
                    <li>Satu jadwal bisa diterbitkan ke beberapa format (Feed, Story, Reels). Tiap format diproses sendiri, sehingga satu format bisa gagal sementara yang lain terbit.</li>
                    <li>Koneksi akun Instagram berlaku terbatas dan diperpanjang otomatis. Koneksi Halaman Facebook dapat berakhir bila izin dicabut atau token dibatalkan oleh Meta. Jika koneksi gagal diperpanjang atau berakhir, Anda perlu menghubungkan ulang akun agar jadwal tetap berjalan.</li>
                </ul>

                <h2>Hal yang tidak boleh dilakukan</h2>
                <ul>
                    <li>Menerbitkan konten yang melanggar hukum, menyesatkan, atau melanggar hak pihak lain.</li>
                    <li>Menghubungkan akun Instagram atau Halaman Facebook milik orang lain tanpa izin.</li>
                    <li>Mengganggu atau membebani layanan, atau mencoba mengakses data pengguna lain.</li>
                </ul>

                <h2>Menghentikan layanan</h2>
                <p>Anda dapat berhenti kapan saja dengan memutus akun Instagram atau Halaman Facebook dan meminta penghapusan data lewat <a href="{{ route('data-deletion') }}">halaman penghapusan data</a>. Kami dapat menghentikan layanan atau akun Anda bila ketentuan ini dilanggar.</p>

                <h2>Batasan tanggung jawab</h2>
                <p>Layanan disediakan apa adanya. Sejauh diizinkan hukum, kami tidak bertanggung jawab atas kerugian tidak langsung, termasuk postingan yang terlambat, tidak terbit, atau berubah tampilan di Instagram atau Facebook akibat sebab di luar kendali kami.</p>

                <h2>Perubahan ketentuan</h2>
                <p>Ketentuan ini dapat berubah. Versi terbaru ditampilkan di halaman ini dengan tanggal pembaruan yang baru. Memakai layanan setelah perubahan berarti Anda menyetujuinya.</p>

                <h2>Hukum yang berlaku</h2>
                <p>Ketentuan ini tunduk pada hukum Republik Indonesia.</p>

                <h2>Kontak</h2>
                <p>Pertanyaan tentang ketentuan ini dapat dikirim ke @include('public._contact'). Cara kami memperlakukan data Anda dijelaskan di <a href="{{ route('privacy') }}">kebijakan privasi</a>.</p>
            </div>
        </div>
    </article>
@endsection
