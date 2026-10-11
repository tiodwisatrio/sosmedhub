# Status Project Sosmedhub

Dokumen ini merangkum keadaan sistem per **10 Oktober 2026** dan daftar langkah berikutnya. Acuan produk tetap [PRD_Sosmedhub.md](../PRD_Sosmedhub.md); aturan penulisan kode ada di [CLAUDE.md](../CLAUDE.md).

## 1. Ringkasan

Sosmedhub adalah aplikasi untuk menjadwalkan postingan Instagram dan Facebook Page. Pengguna menghubungkan akun lewat OAuth, membuat jadwal berisi foto atau video dan caption untuk **satu akun tujuan**, lalu sistem menerbitkannya otomatis lewat Instagram API atau Graph API Facebook.

Alur utama sudah berjalan end-to-end di lokal untuk Instagram: hubungkan akun, buat jadwal, cron mengirim ke antrean, job menerbitkan ke Instagram, postingan muncul di profil. Publikasi Feed (foto) ke Facebook Page sudah dibangun dan **berhasil dites ke Page asli (10 Oktober 2026)**. Aplikasi belum di-deploy.

## 2. Status fitur

| Fitur | Status |
|---|---|
| Login, registrasi, dan persetujuan user oleh admin | Jalan |
| Hubungkan akun Instagram lewat OAuth (token long-lived, terenkripsi) | Jalan, sudah dicoba dengan akun asli |
| Satu user dengan banyak akun Instagram | Didukung (lihat bagian 9 nomor 1) |
| Buat, ubah, batalkan, hapus jadwal; foto lebih dari satu (carousel); foto hanya JPEG | Jalan |
| Feed Instagram campuran foto dan video dalam satu carousel (2-10 item) | Jalan: sudah dites ke Instagram asli oleh pengembang (11 Oktober 2026). Batas video 60 detik/100 MB masih konservatif. Facebook Feed tetap foto saja |
| Format Feed, Story, dan Reels (satu jadwal boleh lebih dari satu format), unggahan video MP4/MOV | Jalan: Feed, Story, dan Reels sudah diterbitkan ke Instagram asli oleh pengembang (11 Oktober 2026) |
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
| Connect Facebook Page | Jalan: Facebook Login, pilih Halaman, token Halaman terenkripsi |
| Publikasi Feed (foto) ke Facebook Page | Jalan: sudah dites ke Page asli oleh pengembang (foto), dan teruji dengan HTTP palsu (satu foto, beberapa foto, token ditolak). Feed, Story, dan Reels sudah dites di Page asli (11 Oktober 2026). Jalur token ditolak belum dites di Page asli |
| Publikasi Story (foto) ke Facebook Page | Jalan: sudah dites ke Page asli oleh pengembang (11 Oktober 2026) dan teruji dengan HTTP palsu (`/photo_stories`) |
| Publikasi Story (video) ke Facebook Page | Jalan: sudah dites ke Page asli oleh pengembang (11 Oktober 2026, video 12 MB) dan teruji dengan HTTP palsu (`/video_stories`) |
| Publikasi Reels ke Facebook Page | Jalan: sudah dites ke Page asli oleh pengembang (11 Oktober 2026) dan teruji dengan HTTP palsu (`/video_reels`). Video Feed untuk Facebook belum ada |
| Pilih akun tujuan lewat kartu (Instagram dulu, lalu Facebook; foto profil, nama, logo platform) | Jalan |
| Potong video Story lebih dari 60 detik (pilih rentang di form, dipotong ffmpeg di server saat simpan) | Terpasang dan teruji dengan ffmpeg palsu; **belum dicoba dengan ffmpeg asli** |
| Pratinjau per platform (Instagram Feed/Story/Reels, Facebook Feed) dengan foto akun, dan pratinjau layar penuh di ponsel | Jalan; tampilan ponsel belum diperiksa di perangkat nyata |
| Threads, insight | Belum ada (di luar MVP; kartu Threads hanya tampilan) |

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
1. Cron `schedule:run` menjalankan `scheduler:dispatch-due`, yang memilih jadwal berstatus `scheduled` yang waktunya sudah lewat dan mengirim `PublishScheduledPostJob` per jadwal.
2. `PublishScheduledPostJob` mengklaim jadwal (`scheduled` menjadi `publishing`, mencegah terbit dua kali) lalu mengirim **satu `PublishPublicationJob` per format** (Feed, Story, Reels) yang belum terbit.
3. `PublishPublicationJob` memanggil `PublicationRunner`, yang menjalankan satu format langkah demi langkah dan menyimpan kemajuannya di kolom `state` (container Instagram dan ID hasil terbit per item), jadi pemanggilan berikutnya melanjutkan tanpa container ganda atau terbit dua kali:
   - **Feed**: satu foto memakai `image_url` + caption; lebih dari satu memakai carousel (container anak dibuat dulu; induk baru dibuat setelah semua anak `FINISHED`). Item carousel boleh foto atau video: foto memakai `image_url`, video memakai `media_type=VIDEO` + `video_url`, keduanya dengan `is_carousel_item=true`, dan video membuat job menunggu seperti Story/Reels. **Satu video saja di Feed** diterbitkan sebagai `REELS` dengan `share_to_feed=true` (Instagram tidak lagi menerbitkan video tunggal sebagai post Feed biasa).
   - **Story**: satu container `STORIES` per item (foto atau video), **tanpa caption**, masing-masing diterbitkan sendiri.
   - **Reels**: satu container `REELS` dengan `video_url`, caption, dan `share_to_feed`.
   Akun **Facebook** dijalankan oleh `FacebookPublicationRunner` (job yang sama memilihnya menurut `social_accounts.platform`), format Feed (foto), Story (foto atau video), dan Reels: satu foto lewat `POST /{page-id}/photos` (file diunggah langsung dari server, tanpa URL publik); beberapa foto diunggah `published=false` lalu digabung lewat satu `POST /{page-id}/feed` dengan `attached_media`. **Story**: tiap foto diunggah `published=false`, lalu diterbitkan satu per satu lewat `POST /{page-id}/photo_stories` (`photo_id`), tanpa caption. **Video Story** memakai alur bertahap `/{page-id}/video_stories`: `upload_phase=start` (mengembalikan `video_id` dan `upload_url` di `rupload.facebook.com`), file diunggah langsung dari server (header `Authorization: OAuth`, `offset`, `file_size`; tanpa URL publik), status dicek lewat `GET /{video_id}?fields=status` sampai unggahan tercatat lengkap (`video_status=upload_complete`), lalu `upload_phase=finish`. **Facebook baru memproses video setelah `finish`** (sebelum itu `processing_phase` tetap `not_started`), jadi runner tidak boleh menunggu status `ready` sebelum `finish`. Bila unggahan belum tercatat lengkap, runner mengembalikan "menunggu" dan job melepas diri seperti Instagram (batas total 10 menit, `scheduler.video.poll_max_minutes`). `container_id` menyimpan ID foto atau `video_id`, `published_id` ID Story, dan `upload_url`/`uploaded` melacak tahap unggah, per item, jadi pemanggilan ulang tidak mengunggah atau menerbitkan ulang yang sudah selesai. Hasilnya langsung jadi, jadi tidak pernah "menunggu". Kemajuan disimpan di `state` dengan bentuk yang sama (ID foto per foto, ID postingan segera setelah terbit), jadi pemanggilan ulang tidak mengunggah ulang foto atau menerbitkan dua kali. **Reels** memakai alur yang sama lewat `/{page-id}/video_reels`, dengan `finish` berisi `video_state=PUBLISHED` dan caption sebagai `description`. `finish` hanya membalas `success` tanpa ID, jadi ID Reels adalah `video_id`; runner lalu menunggu `publish_status=published` paling lama 2 menit, dan bila belum terkonfirmasi, `finish` yang sudah diterima dianggap terbit (mengulang berisiko menerbitkan dua kali). Galat pemrosesan yang muncul sebelum itu (rasio, resolusi, durasi, fps) menggagalkan postingan dengan pesan Facebook.
