@extends('layouts.public')

@section('title', 'Kebijakan Privasi')
@section('description', 'Data apa yang dikumpulkan Sosmedhub dari akun Instagram dan Halaman Facebook Anda, untuk apa dipakai, dan bagaimana Anda bisa menghapusnya.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Terakhir diperbarui 11 Oktober 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Kebijakan Privasi</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                Sosmedhub adalah layanan untuk menjadwalkan dan menerbitkan postingan ke akun Instagram dan Halaman (Page) Facebook. Halaman ini menjelaskan data yang kami kumpulkan saat Anda memakainya, untuk apa data itu dipakai, dan hak Anda atas data tersebut.
            </p>

            <div class="legal mt-4">
                <h2>Data yang kami kumpulkan</h2>
                <p><strong>Data akun Sosmedhub.</strong> Saat mendaftar: nama, alamat email, kata sandi (disimpan dalam bentuk hash, bukan teks asli), dan nomor telepon bila Anda mengisinya.</p>
                <p><strong>Data akun Instagram.</strong> Saat Anda menghubungkan akun: ID akun, username, nama tampilan, foto profil, tipe akun, dan token akses yang diberikan Instagram. Kami tidak menerima atau menyimpan kata sandi Instagram Anda, karena login dilakukan langsung di halaman resmi Instagram.</p>
                <p><strong>Data Halaman Facebook.</strong> Saat Anda menghubungkan Halaman: ID Halaman, nama Halaman, foto Halaman, dan token akses Halaman yang diberikan Facebook. Kami hanya mendukung Halaman, bukan profil pribadi. Token akses pengguna Facebook hanya dipakai sebentar, tersimpan terenkripsi di sesi Anda sampai Anda memilih Halaman, lalu tidak disimpan. Kami tidak menerima atau menyimpan kata sandi Facebook Anda, karena login dilakukan langsung di halaman resmi Facebook.</p>
                <p><strong>Konten yang Anda jadwalkan.</strong> Foto, video, caption, waktu terbit, status penerbitan, ID postingan di Instagram atau Facebook setelah terbit, dan pesan kesalahan bila penerbitan gagal. Saat diunggah, foto diperkecil ke ukuran yang dipakai platform dan metadatanya dihapus, termasuk lokasi GPS dan informasi kamera; file foto asli tidak kami simpan. Video (untuk Feed, Story, dan Reels) disimpan sebagaimana diunggah, tanpa diubah. Untuk Instagram, platform mengambil media dari alamat penyimpanan kami saat penerbitan; untuk Facebook, file dikirim langsung dari server kami ke Facebook.</p>
                <p><strong>Data teknis.</strong> Cookie sesi untuk menjaga Anda tetap masuk, serta catatan kesalahan server yang dapat memuat waktu kejadian dan pesan galat. Kami tidak memasang alat analitik atau pelacak iklan.</p>

                <h2>Untuk apa data dipakai</h2>
                <ul>
                    <li>Menjalankan layanan: menampilkan akun Anda, menyimpan jadwal, dan menerbitkan postingan pada waktu yang Anda pilih.</li>
                    <li>Mengirim email terkait layanan, seperti pemberitahuan postingan gagal atau berhasil terbit, dan pemberitahuan bahwa koneksi akun Instagram atau Halaman Facebook perlu dihubungkan ulang.</li>
                    <li>Menjaga keamanan akun dan mencegah penyalahgunaan.</li>
                </ul>
                <p>Kami tidak menjual data Anda dan tidak memakainya untuk iklan.</p>

                <h2>Izin yang diminta</h2>
                <p><strong>Instagram</strong></p>
                <ul>
                    <li><code>instagram_business_basic</code>: membaca username, tipe akun, dan foto profil.</li>
                    <li><code>instagram_business_content_publish</code>: menerbitkan foto, video, dan caption yang Anda jadwalkan ke akun Anda (Feed, Story, dan Reels).</li>
                </ul>
                <p><strong>Facebook</strong></p>
                <ul>
                    <li><code>pages_show_list</code>: menampilkan daftar Halaman yang Anda kelola agar Anda bisa memilih satu.</li>
                    <li><code>pages_read_engagement</code>: membaca data dasar Halaman (nama, foto) dan token Halaman yang diperlukan untuk menerbitkan, serta status pemrosesan video yang diunggah.</li>
                    <li><code>pages_manage_posts</code>: menerbitkan foto, video, dan caption yang Anda jadwalkan ke Halaman Anda (Feed, Story, dan Reels).</li>
                    <li><code>business_management</code>: menampilkan Halaman yang dimiliki Business Portfolio Anda, yang tidak muncul tanpa izin ini.</li>
                </ul>
                <p>Kami hanya menerbitkan postingan yang Anda buat sendiri di Sosmedhub, dan tidak membaca pesan langsung, komentar, daftar pengikut, atau data iklan Anda.</p>

                <h2>Dengan siapa data dibagikan</h2>
                <ul>
                    <li><strong>Meta Platforms (Instagram dan Facebook).</strong> Foto, video, dan caption dikirim ke Instagram atau Facebook untuk diterbitkan, dan token akses dipakai untuk memanggil layanannya. Pemrosesan di sisi Meta tunduk pada kebijakan Meta.</li>
                    <li><strong>Penyedia layanan email.</strong> Nama dan alamat email Anda diteruskan agar email layanan dapat terkirim.</li>
                    <li><strong>Penyedia hosting.</strong> Data disimpan di server penyedia hosting yang kami gunakan.</li>
                </ul>
                <p>Selain itu, data hanya dibuka bila diwajibkan oleh hukum yang berlaku.</p>

                <h2>Keamanan</h2>
                <p>Token akses Instagram dan token Halaman Facebook disimpan dalam bentuk terenkripsi dan tidak ditampilkan di antarmuka maupun catatan kesalahan. Akses ke aplikasi memerlukan login, dan akun baru harus disetujui admin sebelum dapat dipakai. Tidak ada sistem yang sepenuhnya bebas risiko, tetapi kami berupaya menjaga data Anda dengan wajar.</p>

                <h2>Berapa lama data disimpan</h2>
                <p>Data disimpan selama akun Anda aktif. Foto dan video yang diunggah untuk diterbitkan dihapus {{ config('scheduler.media.publish_retention_days', 30) }} hari setelah postingan terbit; yang tetap disimpan hanya versi kecil foto untuk riwayat. Saat Anda memutus akun Instagram atau Halaman Facebook, token akses langsung dihapus dan postingan terjadwal untuk akun itu tidak akan terbit. Anda dapat meminta penghapusan seluruh data kapan saja, dengan cara yang dijelaskan di <a href="{{ route('data-deletion') }}">halaman penghapusan data</a>.</p>

                <h2>Hak Anda</h2>
                <p>Anda berhak mengetahui data apa yang kami simpan, meminta perbaikan, dan meminta penghapusan data pribadi Anda, sesuai peraturan perlindungan data pribadi yang berlaku di Indonesia. Anda juga dapat mencabut izin Sosmedhub kapan saja lewat pengaturan Instagram atau Facebook.</p>

                <h2>Perubahan kebijakan</h2>
                <p>Bila kebijakan ini berubah, versi terbaru ditampilkan di halaman ini dengan tanggal pembaruan yang baru.</p>

                <h2>Kontak</h2>
                <p>Pertanyaan tentang privasi dapat dikirim ke @include('public._contact').</p>
            </div>
        </div>
    </article>
@endsection
