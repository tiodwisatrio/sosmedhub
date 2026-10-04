@extends('layouts.public')

@section('title', 'Kebijakan Privasi')
@section('description', 'Data apa yang dikumpulkan Sosmedhub, untuk apa dipakai, dan bagaimana Anda bisa menghapusnya.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Terakhir diperbarui 5 Oktober 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Kebijakan Privasi</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                Sosmedhub adalah layanan untuk menjadwalkan dan menerbitkan postingan Instagram. Halaman ini menjelaskan data yang kami kumpulkan saat Anda memakainya, untuk apa data itu dipakai, dan hak Anda atas data tersebut.
            </p>

            <div class="legal mt-4">
                <h2>Data yang kami kumpulkan</h2>
                <p><strong>Data akun Sosmedhub.</strong> Saat mendaftar: nama, alamat email, kata sandi (disimpan dalam bentuk hash, bukan teks asli), dan nomor telepon bila Anda mengisinya.</p>
                <p><strong>Data akun Instagram.</strong> Saat Anda menghubungkan akun: ID akun, username, nama tampilan, foto profil, tipe akun, dan token akses yang diberikan Instagram. Kami tidak menerima atau menyimpan kata sandi Instagram Anda, karena login dilakukan langsung di halaman resmi Instagram.</p>
                <p><strong>Konten yang Anda jadwalkan.</strong> Foto, caption, waktu terbit, status penerbitan, ID postingan di Instagram setelah terbit, dan pesan kesalahan bila penerbitan gagal. Saat diunggah, foto diperkecil ke ukuran yang dipakai Instagram dan metadatanya dihapus, termasuk lokasi GPS dan informasi kamera. File asli tidak kami simpan.</p>
                <p><strong>Data teknis.</strong> Cookie sesi untuk menjaga Anda tetap masuk, serta catatan kesalahan server yang dapat memuat waktu kejadian dan pesan galat. Kami tidak memasang alat analitik atau pelacak iklan.</p>

                <h2>Untuk apa data dipakai</h2>
                <ul>
                    <li>Menjalankan layanan: menampilkan akun Anda, menyimpan jadwal, dan menerbitkan postingan pada waktu yang Anda pilih.</li>
                    <li>Mengirim email terkait layanan, seperti pemberitahuan postingan gagal atau berhasil terbit, dan pemberitahuan bahwa koneksi akun Instagram perlu dihubungkan ulang.</li>
                    <li>Menjaga keamanan akun dan mencegah penyalahgunaan.</li>
                </ul>
                <p>Kami tidak menjual data Anda dan tidak memakainya untuk iklan.</p>

                <h2>Izin Instagram yang diminta</h2>
                <ul>
                    <li><code>instagram_business_basic</code>: membaca username, tipe akun, dan foto profil.</li>
                    <li><code>instagram_business_content_publish</code>: menerbitkan foto dan caption yang Anda jadwalkan ke akun Anda.</li>
                </ul>
                <p>Kami hanya menerbitkan postingan yang Anda buat sendiri di Sosmedhub, dan tidak membaca pesan langsung, komentar, atau daftar pengikut Anda.</p>

                <h2>Dengan siapa data dibagikan</h2>
                <ul>
                    <li><strong>Meta Platforms (Instagram).</strong> Foto dan caption dikirim ke Instagram untuk diterbitkan, dan token akses dipakai untuk memanggil layanannya. Pemrosesan di sisi Instagram tunduk pada kebijakan Meta.</li>
                    <li><strong>Penyedia layanan email.</strong> Nama dan alamat email Anda diteruskan agar email layanan dapat terkirim.</li>
                    <li><strong>Penyedia hosting.</strong> Data disimpan di server penyedia hosting yang kami gunakan.</li>
                </ul>
                <p>Selain itu, data hanya dibuka bila diwajibkan oleh hukum yang berlaku.</p>

                <h2>Keamanan</h2>
                <p>Token akses Instagram disimpan dalam bentuk terenkripsi dan tidak ditampilkan di antarmuka maupun catatan kesalahan. Akses ke aplikasi memerlukan login, dan akun baru harus disetujui admin sebelum dapat dipakai. Tidak ada sistem yang sepenuhnya bebas risiko, tetapi kami berupaya menjaga data Anda dengan wajar.</p>

                <h2>Berapa lama data disimpan</h2>
                <p>Data disimpan selama akun Anda aktif. Versi foto untuk Instagram dihapus {{ config('scheduler.media.publish_retention_days', 30) }} hari setelah postingan terbit; yang tetap disimpan hanya versi kecil untuk riwayat. Saat Anda memutus akun Instagram, token akses langsung dihapus dan postingan terjadwal untuk akun itu tidak akan terbit. Anda dapat meminta penghapusan seluruh data kapan saja, dengan cara yang dijelaskan di <a href="{{ route('data-deletion') }}">halaman penghapusan data</a>.</p>

                <h2>Hak Anda</h2>
                <p>Anda berhak mengetahui data apa yang kami simpan, meminta perbaikan, dan meminta penghapusan data pribadi Anda, sesuai peraturan perlindungan data pribadi yang berlaku di Indonesia. Anda juga dapat mencabut izin Sosmedhub kapan saja lewat pengaturan Instagram.</p>

                <h2>Perubahan kebijakan</h2>
                <p>Bila kebijakan ini berubah, versi terbaru ditampilkan di halaman ini dengan tanggal pembaruan yang baru.</p>

                <h2>Kontak</h2>
                <p>Pertanyaan tentang privasi dapat dikirim ke @include('public._contact').</p>
            </div>
        </div>
    </article>
@endsection