4. Instagram memproses video dalam hitungan menit. Runner mengembalikan "menunggu" dan job **melepas diri** (`release`, jeda `SCHEDULER_VIDEO_POLL_SECONDS`, bawaan 30 detik) tanpa menahan worker; batas total 10 menit, lewat itu format dinyatakan gagal. Foto dicek berkala di dalam satu pemanggilan.
5. Setelah semua format selesai, status jadwal ditetapkan sekali: semua terbit menjadi `published`; sebagian menjadi `partial`; semua gagal menjadi `failed`. Email dikirim sekali (berhasil, atau gagal/sebagian berisi galat per format).
6. Tidak ada percobaan ulang otomatis. **Jadwalkan Ulang** hanya mengulang format yang gagal: item Story yang sudah terbit tidak diterbitkan lagi, container lama dilepas (bisa sudah kedaluwarsa), dan format yang sudah terbit terkunci (media dan formatnya tidak bisa diubah).

### Format Feed, Story, Reels
| | Feed | Story | Reels |
|---|---|---|---|
| Isi | 1-10 item: foto JPEG, atau video MP4/MOV (hanya Instagram) | 1-10 item, tiap item satu Story (foto JPEG atau video) | tepat 1 video |
| Caption | wajib | tidak didukung Instagram (disembunyikan di form) | opsional |
| Rasio | 4:5 sampai 1,91:1 (dipaksa) | 9:16 disarankan (tidak dipaksa) | 9:16 disarankan |
| Video | - | 3-60 detik, maks 100MB; video lebih panjang dipotong di form (lihat bagian bawah), sumber maks 500MB | 3 detik sampai `SCHEDULER_REEL_MAX_SECONDS` (bawaan 3 menit; Meta mengizinkan 15 menit), maks 300MB |

**Video di Feed** (Instagram): MP4/MOV dengan syarat codec, audio, dan frame rate yang sama seperti Story, durasi 3 sampai `SCHEDULER_FEED_VIDEO_MAX_SECONDS` detik (bawaan 60) dan maks 100 MB. Batas 60 detik/100 MB adalah **tebakan konservatif**: dokumentasi Meta tidak menyebut syarat video item carousel. Naikkan setelah dites di akun asli. Rasio video dalam carousel belum diperiksa; Instagram memotong semua item mengikuti item pertama.

**Urutan media** (Feed dan Story) diatur dengan seret-lepas (HTML5 native, tanpa dependensi) atau tombol panah untuk layar sentuh dan keyboard. Urutan mencampur media tersimpan dan unggahan baru; form mengirim `order[format][]` berisi `e:ID` (media tersimpan) dan `n:N` (unggahan ke-N, mengikuti urutan file di input). Server (`ScheduledPostService::applyOrder`) menyimpannya ke kolom `position`, mengabaikan token yang tidak valid atau milik jadwal lain, dan menaruh media yang tidak disebut di belakang. Urutan itu juga yang dipakai pratinjau dan penerbitan (carousel Feed dan urutan tayang Story). Format yang sudah terbit tidak bisa diurutkan.

Syarat video diperiksa saat unggah (`FormatMedia` + `VideoSpec` + `Mp4Inspector`): container MP4/MOV, codec video H.264 atau HEVC, audio AAC (atau tanpa audio), frame rate 23-60 fps. Video **tidak dikonversi**; yang tidak memenuhi syarat ditolak dengan pesan yang menyebut penyebabnya. `Mp4Inspector` membaca metadata langsung dari struktur file (tanpa `ffprobe`), termasuk **MP4 fragmented** (video stok, perekam layar) yang menulis durasi 0 di `moov`: durasi dan frame rate dijumlahkan dari kotak `moof`/`trun` (atau `mehd`, bawaan `tfhd`/`trex`), dan sudah dicocokkan dengan AVFoundation pada file nyata, sudah dibandingkan dengan AVFoundation pada video nyata (durasi, ukuran, frame rate cocok) dan memakai MP4 sintetis di test untuk kasus rotasi iPhone dan `moov` di belakang.

