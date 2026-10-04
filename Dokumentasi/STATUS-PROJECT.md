# Status Project Sosmedhub

Dokumen ini merangkum keadaan sistem per **5 Oktober 2026** dan daftar langkah berikutnya. Acuan produk tetap [PRD_Sosmedhub.md](../PRD_Sosmedhub.md); aturan penulisan kode ada di [CLAUDE.md](../CLAUDE.md).

## 1. Ringkasan

Sosmedhub adalah aplikasi untuk menjadwalkan postingan Instagram. Pengguna menghubungkan akun Instagram lewat OAuth, membuat jadwal berisi foto dan caption, lalu sistem menerbitkannya otomatis lewat Instagram API.

Alur utama sudah berjalan end-to-end di lokal: hubungkan akun, buat jadwal, cron mengirim ke antrean, job menerbitkan ke Instagram, postingan muncul di profil. Aplikasi belum di-deploy.

## 2. Status fitur

| Fitur | Status |
|---|---|
| Login, registrasi, dan persetujuan user oleh admin | Jalan |
| Hubungkan akun Instagram lewat OAuth (token long-lived, terenkripsi) | Jalan, sudah dicoba dengan akun asli |
| Satu user dengan banyak akun Instagram | Didukung (lihat bagian 9 nomor 1) |
| Buat, ubah, batalkan, hapus jadwal; foto lebih dari satu (carousel); upload hanya JPEG | Jalan |
| Kalender mingguan (halaman Penjadwalan) | Jalan |
| Halaman Riwayat terpisah: tab status, filter akun, pencarian caption | Jalan |
| Publikasi otomatis (cron + antrean) | Jalan, sudah terbit ke Instagram asli |
| Jadwalkan ulang postingan gagal | Jalan |
| Duplikat postingan (menjadi draf) | Jalan |
| Email postingan gagal | Jalan, sudah diterima di inbox |
| Email postingan berhasil | Terpasang, belum dites manual |
| Email akun terputus dan peringatan token hampir habis | Terpasang, belum dites manual |
| Refresh token otomatis harian | Terpasang, belum dites dengan Instagram asli |
| Halaman publik: beranda, kebijakan privasi, ketentuan layanan, penghapusan data | Jalan (isi hardcode, lihat bagian 10) |
| Batas akun atau jumlah post per user | Belum ada (sengaja, menunggu model bisnis) |
| Facebook Page, Reels, Story, insight | Belum ada (di luar MVP) |

## 3. Arsitektur

Aplikasi memakai Laravel 13 dengan modul (`nwidart/laravel-modules`), Livewire, Tailwind, dan Spatie Permission. Modul yang aktif:

| Modul | Isi |
|---|---|
| `Dashboard`, `User`, `Role`, `Menu`, `SiteSetting` | Dipertahankan dari template |
| `SocialAccount` | Model akun, OAuth Instagram (`InstagramGraphService`), publikasi dan refresh token (`InstagramPublisher`), command refresh token, notifikasi akun terputus |
| `Scheduler` | Model jadwal dan foto, controller dan view (Penjadwalan di `ScheduledPostController`, Riwayat di `PostHistoryController`), job `PublishScheduledPostJob`, command `scheduler:dispatch-due`, notifikasi gagal dan berhasil |

### Alur menghubungkan akun
1. User klik **Hubungkan Instagram**, lalu diarahkan ke halaman login Instagram.
2. Instagram mengembalikan `code` ke `/admin/social-accounts/instagram/callback`.
3. Server menukar `code` menjadi token, menukarnya lagi menjadi token long-lived (60 hari), mengambil profil lewat `/me`, lalu menyimpan akun dengan token terenkripsi.

### Alur menerbitkan postingan
1. Cron `schedule:run` tiap menit menjalankan `scheduler:dispatch-due`. Command ini memilih jadwal berstatus `scheduled` yang waktunya sudah lewat dan mengirim satu job per jadwal ke antrean.
2. Job mengklaim jadwal dengan mengubah status `scheduled` menjadi `publishing` (mencegah terbit dua kali), lalu memanggil Instagram: buat container, tunggu status `FINISHED`, publish. Satu foto memakai `image_url`, lebih dari satu memakai carousel.
3. Berhasil: status `published`, `ig_media_id` dan `published_at` terisi. Gagal: status `failed`, pesan error tersimpan (maksimal 500 karakter, tanpa token).
4. Job tidak diulang otomatis (`tries = 1`) agar tidak ada postingan ganda. Pengulangan dilakukan manual lewat **Jadwalkan Ulang**.