Video disimpan apa adanya di `storage/app/public/scheduled-posts/videos/` dan dihapus oleh `scheduler:prune-media` setelah `SCHEDULER_PUBLISH_RETENTION_DAYS` hari sejak format itu terbit (foto: versi terbit dihapus, thumbnail tetap).

**Batas unggahan**: video hingga 300MB butuh `upload_max_filesize` >= 320M, `post_max_size` >= 512M, `max_execution_time` >= 120, dan `client_max_body_size` (Nginx) >= 512M. Bila total melebihi `post_max_size`, form menampilkan pesan batas server, bukan error 413 mentah. Di Herd lokal batasnya masih 10M.

Aturan per platform: **Instagram** menerima Feed, Story, dan Reels, dan Feed dipaksa rasio 4:5 sampai 1,91:1. **Facebook** menerima Feed (foto), Story (foto atau video), dan Reels: rasio Feed bebas, sedangkan jenis file (JPEG), ukuran (8 MB), jumlah (10 foto), dan panjang caption (2200 karakter) masih memakai batas Instagram, lebih ketat dari yang Facebook perlukan. Validasi server menolak video Feed untuk akun Facebook (`FormatMedia`). Reels Facebook dibatasi **90 detik** (`scheduler.video.facebook_reel_max_seconds`, `VideoSpec::maxSeconds('reel', 'facebook')`), lebih pendek dari Instagram; kolom `share_to_feed` tidak dipakai untuk Facebook dan caption menjadi `description`. Video Story Facebook memakai syarat yang sama dengan Story Instagram (MP4/MOV, H.264/HEVC, 3-60 detik, maks 100 MB, termasuk potong video); Meta mensyaratkan minimal 540x960, rasio 9:16, dan 24-60 fps. Resolusi dan rasio **belum diperiksa** saat unggah, jadi video yang terlalu kecil baru ketahuan gagal saat terbit.

### Potong video Story
Video Story lebih dari 60 detik tidak ditolak bila ffmpeg tersedia: panel "Pilih bagian yang ditayangkan" muncul dengan pemutar dan dua pegangan geser (rentang 3-60 detik). Form mengirim `trim_story[N][start|end]` (N = urutan file di `media_story[]`).
- **Validasi** (`FormatMedia`): rentang harus di dalam durasi video, 3-60 detik; ukuran sumber boleh sampai `SCHEDULER_STORY_TRIM_SOURCE_MAX_MB` (500); codec dan frame rate tetap diperiksa. Tanpa ffmpeg, rentang diabaikan dan batas 60 detik berlaku seperti biasa.
- **Pemotongan** (`VideoTrimmer`, dipanggil `ScheduledPostService::storeVideo` saat simpan): ffmpeg memotong dan mengenkode ulang ke H.264 + AAC dengan `faststart` (presisi, dan bitrate dibatasi supaya potongan 60 detik muat di batas 100 MB). Yang disimpan hanya potongannya; file asli tidak disimpan.
- **Berjalan sinkron** saat tombol simpan ditekan. Klip 60 detik biasanya beberapa belas detik, tetapi bisa kena batas waktu nginx atau PHP-FPM di server yang lambat. Galat pemotongan ditampilkan sebagai pesan di bawah kotak media Story.
- Deteksi ffmpeg (`VideoTrimmer::isAvailable`) hanya menyimpan hasil "ada" di cache selama 1 jam, supaya fitur langsung aktif setelah ffmpeg dipasang. Bila PHP-FPM tidak melihat ffmpeg di `PATH`, isi `SCHEDULER_FFMPEG` dengan path penuh.
- Video Story yang sudah tersimpan tidak bisa dipotong ulang; hapus dan unggah lagi. Pratinjau ponsel memutar hanya rentang yang dipilih.

### Slot jadwal dan cron
Jam terbit dipilih lewat komponen `<x-scheduler::schedule-picker>` (tanggal, jam 00-23, dan menit per slot; selalu 24 jam, tanpa preset jam; menit di luar slot tidak bisa dipilih) dan terkirim sebagai satu field `scheduled_at` berformat `Y-m-d\TH:i` (WIB). Jam terbit wajib jatuh di slot yang sejajar dengan cron (`SCHEDULER_SLOT_MINUTES`, bawaan 15: menit 00, 15, 30, 45). Karena cron berjalan tepat di menit yang sama, jadwal 09.00 diproses di putaran 09.00, bukan 09.15. Validasi server (`OnScheduleSlot`) menolak jam di luar slot; form memakai `step` sesuai slot, nilai minimum dan default di slot berikutnya, dan duplikasi mengusulkan waktu di slot.

Dalam satu putaran `schedule:run`: `scheduler:dispatch-due` mengirim post jatuh tempo ke antrean, lalu `queue:work --stop-when-empty --max-time=240` memprosesnya (urutan ini wajib; jika terbalik, post telat satu putaran). Worker di dalam scheduler bisa dimatikan dengan `SCHEDULER_RUN_WORKER=false` saat memakai Supervisor di VPS. Semua kunci `withoutOverlapping` diberi masa kedaluwarsa pendek (10 menit, worker sekitar 9 menit, refresh token 60 menit) agar proses yang dibunuh host tidak menahan scheduler seharian.

Setiap putaran mencatat detak (`SchedulerHeartbeat`). Dashboard menampilkan peringatan untuk developer bila scheduler belum pernah berjalan atau terlewat lebih dari dua slot.

**Rencana saat ini: VPS** (cron tiap menit, slot 1 menit). Di slot 1 menit, pilihan menit di form berisi 00-59 dan teks bantuannya "Pilih menit bebas". Konfigurasi VPS:

`.env`:
```env
SCHEDULER_SLOT_MINUTES=1
SCHEDULER_RUN_WORKER=false   # queue:work dijalankan Supervisor
QUEUE_CONNECTION=database
```

Cron (user web server, misalnya `www-data`):
```
* * * * * cd /var/www/sosmedhub && php artisan schedule:run >> /dev/null 2>&1
```

Supervisor (`/etc/supervisor/conf.d/sosmedhub-queue.conf`):
```ini
[program:sosmedhub-queue]
command=php /var/www/sosmedhub/artisan queue:work --sleep=3 --tries=1 --max-time=3600
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sosmedhub/storage/logs/worker.log
stopwaitsecs=3600
```
Setelah menyimpan: `supervisorctl reread && supervisorctl update`. Setiap deploy jalankan `php artisan queue:restart` agar worker memakai kode baru. `--tries=1` wajib dipertahankan agar post tidak terbit dua kali.

Bila `SCHEDULER_SLOT_MINUTES` tidak diisi, bawaannya 15 (paling aman: jadwal tidak pernah lebih rapat daripada cron). Untuk shared hosting tetap memakai 15, `SCHEDULER_RUN_WORKER=true`, dan cron tiap 15 menit.

### Pindah ke object storage (S3, Cloudflare R2)
Disk media dipilih lewat `SCHEDULER_MEDIA_DISK` dan `Modules/Scheduler/app/Services/MediaDisk.php`. Path di database (`media_path`, `thumbnail_path`) relatif terhadap disk itu, jadi berpindah disk **tidak butuh migrasi database**. Langkah saat siap:
1. `composer require league/flysystem-aws-s3-v3 "^3.0"` (belum terpasang).
2. Isi `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT` (R2: `https://<account>.r2.cloudflarestorage.com`), `AWS_URL` (domain publik bucket/CDN, **harus bisa dijangkau Meta**), `AWS_DEFAULT_REGION=auto`, `AWS_USE_PATH_STYLE_ENDPOINT=true` untuk R2.
3. Salin file lama ke bucket dengan path yang sama (belum ada perintahnya; rencana `scheduler:copy-media-to-disk`).
4. `SCHEDULER_MEDIA_DISK=s3`, lalu `config:clear` dan `queue:restart`.

Efek samping yang menguntungkan: Instagram mengambil media langsung dari `AWS_URL`, jadi `APP_URL` tidak lagi harus publik untuk publikasi. Facebook mengunggah dari stream disk, bukan path lokal. Halaman daftar dan riwayat tidak lagi memanggil `exists()` per thumbnail (di S3 itu satu request per item). **Belum dites di bucket asli**; yang teruji hanya disk lokal dan disk palsu terpisah. Avatar user dan logo situs masih memakai disk `public`.

### Pengolahan foto
Foto diolah saat diunggah oleh `MediaProcessor` (Imagick, wajib ada di server): dibaca dalam skala kecil bila lebih besar dari target (hemat memori untuk foto hingga 50MP), diputar sesuai EXIF, metadata dihapus (termasuk GPS), lalu disimpan dua versi: **versi terbit** (lebar maks 1440px, JPEG 85, biasanya 400-900KB; yang diambil Instagram) dan **thumbnail** (400px, JPEG 75, sekitar 20-40KB; untuk kalender dan riwayat). File asli tidak disimpan. Instagram memperkecil semua foto ke 1080-1440px, jadi kualitas di Instagram tidak berbeda.

Validasi (`InstagramPhoto`): JPEG terbaca, maksimal `SCHEDULER_MAX_MEGAPIXELS` (50), rasio 4:5 sampai 1,91:1 (dihitung setelah orientasi EXIF). Foto yang pasti ditolak Instagram ketahuan saat upload, bukan saat jam terbit.

`scheduler:prune-media` (harian 01.00) menghapus versi terbit dari postingan yang terbit lebih dari `SCHEDULER_PUBLISH_RETENTION_DAYS` (30) hari lalu; thumbnail tetap. Foto tanpa thumbnail tidak dihapus. Duplikat dari postingan yang fotonya sudah dihapus menghasilkan draf tanpa foto, dengan peringatan untuk mengunggah ulang.

Setelah deploy pertama fitur ini, jalankan sekali `php artisan scheduler:process-existing-media` untuk mengolah foto lama.

PHP harus mengizinkan upload sebesar validasi: `upload_max_filesize` minimal 8M dan `post_max_size` minimal 100M (carousel 10 foto x 8MB). Atur di MultiPHP INI Editor (cPanel) dan di Herd untuk lokal.

### Pemulihan format yang macet
Dua lapis pengaman supaya jadwal tidak menggantung di `publishing`:
1. **`PublishPublicationJob::failed()`**: dipanggil Laravel saat job kehabisan percobaan, kena batas waktu, atau melempar pengecualian yang tidak tertangani. Format ditandai gagal dan email dikirim.
2. **`scheduler:recover-stuck`** (tiap 5 menit): format `pending`/`publishing` yang `updated_at`-nya lebih tua dari `SCHEDULER_STUCK_MINUTES` (bawaan 30) pada jadwal berstatus `publishing` ditandai gagal. Ini menangkap kasus yang tidak sempat memanggil `failed()` (proses dibunuh, job hilang).

Keduanya lewat `PostFinalizer`, yang juga dipakai jalur normal, jadi status dan email konsisten. Pesan galatnya meminta pengguna **memeriksa akun tujuan lebih dulu**: format yang macet setelah `finish` Facebook atau `media_publish` Instagram mungkin sebenarnya sudah terbit, dan menjadwalkan ulang bisa menggandakan postingan.