### Status postingan
`draft` (hasil duplikasi), `scheduled`, `publishing`, `published`, `failed`, `cancelled`.
Yang boleh diubah: `scheduled`, `failed`, `draft`. Menyimpan postingan `failed` atau `draft` mengembalikannya ke `scheduled`.

### Komponen pratinjau
Pratinjau ala Instagram dan kartu ringkasan jadwal ada di komponen `<x-scheduler::post-preview>` (`Modules/Scheduler/resources/views/components/post-preview.blade.php`), dipakai halaman buat dan ubah. Komponen membaca state Alpine dari elemen induknya, jadi harus berada di dalam `x-data` yang menyediakan `previewList`, `currentIndex`, `caption`, `schedulePreview`, dan `photoCountLabel`. Props Blade: `username`, `app-name`, `app-initial`, `logo-url`.

### Halaman Riwayat
`/admin/post-history` (route `admin.post-history.index`, izin `scheduler.view`) berisi postingan yang sudah lewat antrean: status selain `scheduled`, atau `scheduled` yang waktunya sudah lewat. Ada tab status (Semua, Terbit, Gagal, Dibatalkan, Draf) dengan jumlahnya, filter akun, dan pencarian caption. Halaman Penjadwalan hanya menampilkan kalender dan ringkasan jumlah postingan gagal dengan tautan ke Riwayat. Nama route sengaja tidak memakai awalan `admin.scheduled-posts.` supaya menu Penjadwalan tidak ikut aktif. Menu Riwayat ditambahkan di `MenuDatabaseSeeder`; di server yang sudah punya data menu, tambahkan lewat halaman Menu (route `admin.post-history.index`, pola aktif `admin.post-history.*`, izin `scheduler.view`) karena seeder menu menghapus seluruh tabel menu.

### Notifikasi email (lewat antrean)
- **Postingan gagal**: selalu dikirim, berisi penyebab dan tombol Jadwalkan ulang.
- **Postingan berhasil**: dikirim kecuali `SCHEDULER_NOTIFY_PUBLISHED=false`.
- **Akun terputus**: dikirim saat Instagram menolak token (error 190), dan saat refresh token gagal setelah token habis. Akun ditandai `expired`.
- **Token hampir habis**: dikirim bila refresh gagal dan sisa waktu 3 hari atau kurang.

## 4. Data

Tabel milik produk: `users`, `social_accounts`, `scheduled_posts`, `scheduled_post_media`.

```
users 1──N social_accounts
users 1──N scheduled_posts
social_accounts 1──N scheduled_posts   (social_account_id boleh kosong)
scheduled_posts 1──N scheduled_post_media
```

- `social_accounts.access_token` terenkripsi (cast `encrypted`). `provider_account_id` unik per platform.
- Waktu disimpan UTC dan ditampilkan WIB.
- Tabel pendukung: `roles`, `permissions`, `model_has_*`, `role_has_permissions`, `menus`, `site_settings`, `jobs`.
- **Tabel sisa CMS lama** masih ada di database lokal: `banners`, `categories`, `faqs`, `heroes`, `keunggulans`, `kliens`, `layanans`, `pakets`, `posts`, `teams`. Modul dan migration-nya sudah tidak ada, jadi server baru tidak akan membuatnya.

## 5. Role dan izin

- **developer**: semua izin, melihat semua data.
- **client**: izin `scheduler.*` dan `social-account.*`. Hanya melihat data miliknya.
- User baru berstatus `pending` sampai disetujui admin (middleware `approved`).

## 6. Konfigurasi `.env`