`retry_after` antrean database dinaikkan ke 180 detik (`DB_QUEUE_RETRY_AFTER`), harus lebih besar dari timeout job 150 detik; sebelumnya 90 detik.

### Status postingan
`draft` (hasil duplikasi), `scheduled`, `publishing`, `published`, `partial` (sebagian format terbit), `failed`, `cancelled`.
Yang boleh diubah: `scheduled`, `failed`, `partial`, `draft`. Menyimpan postingan `failed`, `partial`, atau `draft` mengembalikannya ke `scheduled`; hanya format yang gagal yang diulang.

Status tiap format ada di `scheduled_post_publications`: `pending`, `publishing`, `published`, `failed`. Lencana format (Feed, Story, Reels) tampil di kalender, modal, riwayat (dengan titik status per format), dan form ubah.

### Halaman Akun Sosial
Halaman `/admin/social-accounts` berisi satu kartu per platform: Instagram dan Facebook (aktif), Threads (tombol Hubungkan nonaktif, "Segera hadir"; tampilan saja, tanpa backend). Komponen: `<x-social-account::platform-card>` dan `<x-social-account::brand-icon>` (logo SVG di dalam ubin warna merek) di `Modules/SocialAccount/resources/views/components/`.
- Tanpa akun: tombol **Hubungkan** (langsung ke OAuth Instagram).
- Ada akun: tiap akun tampil dengan avatar (cadangan inisial bila gambar CDN kedaluwarsa), nama, `@username`, dan tombol **Putuskan** (tanpa tombol Edit), lalu **+ Tambah Akun** di bawah kartu. Akun berstatus `expired` menampilkan peringatan dan **Hubungkan Ulang**.
- Akun berstatus `disconnected` tidak ditampilkan; menghubungkannya lagi mengaktifkan barisnya kembali.
- Tombol mengikuti izin (`social-account.create`, `.delete`). Developer melihat akun semua user dengan label pemilik.
- Form manual (`/admin/social-accounts/create`) tidak lagi ditautkan dari halaman ini tetapi masih bisa dibuka lewat URL.

### Form jadwal dan pratinjau
**Akun tujuan** dipilih lewat kartu (bukan dropdown): foto profil (cadangan inisial), nama (`@username` untuk Instagram, nama Page untuk Facebook), logo platform, urutan Instagram dulu lalu Facebook. Satu jadwal tetap satu akun. Memilih akun Facebook tidak membatasi format; yang berbeda hanya batas durasi Reels (90 detik) dan pilihan "Tampilkan Reels juga di Feed" yang disembunyikan. Kolom Caption hilang bila hanya Story yang dipilih.

**Pratinjau** ada di komponen `<x-scheduler::post-preview>` (`Modules/Scheduler/resources/views/components/post-preview.blade.php`), dipakai halaman buat dan ubah. Komponen membaca state Alpine dari elemen induknya (`x-data` "postComposer" di `composer-script.blade.php`), jadi harus berada di dalamnya. Satu prop Blade: `username`; nama, foto, dan platform akun dibaca reaktif dari `accounts` yang dikirim form, mengikuti kartu akun yang dipilih.
- **Instagram**: bingkai ponsel dengan Feed, Story, dan Reels; foto profil lewat `<x-scheduler::account-avatar>`. Video Story dan Reels bisa diputar (klik putar/jeda, tombol suara, batang progres); semua video pratinjau (`data-preview-video`) dijeda dan dikembalikan ke awal (atau awal rentang potong) saat format, item, atau media berganti (`stopPlayback()`). Video yang dipotong diputar hanya pada rentangnya.
- **Facebook**: layar Feed ala Facebook (header, nama Page, caption, foto tunggal atau kisi hingga 4 foto dengan "+N", tombol Suka/Komentar/Bagikan).
- **Facebook Reels**: layar penuh ala Reels Facebook (judul Reels, kolom Suka/Komentar/Bagikan, nama Page dengan tombol Ikuti, deskripsi dari caption), tanpa bilah navigasi Instagram. **Facebook Story**: layar Story ala Facebook (bilah segmen, foto Page dengan cincin biru, "Sekarang", tombol titik tiga dan tutup, kolom Balas dengan reaksi), untuk foto maupun video. Petunjuk durasi dan opsi "Tampilkan Reels juga di Feed" mengikuti platform akun yang dipilih (Facebook: 90 detik, tanpa opsi Feed).
- **Ponsel**: bingkai lebih sempit (maks 300 px) dan tombol **Pratinjau layar penuh** (hanya di layar kecil) yang menampilkan pratinjau memenuhi layar HP tanpa bingkai tiruan, hanya dengan tombol tutup; yang tampil adalah format yang sedang aktif. Tombol **Jadwalkan Terbit** dan **Batal** ada di paling bawah halaman, selebar layar dan bertumpuk.

### Halaman Riwayat
`/admin/post-history` (route `admin.post-history.index`, izin `scheduler.view`) berisi postingan yang sudah lewat antrean: status selain `scheduled`, atau `scheduled` yang waktunya sudah lewat. Ada tab status (Semua, Terbit, Gagal, Dibatalkan, Draf) dengan jumlahnya, filter akun, dan pencarian caption. Halaman Penjadwalan hanya menampilkan kalender dan ringkasan jumlah postingan gagal dengan tautan ke Riwayat. Nama route sengaja tidak memakai awalan `admin.scheduled-posts.` supaya menu Penjadwalan tidak ikut aktif. Menu Riwayat ditambahkan di `MenuDatabaseSeeder`; di server yang sudah punya data menu, tambahkan lewat halaman Menu (route `admin.post-history.index`, pola aktif `admin.post-history.*`, izin `scheduler.view`) karena seeder menu menghapus seluruh tabel menu.

### Notifikasi email (lewat antrean)
- **Postingan gagal**: selalu dikirim, berisi penyebab dan tombol Jadwalkan ulang.
- **Postingan berhasil**: dikirim kecuali `SCHEDULER_NOTIFY_PUBLISHED=false`.
- **Akun terputus**: dikirim saat Instagram menolak token (error 190), dan saat refresh token gagal setelah token habis. Akun ditandai `expired`.
- **Token hampir habis**: dikirim bila refresh gagal dan sisa waktu 3 hari atau kurang.

## 4. Data

Tabel milik produk: `users`, `social_accounts`, `scheduled_posts`, `scheduled_post_publications`, `scheduled_post_media`.

```
users 1──N social_accounts
users 1──N scheduled_posts
social_accounts 1──N scheduled_posts   (social_account_id boleh kosong)
scheduled_posts 1──N scheduled_post_publications   (satu baris per format: feed, story, reel)
scheduled_posts 1──N scheduled_post_media          (kolom format dan type: image atau video)
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
| `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` | App ID dan App Secret dari **Settings > Basic** di Meta (bukan ID aplikasi Instagram). |
| `FACEBOOK_REDIRECT_URI` | Harus persis sama dengan *Valid OAuth Redirect URIs* di Facebook Login. Formatnya `https://DOMAIN/admin/social-accounts/facebook/callback`. |
| `FACEBOOK_GRAPH_VERSION` | Default `v26.0`. |
| `INSTAGRAM_SCOPES` | Izin OAuth Instagram, dipisah koma. Kosong = `instagram_business_basic,instagram_business_content_publish`. |
| `FACEBOOK_SCOPES` | Izin OAuth Facebook, dipisah koma. Kosong = `pages_show_list,pages_read_engagement,pages_manage_posts,business_management`. Aplikasi menolak koneksi bila ada izin di daftar ini yang tidak disetujui user. |
| `INSTAGRAM_GRAPH_VERSION` | Versi Graph API, misalnya `v26.0`. |
| `QUEUE_CONNECTION` | `database`. |
| `MAIL_*` | Resend lewat SMTP. API key di `MAIL_PASSWORD`; domain pengirim harus terverifikasi di Resend. |
| `SCHEDULER_STUCK_MINUTES` | Batas (menit, bawaan 30) sebelum format yang macet di `publishing` ditandai gagal oleh `scheduler:recover-stuck`. |
| `SCHEDULER_FEED_VIDEO_MAX_SECONDS` | Batas durasi video di carousel Feed Instagram (bawaan 60, konservatif). |
| `DB_QUEUE_RETRY_AFTER` | `retry_after` antrean database (bawaan 180 detik). Harus lebih besar dari timeout job 150 detik. |
| `LOG_STACK` / `LOG_DAILY_DAYS` | `daily` dan 14 hari di produksi supaya log berotasi. |
| `SCHEDULER_MEDIA_DISK` | Nama disk (di `config/filesystems.php`) untuk foto, thumbnail, dan video jadwal. Bawaan `public` (lokal). Semua kode Scheduler membacanya lewat `MediaDisk`, jadi pindah ke object storage cukup mengganti nilai ini. Lihat "Pindah ke object storage" di bawah. |
| `SCHEDULER_NOTIFY_PUBLISHED` | `true` atau `false`. |
| `SCHEDULER_REEL_MAX_SECONDS` | Batas durasi Reels dalam detik (bawaan 180). Meta mengizinkan sampai 900. Story tetap 60 detik. |
| `SCHEDULER_VIDEO_POLL_SECONDS` | Jeda cek status video yang masih diproses Instagram (bawaan 30). |
| `SCHEDULER_FFMPEG` | Path atau nama biner ffmpeg untuk memotong video Story (bawaan `ffmpeg`). Di Mac dengan Herd, mungkin perlu `/opt/homebrew/bin/ffmpeg`. Tanpa ffmpeg, fitur potong mati. |
| `SCHEDULER_STORY_TRIM_SOURCE_MAX_MB` / `SCHEDULER_TRIM_TIMEOUT_SECONDS` | Batas ukuran video sumber yang akan dipotong (bawaan 500) dan batas waktu proses ffmpeg (bawaan 600 detik). |
| `DEVELOPER_NAME`, `DEVELOPER_EMAIL`, `DEVELOPER_PASSWORD` | Akun developer awal untuk `db:seed`. Dibaca lewat `config/sosmedhub.php`. Password hanya wajib saat akun belum ada; akun yang sudah ada tidak diubah. |

## 7. Perintah penting

```bash
php artisan schedule:work                       # lokal: menjalankan scheduler (termasuk worker antrean)
php artisan scheduler:dispatch-due              # kirim jadwal jatuh tempo ke antrean
php artisan social-accounts:refresh-tokens      # perpanjang token yang hampir habis
php artisan queue:retry all                     # kirim ulang job yang gagal
php artisan test                                # 321 test otomatis
```

Setelah mengubah `.env`, jalankan `php artisan config:clear` dan **restart `queue:work`** (worker menyimpan config di memori).

## 8. Pengujian