| Variabel | Keterangan |
|---|---|
| `APP_URL` | Alamat publik. **Menentukan alamat foto yang diambil Meta**, jadi harus domain yang bisa dijangkau internet saat publish. |
| `INSTAGRAM_CLIENT_ID` | ID aplikasi **Instagram** (bukan App ID Meta). |
| `INSTAGRAM_CLIENT_SECRET` | Rahasia aplikasi Instagram, 32 karakter heksadesimal. |
| `INSTAGRAM_REDIRECT_URI` | Harus **persis sama** dengan URL redirect di dashboard Meta. Formatnya `https://DOMAIN/admin/social-accounts/instagram/callback`. |
| `INSTAGRAM_GRAPH_VERSION` | Versi Graph API, misalnya `v26.0`. |
| `QUEUE_CONNECTION` | `database`. |
| `MAIL_*` | Resend lewat SMTP. API key di `MAIL_PASSWORD`; domain pengirim harus terverifikasi di Resend. |
| `SCHEDULER_NOTIFY_PUBLISHED` | `true` atau `false`. |
| `DEVELOPER_NAME`, `DEVELOPER_EMAIL`, `DEVELOPER_PASSWORD` | Akun developer awal untuk `db:seed`. Dibaca lewat `config/sosmedhub.php`. Password hanya wajib saat akun belum ada; akun yang sudah ada tidak diubah. |

## 7. Perintah penting

```bash
php artisan schedule:work                       # lokal: menjalankan scheduler
php artisan queue:work --tries=1                # lokal: memproses antrean
php artisan scheduler:dispatch-due              # kirim jadwal jatuh tempo ke antrean
php artisan social-accounts:refresh-tokens      # perpanjang token yang hampir habis
php artisan queue:retry all                     # kirim ulang job yang gagal
php artisan test                                # 134 test otomatis
```

Setelah mengubah `.env`, jalankan `php artisan config:clear` dan **restart `queue:work`** (worker menyimpan config di memori).

## 8. Pengujian

**Otomatis**: 134 test lulus, mencakup penjadwalan, publikasi (dengan `Http::fake`), notifikasi, refresh token, duplikasi, dan jadwal ulang.

**Manual** (butuh server publik karena Meta harus bisa mengambil foto):

| # | Skenario | Status |
|---|---|---|
| A | Postingan gagal, status `failed`, email gagal masuk | Selesai |
| B | Jadwalkan ulang postingan gagal sampai terbit dan email berhasil masuk | Belum (butuh domain publik) |
| C | Duplikat, jadi draf, atur waktu, jadi `scheduled` | Belum |
| D | Akun terputus (cabut akses app di Instagram), dua email, akun `expired`, hubungkan ulang | Belum |
| E | Refresh token asli (set `token_expires_at` ke 5 hari lagi, jalankan command) | Belum |
| F | User tanpa akun melihat pesan dan tombol nonaktif | Belum |

Kendala tes: `herd share` memakai Expose Free yang membatasi sesi (tunggu sekitar 40 menit antar sesi). Itu alasan deploy ke domain publik lebih praktis.

## 9. Masalah yang diketahui

1. **Satu akun Instagram yang sama dihubungkan oleh user berbeda.** Keputusan saat ini: dibiarkan, karena bisa ada dua client yang memakai satu akun, dan developer memakai akun yang sama untuk tes sebagai developer dan client. Perilakunya: tabel `social_accounts` unik per `platform` + `provider_account_id`, jadi saat user B menghubungkan akun yang sudah dihubungkan user A, baris yang sama berpindah ke user B (`user_id` berubah). User A tidak lagi melihat akun itu di daftarnya, dan jadwal lama A tetap terkait ke akun tersebut. Jika nanti dua user perlu memakai akun yang sama bersamaan, ubah unik menjadi per `user_id` + `platform` + `provider_account_id` (satu baris dan satu token per user), atau tolak dengan pesan yang jelas.
2. Tabel sisa CMS lama di database lokal (lihat bagian 4).
3. `CLAUDE.md` sudah ada di `.gitignore`, tetapi masih dilacak git karena pernah di-commit. Untuk berhenti melacaknya: `git rm --cached CLAUDE.md`, lalu commit.
4. Banyak perubahan belum di-commit (modul `SocialAccount`, `Scheduler`, migration `avatar_url`, jadwal ulang, duplikasi, notifikasi, JPEG saja, seeder dari `.env`).
5. Mode Development Meta: hanya akun dengan role **Instagram Tester** yang bisa dihubungkan.
6. `DEVELOPER_PASSWORD` di `.env` lokal masih kosong. Tidak masalah selama akun developer sudah ada di database; isi sebelum menjalankan seeder di database baru.

## 10. Langkah berikutnya

### Sebelum deploy
1. ~~Batasi upload ke JPEG~~ (selesai).
2. ~~Password developer dari `.env`~~ (selesai). Di server, isi `DEVELOPER_EMAIL` dan `DEVELOPER_PASSWORD` yang kuat sebelum `db:seed`.
3. Putuskan perilaku akun Instagram yang sama dipakai banyak user (bagian 9 nomor 1).
4. Rapikan halaman Akun Sosial: tombol **Hubungkan Akun** langsung ke OAuth, badge dan tombol **Hubungkan Ulang** untuk akun `expired`, hapus form manual untuk client.
5. ~~Halaman publik~~ (selesai, lihat di bawah). **Isi email kontak di Pengaturan Situs** sebelum App Review; halaman hukum membacanya, dan menampilkan penanda kuning jika kosong.
6. Commit semua perubahan dan push.

Halaman publik (isi hardcode di `resources/views/public/`): `/` (beranda untuk tamu; user yang sudah masuk diarahkan ke dashboard), `/kebijakan-privasi`, `/ketentuan-layanan`, `/hapus-data`. Tautan ke tiga halaman hukum ada di footer. URL yang diisi di dashboard Meta: Privacy Policy `https://DOMAIN/kebijakan-privasi`, Terms `https://DOMAIN/ketentuan-layanan`, Data Deletion `https://DOMAIN/hapus-data` (tipe instruksi, bukan callback). Baca ulang isinya sebelum review; komitmen seperti "paling lama 30 hari" bisa diubah sesuai kemampuan Anda.

### Deploy awal di shared hosting
Rencana: shared hosting dulu sampai sekitar 10 client.
- Pastikan hosting punya PHP 8.3, cron tiap 1 menit, dan document root bisa diarahkan ke `public`.
- Cron tiap menit:
  ```
  * * * * * cd /home/USER/sosmedhub && php artisan schedule:run >> /dev/null 2>&1
  * * * * * cd /home/USER/sosmedhub && php artisan queue:work --stop-when-empty --tries=1 --max-time=50 >> /dev/null 2>&1
  ```
- Jalankan `npm run build` dan `composer install --no-dev` di laptop jika server tidak punya Node atau Composer, lalu upload.
- Pastikan `/storage/...` terbuka publik dan tidak diblokir (hotlink protection, ModSecurity). Jika `storage:link` diblokir, arahkan disk `public` ke folder di dalam `public/`.
- `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` dan `INSTAGRAM_REDIRECT_URI` memakai domain produksi, `APP_KEY` baru.
- Daftarkan URL redirect produksi di dashboard Meta (langkah 4, Siapkan login bisnis Instagram).
- Verifikasi domain pengirim di Resend.
- Jalankan `migrate --force` dan `db:seed`, login, lalu hubungkan ulang akun Instagram.
- Jalankan manual tes B sampai F di server.

### Setelah itu
- Tambahkan client awal sebagai **Instagram Tester** di dashboard Meta (client menerima undangan di Settings, Apps and websites, Tester invites).
- Siapkan syarat **App Review** supaya client bisa mendaftar dan menghubungkan akun sendiri: halaman Privacy Policy, Terms, dan Data Deletion sudah ada; tinggal screencast alur lengkap, dan alasan tiap permission (`instagram_business_basic`, `instagram_business_content_publish`). Mode Live saja tidak cukup; yang membuka akses umum adalah Advanced Access lewat App Review.
- Putuskan model bisnis (batas akun atau antrean per paket), lalu pasang batas di dua tempat: saat menghubungkan akun dan saat validasi jadwal.
- Pertimbangkan fitur lanjutan: draf mandiri, template caption, pustaka media, Reels, insight, tim dan persetujuan konten, billing (Midtrans atau Xendit).
- Pindah ke VPS dengan Supervisor untuk antrean jika client sudah sekitar 10 dan aktif, atau jika publikasi sering telat.

## 11. Catatan penting tentang Meta

- Permission yang dipakai: `instagram_business_basic` dan `instagram_business_content_publish`.
- Akun Instagram harus bertipe **Business** atau **Creator**.
- Foto wajib berupa URL publik yang dapat diambil server Meta. Domain lokal (`.test`) tidak bisa.
- Batas sekitar 100 postingan per 24 jam per akun dari Instagram.
- Token long-lived berlaku 60 hari dan hanya bisa di-refresh jika umurnya lebih dari 24 jam dan belum kedaluwarsa.
- Webhook tidak dipakai di MVP, jadi langkah webhook di dashboard Meta boleh dikosongkan.