**Otomatis**: 321 test (320 lulus, 1 dilewati), mencakup penjadwalan, publikasi Instagram dan Facebook (dengan `Http::fake`), pemotongan video (dengan `VideoTrimmer` palsu dan `Process::fake`), pratinjau, notifikasi, refresh token, duplikasi, dan jadwal ulang. Sekali ada satu test yang gagal pada satu kali jalan lalu lulus pada dua kali jalan berikutnya; penyebabnya belum diketahui.

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
4. **Pemotongan video Story butuh ffmpeg dan berjalan sinkron saat simpan.** Belum dicoba dengan ffmpeg asli (hanya dengan ffmpeg palsu di test). Di shared hosting cadangan ffmpeg kemungkinan tidak ada, jadi fitur potong mati dan video Story tetap maks 60 detik. Rincian di bagian 3.
5. Mode Development Meta: hanya akun dengan role **Instagram Tester** yang bisa dihubungkan.
7. ~~Story dan Reels belum pernah diterbitkan ke Instagram asli.~~ Pengembang melaporkan Feed, Story, dan Reels sudah berhasil terbit (11 Oktober 2026). Rinciannya tidak dicatat, jadi tiga pertanyaan ini hanya ditandai terjawab bila video dan akun yang dipakai memang memenuhi kondisinya: (a) apakah akun Creator boleh menerbitkan Story (satu sumber pihak ketiga bilang hanya Business), (b) apakah video dengan `moov` di belakang file, atau MP4 fragmented, diterima (dokumentasi resmi tidak menyebutnya; satu sumber bilang `moov` di belakang ditolak; belum ada remux otomatis. Bila ditolak, solusinya `ffmpeg -i in.mp4 -c copy -movflags +faststart out.mp4`), (c) apakah `share_to_feed` diterima pada Reels.
8. Foto Story dan video tidak punya pratinjau gambar di daftar bila hanya berisi video (dipakai ubin ikon). Frame video pada pratinjau form belum terverifikasi di browser dengan GPU (Chrome headless tidak menampilkannya).
9. Publikasi Feed Facebook sudah berhasil ke Page asli untuk foto. Yang belum dites di Page asli: album (beberapa foto), jalur token ditolak, dan apakah `publish_video` perlu untuk video nanti. ID postingan Facebook disimpan di kolom `ig_media_id` (nama warisan Instagram).
10. Batas Facebook lebih longgar daripada yang dipaksa form (PNG, ukuran foto, jumlah foto, panjang caption); nilai pastinya belum dicek ke dokumentasi Meta.
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
- Hosting: Rumahweb shared Medium (interval cron minimal 15 menit, symlink harus diminta aktif ke support, SSH dan Git tersedia). Konfirmasi PHP 8.3 dan Composer sebelum membeli.
- Satu entri cron tiap 15 menit (worker antrean sudah dijalankan dari dalam scheduler):
  ```
  */15 * * * * cd /home/USER/sosmedhub && php artisan schedule:run >> /dev/null 2>&1
  ```
  Pastikan cron berjalan di menit 00, 15, 30, 45. Jika host menggeser menitnya, slot di form tidak lagi sejajar.
- Jalankan `npm run build` dan `composer install --no-dev` di laptop jika server tidak punya Node atau Composer, lalu upload.
- Pastikan `/storage/...` terbuka publik dan tidak diblokir (hotlink protection, ModSecurity). Jika `storage:link` diblokir, arahkan disk `public` ke folder di dalam `public/`.
- `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` dan `INSTAGRAM_REDIRECT_URI` memakai domain produksi, `APP_KEY` baru.
- Daftarkan URL redirect produksi di dashboard Meta (langkah 4, Siapkan login bisnis Instagram).
- Verifikasi domain pengirim di Resend.
- Jalankan `migrate --force` dan `db:seed`, login, lalu hubungkan ulang akun Instagram.
- Jalankan manual tes B sampai F di server.

### Facebook: langkah berikutnya
1. ~~Tes publikasi Feed foto ke Page sungguhan~~ berhasil (foto tunggal; ini juga memenuhi panggilan API `pages_manage_posts` untuk App Review). **Story foto** (`/photo_stories`) juga berhasil dites di Page asli dengan izin `pages_manage_posts` yang sama. Tinggal tes album (beberapa foto) di Page **Tio**.
2. ~~Tes Story video di Page Tio~~ berhasil (video 12 MB). Belum dites: apakah unggahan hingga 100 MB selesai dalam batas waktu job (150 detik; unggah di runner memakai timeout 120 detik), dan apakah `post_id` dari `finish` berarti Story sudah tayang atau masih diproses (kode menganggapnya terbit). Sebelum membangun video Feed dan Reels untuk Page: baca dokumentasi Meta untuk endpoint dan permission-nya. `publish_video` disebut di dokumentasi Pages API tetapi belum ada di kasus penggunaan app; apakah perlu ditambahkan belum dicek.
3. ~~Penanganan token kedaluwarsa atau dicabut untuk akun Facebook~~ selesai (kode 190 menandai akun `expired` dan mengirim email hubungkan ulang). Belum dites dengan token asli.
4. Gunakan Page **Tio** untuk tes; jangan memakai dua Page "Tio Dwi Satrio" yang batal diterbitkan.
5. Longgarkan aturan foto dan caption untuk Facebook setelah membaca batas resminya (bagian 9 nomor 10).

### Video Story: langkah berikutnya
- Pasang ffmpeg di VPS (`apt install ffmpeg`) dan coba potong video 2-3 menit dengan ffmpeg asli di lokal (`brew install ffmpeg`), termasuk waktu proses saat simpan.
- Pertimbangkan memindahkan pemotongan ke antrean bila waktu simpan terlalu lama.

### Setelah itu
- Tambahkan client awal sebagai **Instagram Tester** di dashboard Meta (client menerima undangan di Settings, Apps and websites, Tester invites).
- Siapkan syarat **App Review** supaya client bisa mendaftar dan menghubungkan akun sendiri: halaman Privacy Policy, Terms, dan Data Deletion sudah ada; tinggal screencast alur lengkap, dan alasan tiap permission (`instagram_business_basic`, `instagram_business_content_publish`). Mode Live saja tidak cukup; yang membuka akses umum adalah Advanced Access lewat App Review.
- Putuskan model bisnis (batas akun atau antrean per paket), lalu pasang batas di dua tempat: saat menghubungkan akun dan saat validasi jadwal.
- Pertimbangkan fitur lanjutan: draf mandiri, template caption, pustaka media, Reels, insight, tim dan persetujuan konten, billing (Midtrans atau Xendit).
- Pindah ke VPS dengan Supervisor untuk antrean jika client sudah sekitar 10 dan aktif, atau jika publikasi sering telat.

## 11. Catatan penting tentang Meta

- Permission yang dipakai: `instagram_business_basic` dan `instagram_business_content_publish`.
- Akun Instagram harus bertipe **Business** atau **Creator**. Story dan Reels memakai izin yang sama (`instagram_business_content_publish`); dokumentasi resmi tidak menyebut izin terpisah. Screencast App Review harus memperlihatkan alur Story dan Reels bila fitur itu aktif saat review.
- Foto wajib berupa URL publik yang dapat diambil server Meta. Domain lokal (`.test`) tidak bisa.
- Batas sekitar 100 postingan per 24 jam per akun dari Instagram.
- Token long-lived berlaku 60 hari dan hanya bisa di-refresh jika umurnya lebih dari 24 jam dan belum kedaluwarsa.
- Webhook tidak dipakai di MVP, jadi langkah webhook di dashboard Meta boleh dikosongkan.

### Facebook (Page)

Status per 2026-10-10: **connect Facebook jalan; publikasi Feed (foto) sudah dibangun, masuk form penjadwalan, dan berhasil dites ke Page asli oleh pengembang (foto tunggal; album belum).** Story foto sudah dibangun dan berhasil dites ke Page asli (11 Oktober 2026). Story video juga sudah dites di Page asli. Reels sudah dibangun (teruji HTTP palsu, belum di Page asli). Video Feed untuk Facebook belum ada dan ditolak validasi.

- Publikasi: `FacebookPublicationRunner` (dipilih `PublishPublicationJob` menurut platform akun) dan `FacebookPublisher`. Satu foto: `POST /{page-id}/photos` dengan file diunggah langsung dari server (tanpa URL publik). Beberapa foto: tiap foto diunggah `published=false`, lalu satu `POST /{page-id}/feed` dengan `attached_media`. Hasil langsung jadi, tanpa container yang ditunggu. ID postingan disimpan di kolom `ig_media_id` (nama kolom warisan Instagram).
- Token ditolak (kode 190) menandai akun `expired` dan mengirim email hubungkan ulang, sama seperti Instagram.
- Batasan: rasio foto Feed tidak dipaksa untuk Facebook, tetapi JPEG, maks 8 MB, maks 10 foto, dan caption 2200 karakter masih mengikuti batas Instagram (lebih ketat dari Facebook).

- Yang didukung adalah **Page**, bukan profil pribadi. Profil pribadi tidak bisa diposting lewat API. Di Akun Sosial, ID yang tampil (`ID ...`) adalah ID Page, bukan ID profil.
- Alur: Facebook Login (`graph.facebook.com`, v26.0) di `FacebookConnectController` dan `FacebookGraphService`. Scope: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `business_management`. Aplikasi menolak koneksi jika ada izin yang tidak disetujui. Jika user punya lebih dari satu Page, muncul halaman pilih Page; token pengguna hanya disimpan terenkripsi di sesi sampai Page dipilih. Token Page dari token pengguna long-lived tidak kedaluwarsa (`token_expires_at` null), jadi tidak ada refresh.
- **`business_management` wajib untuk Page milik Business Portfolio.** Tanpa izin itu `me/accounts` mengembalikan daftar kosong ("Tidak ada Halaman Facebook yang bisa kamu kelola"). Page klasik (milik akun pribadi) muncul tanpa izin itu. Ini kesimpulan dari uji di akun pengembang, bukan dari dokumentasi Meta. Untuk akun di luar role app, izin ini kemungkinan butuh Advanced Access lewat App Review; syarat resminya belum dibaca.
- Page yang berstatus **batal diterbitkan** (unpublished) tidak bisa dibuka publik dan kemungkinan tidak bisa diposting. Dua Page "Tio Dwi Satrio" milik pengembang berstatus itu dan sudah ditinggalkan. Page uji yang dipakai: **Tio** (`1010989448772312`).
- Pengaturan di dashboard Meta (sudah dilakukan): kasus penggunaan **Kelola segala sesuatu di Halaman Anda**; **Facebook Login > Settings > Valid OAuth Redirect URIs** berisi `https://DOMAIN/admin/social-accounts/facebook/callback` (dicek dengan Validator URI Pengalihan); **Settings > Basic > Domain Aplikasi** berisi domain app. `FACEBOOK_CLIENT_ID` dan `FACEBOOK_CLIENT_SECRET` adalah App ID dan App Secret dari Settings > Basic (bukan ID Instagram).
- `FACEBOOK_REDIRECT_URI` harus persis sama dengan yang terdaftar di Meta. Jangan memakai `${APP_URL}` bila `APP_URL` diakhiri `/` (hasilnya `//admin`).
- Menu kasus penggunaan bernama **Facebook Login for Business**. Aplikasi memakai parameter `scope`, dan itu berhasil di akun pengembang. Jika suatu hari dialog menolak scope atau meminta `config_id`, buat konfigurasi di menu Konfigurasi dan pakai ID-nya.
- Di dashboard, tiap permission butuh minimal 1 panggilan API berhasil sebelum bisa diajukan ke App Review (kolom "0 dari 1 panggilan API diperlukan"). Yang sudah tercatat dari alur nyata: `pages_show_list`, `pages_read_engagement`, `public_profile`, `business_management` setelah connect. Yang belum: `pages_manage_posts` dan `pages_manage_engagement`; `pages_manage_posts` terpenuhi setelah tes publikasi foto berhasil (periksa tampilannya di dashboard Meta); `pages_manage_engagement` baru terpenuhi setelah balasan komentar dites.
