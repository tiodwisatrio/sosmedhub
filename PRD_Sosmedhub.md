# PRD — Antrian Konten

**Product Requirements Document**

| | |
|---|---|
| **Versi** | 0.2 — Draf |
| **Tanggal** | 27 Agustus 2026 |
| **Cakupan** | MVP + Peta jalan |
| **Status** | Menunggu keputusan |

Penjadwal postingan Instagram berbahasa Indonesia. Dokumen ini memuat cakupan MVP sekaligus peta jalan enam fase sesudahnya — lengkap dengan syarat masuk tiap fase, supaya urutan pengerjaan tidak ditentukan oleh suasana hati.

---

## Daftar Isi

1. [Ringkasan](#01--ringkasan)
2. [Masalah](#02--masalah)
3. [Target Pengguna](#03--target-pengguna)
4. [Prinsip Kerja](#04--prinsip-kerja)
5. [Peta Jalan](#05--peta-jalan)
6. [Fase 0 — MVP](#06--fase-0--mvp)
7. [Fase 1 — Layak Dipakai Harian](#07--fase-1--layak-dipakai-harian)
8. [Fase 2 — Video & Reels](#08--fase-2--video--reels)
9. [Fase 3 — Kerja Tim & Persetujuan Klien](#09--fase-3--kerja-tim--persetujuan-klien)
10. [Fase 4 — Monetisasi](#10--fase-4--monetisasi)
11. [Fase 5 — Platform Lain](#11--fase-5--platform-lain)
12. [Fase 6 — Analitik & Otomasi](#12--fase-6--analitik--otomasi)
13. [Tidak Akan Dibangun](#13--tidak-akan-dibangun)
14. [Penjaga Keputusan](#14--penjaga-keputusan)
15. [Teknis](#15--teknis)
16. [Utang Teknis Terencana](#16--utang-teknis-terencana)
17. [Risiko](#17--risiko)
18. [Rencana Rilis MVP](#18--rencana-rilis-mvp)
19. [Keputusan Terbuka](#19--keputusan-terbuka)

---

## 01 — Ringkasan

Antrian Konten adalah aplikasi web untuk menjadwalkan postingan Instagram. Pengguna menghubungkan akun Instagram Business miliknya atau milik klien, menyusun konten, memilih tanggal serta jam terbit, lalu sistem menerbitkannya otomatis tanpa perlu dibuka lagi.

MVP dibangun untuk menguji **satu hipotesis**: pengelola media sosial di Indonesia bersedia membayar dalam Rupiah untuk penjadwal posting yang sederhana dan berbahasa Indonesia.

Bagian 05 sampai 14 adalah peta jalan sesudah MVP. Fungsinya bukan daftar keinginan, melainkan **urutan yang sudah diputuskan sekarang** supaya keputusan setelah MVP berhasil tidak diambil terburu-buru. Setiap fase punya syarat masuk yang harus terpenuhi lebih dulu.

---

## 02 — Masalah

| Kondisi saat ini | Kekurangannya |
|---|---|
| **Buffer, Later, Hootsuite** | Harga dalam USD dengan langganan bulanan, antarmuka bahasa Inggris, dan banyak fitur yang tidak dipakai pengelola akun skala kecil. |
| **Meta Business Suite** | Gratis dan resmi, tapi antarmukanya berat, sering berubah, dan alurnya tidak dirancang untuk satu orang yang memegang beberapa akun klien sekaligus. |
| **Posting manual** | Harus membuka HP pada jam tertentu setiap hari. Konten yang sudah disiapkan sering terlambat terbit atau terlewat. |
| **Pemain lokal** | Yang ada bermain di *social listening* dan pemantauan sentimen, bukan penjadwalan konten. Kategori ini praktis kosong. |

---

## 03 — Target Pengguna

**Persona utama:** admin media sosial UMKM atau agency kecil yang memegang 1–5 akun Instagram. Bekerja sendiri atau berdua, menyiapkan konten mingguan, dan tidak punya waktu mempelajari tool yang rumit.

**Bukan target sampai Fase 3:** tim besar dengan alur persetujuan berlapis dan brand enterprise. Menolak kelompok ini di awal membuat cakupan MVP tetap kecil — mereka baru dilayani setelah dasarnya kokoh.

---

## 04 — Prinsip Kerja

1. **Satu alur, dituntaskan.** Hubungkan → susun → jadwalkan → terbit. Semua energi ke empat langkah ini sampai benar-benar mulus.
2. **Bawaan dulu, paket belakangan.** Queue, scheduler, dan auth bawaan Laravel dipakai apa adanya. Paket pihak ketiga hanya kalau bawaan terbukti tidak cukup.
3. **Fitur menunggu pemicunya.** Setiap fase punya syarat masuk yang jelas. Sebelum syaratnya terpenuhi, fase itu tidak dikerjakan — sekalipun idenya menarik.
4. **Gagal harus berisik.** Postingan yang gagal terbit tidak boleh diam. Status terlihat di antarmuka dan pemiliknya diberi tahu lewat email.
5. **Kode dibaca lebih sering daripada ditulis.** Nama yang jelas dan tanpa singkatan lebih penting daripada kode yang ringkas. Aturannya di bagian 15.4.

---

## 05 — Peta Jalan

Tujuh fase, masing-masing punya syarat masuk. Fase berikutnya tidak dimulai sebelum fase sekarang lulus ukurannya — ini yang menjaga agar produk tidak melebar sebelum dasarnya kuat.

| Fase | Fokus | Ringkas |
|---|---|---|
| **0** ← sekarang | MVP — Terbit Otomatis | Satu gambar, satu akun, terbit tepat waktu. Membuktikan pipa publikasi bisa diandalkan. |
| **1** | Layak Dipakai Harian | Carousel, kalender, draf, perpustakaan media. Membuat pengguna betah, bukan sekadar mencoba. |
| **2** | Video & Reels | Format yang mendominasi Instagram hari ini, tapi paling banyak mode gagalnya. |
| **3** | Kerja Tim & Persetujuan Klien | Ruang kerja per klien, peran, dan alur persetujuan. Membuka pintu ke agency. |
| **4** | Monetisasi | Paket, kuota, dan pembayaran. Baru masuk akal setelah ada yang menyatakan mau bayar. |
| **5** | Platform Lain | Facebook, TikTok, Threads. Titik di mana abstraksi platform baru sah dibuat. |
| **6** | Analitik & Otomasi | Laporan, rekomendasi jam, antrian berulang. Nilai tambah, bukan fondasi. |

> **Cara membaca.** Urutan ini bukan jadwal dengan tanggal. Sebuah fase bisa berhenti bertahun-tahun di tempatnya kalau syarat masuknya tidak pernah terpenuhi — dan itu hasil yang benar, bukan kegagalan.

---

## 06 — Fase 0 — MVP

### Terbit Otomatis · perkiraan 6–8 minggu

**Mulai ketika:** empat keputusan di bagian 19 sudah diambil.

**Isi fase:**
- Daftar & masuk dengan email
- Hubungkan akun Instagram Business
- Lihat & putuskan akun terhubung
- Susun post: satu foto + caption
- Pilih tanggal & jam terbit
- Daftar antrian & riwayat
- Ubah / batalkan sebelum terbit
- Terbit otomatis pada waktunya
- Email saat gagal terbit
- Pembaruan token otomatis

**Lulus ketika:** 20 akun terhubung · 200 post terbit · keberhasilan ≥98% · 5 pengguna kembali tiap minggu

**Sengaja belum ada:** carousel, video, kalender, draf, tim, pembayaran

### 6.1 Rincian kebutuhan

| ID | Kebutuhan | Catatan |
|---|---|---|
| F-01 | Daftar dan masuk dengan email & kata sandi | Pakai starter kit Laravel apa adanya |
| F-02 | Hubungkan akun Instagram Business lewat Facebook | OAuth, simpan token jangka panjang |
| F-03 | Lihat daftar akun terhubung dan putuskan koneksinya | Satu pengguna boleh punya beberapa akun |
| F-04 | Susun post: unggah satu foto, tulis caption, pilih akun tujuan | JPEG/PNG, maksimal 8 MB |
| F-05 | Pilih tanggal dan jam terbit | Waktu Indonesia Barat |
| F-06 | Lihat antrian: postingan mendatang dan riwayat, lengkap dengan status | Daftar berurut tanggal — kalender baru di Fase 1 |
| F-07 | Ubah atau batalkan postingan yang belum terbit | Klien sering berubah pikiran |
| F-08 | Terbit otomatis pada waktu yang dijadwalkan | Inti produk |
| F-09 | Kirim email ke pemilik saat postingan gagal terbit | Menutup prinsip "gagal harus berisik" |
| F-10 | Perbarui token akses otomatis sebelum kedaluwarsa | Tanpa ini, koneksi putus tiap 60 hari |

### 6.2 Cerita pengguna

- Sebagai admin medsos, saya ingin menghubungkan akun Instagram klien **sekali saja**, *agar tidak perlu login ulang setiap kali mau posting.*
- Sebagai admin medsos, saya ingin menyiapkan konten **seminggu sekaligus**, *agar tidak perlu membuka HP setiap hari pada jam tertentu.*
- Sebagai admin medsos, saya ingin **segera tahu kalau ada postingan gagal terbit**, *agar sempat memposting manual sebelum terlambat.*
- Sebagai admin medsos, saya ingin **membatalkan postingan terjadwal**, *karena klien sering berubah pikiran di menit terakhir.*
- Sebagai admin medsos, saya ingin melihat **semua akun klien dalam satu antrian**, *agar tidak perlu berpindah-pindah aplikasi.*

### 6.3 Alur utama

1. **Hubungkan akun** — Pengguna menekan "Hubungkan Instagram" → login Facebook → memilih Page → sistem mengambil akun Instagram Business yang tertaut, menukar token pendek menjadi token jangka panjang, dan menyimpannya.
2. **Susun post** — Pengguna memilih akun tujuan, mengunggah satu foto, menulis caption, lalu memilih tanggal dan jam. Post tersimpan dan tampil berlabel "Terjadwal".
3. **Menunggu di antrian** — Post tampil di daftar antrian. Selama belum terbit, masih bisa diubah atau dibatalkan.
4. **Terbit otomatis** — Pada waktunya, sistem mengirim konten ke Instagram dan labelnya berubah jadi "Terbit".
5. **Kalau gagal** — Sistem mencoba ulang beberapa kali. Kalau tetap gagal, post berlabel "Gagal" beserta alasannya, dan pemiliknya menerima email agar bisa memposting manual.

---

## 07 — Fase 1 — Layak Dipakai Harian

MVP membuktikan orang *mau* memakainya. Fase ini membuat mereka *betah* memakainya. Semua isinya adalah hal yang bikin pengguna berhenti kalau tidak ada.

### Layak Dipakai Harian · perkiraan 4–6 minggu

**Mulai ketika:** keempat ukuran Fase 0 tercapai, dan tidak ada lagi kegagalan publikasi yang belum dipahami sebabnya.

**Isi fase:**
- Carousel 2–10 gambar
- Kalender bulanan
- Simpan sebagai draf
- Duplikat postingan
- Perpustakaan media
- Pratinjau tampilan feed
- Ubah jadwal dari kalender
- Hitung karakter caption

**Utang dibayar:** media pindah dari satu kolom ke tabel `post_media` — carousel yang memaksanya

**Lulus ketika:** rata-rata pengguna aktif menjadwalkan >4 postingan per minggu

> **Catatan.** Carousel diletakkan di sini, bukan di MVP, karena menambah bentuk data baru sekaligus menguji keandalan publikasi berarti menguji dua hal bersamaan — kalau gagal, sebabnya jadi kabur. Kalau ternyata klien Anda mayoritas memposting carousel, fitur ini naik ke Fase 0 dan video tetap di Fase 2.

---

## 08 — Fase 2 — Video & Reels

Format yang paling diminta, sekaligus paling mahal dibangun. Wadah media video tidak langsung siap seperti gambar — harus ditunggu statusnya sebelum bisa diterbitkan, dan itu membatalkan penyederhanaan yang dipakai sejak MVP.

### Video & Reels · perkiraan 4–6 minggu

**Mulai ketika:** minimal 3 pengguna memintanya, atau ada pengguna yang berhenti memakai dengan alasan tidak bisa menjadwalkan video.

**Isi fase:**
- Unggah video untuk feed
- Reels
- Pilih gambar sampul
- Validasi durasi & rasio
- Validasi format & ukuran
- Pratinjau sebelum dijadwalkan
- Video di dalam carousel
- Indikator proses unggah

**Utang dibayar:** menunggu status wadah media sampai siap · media pindah ke penyimpanan objek karena ukuran berkas

**Lulus ketika:** tingkat keberhasilan video setara dengan gambar

> **Jangan.** Jangan membangun pengubah format video sendiri. Tolak berkas yang tidak memenuhi syarat dengan pesan yang jelas dan panduan ekspor singkat — jauh lebih murah daripada memelihara pemrosesan video di server sendiri.

---

## 09 — Fase 3 — Kerja Tim & Persetujuan Klien

Fase yang mengubah produk dari alat pribadi menjadi alat agency. Perubahan terbesar sepanjang peta jalan ada di sini — kepemilikan data bergeser dari perorangan ke ruang kerja.

### Kerja Tim & Persetujuan Klien · perkiraan 6–8 minggu

**Mulai ketika:** ada agency dengan lebih dari 3 klien yang mendaftar, atau calon pelanggan berbayar yang mensyaratkan alur persetujuan.

**Isi fase:**
- Ruang kerja per klien
- Undang anggota tim
- Peran: pemilik, editor, peninjau
- Alur draf → diajukan → disetujui
- Catatan internal per post
- Tautan pratinjau tanpa login
- Riwayat perubahan
- Pemberitahuan menunggu persetujuan

**Utang dibayar:** kepemilikan berubah dari `user → account` menjadi `workspace → account`

**Lulus ketika:** ada agency yang memakai alur persetujuan untuk seluruh kliennya

> **Urutan penting.** Fase ini harus mendahului Fase 4. Tagihan menempel pada ruang kerja, bukan perorangan — kalau monetisasi dibangun lebih dulu di atas model kepemilikan lama, perpindahannya harus dikerjakan dua kali sambil menangani uang yang sudah masuk.

---

## 10 — Fase 4 — Monetisasi

Bukan fase pertama yang menghasilkan uang — hanya fase yang membuat penagihan berjalan otomatis. Sepuluh pelanggan pertama ditagih manual lewat transfer, dan itu sudah cukup untuk membuktikan orang benar-benar mau bayar.

### Monetisasi · perkiraan 4–5 minggu

**Mulai ketika:** minimal 10 pelanggan sudah membayar secara manual, dan menagih satu per satu mulai memakan waktu yang terasa.

**Isi fase:**
- Paket langganan & harga
- Kuota akun & post per paket
- Pembatasan sesuai paket
- Pembayaran otomatis
- Faktur & riwayat tagihan
- Masa uji coba
- Peringatan sebelum jatuh tempo
- Verifikasi email saat daftar

**Utang dibayar:** verifikasi email — ditunda sejak MVP, wajib begitu ada uang yang masuk

**Lulus ketika:** penagihan berjalan tanpa perlu disentuh setiap bulan

> **Catatan.** Di sinilah payment gateway baru masuk hitungan — setelah ada pendapatan nyata yang membenarkan biayanya. Membangunnya lebih awal berarti mengintegrasikan sistem pembayaran untuk produk yang belum tentu ada yang mau bayar.

---

## 11 — Fase 5 — Platform Lain

Titik di mana abstraksi platform akhirnya sah dibuat. Sebelum ada dua platform nyata, lapisan abstraksi hanya menambah berkas tanpa menambah kemampuan — itu sebabnya seluruh fase sebelumnya menulis kode Instagram secara langsung.

### Platform Lain · perkiraan 3–4 minggu per platform

**Mulai ketika:** retensi Instagram sudah stabil, dan minimal 5 pengguna meminta platform yang sama.

**Isi fase:**
- Facebook Page
- TikTok
- Threads
- Terbit ke beberapa platform sekaligus
- Caption berbeda per platform
- Status terpisah per platform

**Utang dibayar:** abstraksi platform — dibuat di sini, bukan sebelumnya

**Lulus ketika:** platform kedua berjalan dengan keandalan setara Instagram

> **Mulai dari.** Facebook Page lebih dulu. Token dan API-nya satu keluarga dengan Instagram, jadi biayanya paling kecil sekaligus jadi ujian yang jujur untuk rancangan abstraksinya sebelum menghadapi TikTok yang benar-benar berbeda.

---

## 12 — Fase 6 — Analitik & Otomasi

Nilai tambah, bukan fondasi. Semua isinya berguna, tidak satu pun mendesak — produk tetap dipakai tanpa fase ini.

### Analitik & Otomasi · perkiraan terbuka

**Mulai ketika:** pengguna mulai bertanya sendiri "postingan mana yang paling bagus?" — bukan ketika Anda merasa fitur ini akan berguna.

**Isi fase:**
- Angka per post
- Laporan per akun & periode
- Ekspor laporan untuk klien
- Rekomendasi jam dari data sendiri
- Slot antrian berulang
- Unggah massal
- Bantuan caption
- Ambil media dari Google Drive

**Lulus ketika:** tidak ada — fase ini terbuka dan dikerjakan sepotong-sepotong sesuai permintaan

---

## 13 — Tidak Akan Dibangun

Daftar ini sama pentingnya dengan peta jalan. Semuanya akan terpikir suatu saat — jawabannya sudah disiapkan sekarang supaya tidak perlu diperdebatkan lagi nanti.

| Tidak dibangun | Alasan |
|---|---|
| **Auto-DM & auto-komentar** | Melanggar ketentuan Instagram. Risikonya ditanggung akun klien, bukan kita — dan itu tidak bisa diterima. |
| **Auto-follow / unfollow** | Sama, ditambah merusak akun yang justru sedang dibantu. |
| **Penambah pengikut** | Bukan hanya melanggar aturan, tapi juga menghancurkan kepercayaan produk. |
| **Aplikasi mobile native** | Dua basis kode untuk memelihara. Tampilan web yang responsif sudah cukup untuk pekerjaan menjadwalkan. |
| **Editor gambar & video** | Canva sudah melakukannya jauh lebih baik. Cukup terima hasilnya. |
| **Kotak masuk DM terpadu** | Itu produk yang berbeda, bukan perluasan dari penjadwal. Membangunnya berarti memulai proyek kedua. |
| **Penjadwalan Story** | Hilang dalam 24 jam sehingga nilai penjadwalannya kecil, sementara biaya membangunnya setara dengan feed. |

---

## 14 — Penjaga Keputusan

Peta jalan hanya berguna kalau ada yang menahan godaan menyalipnya. Tiga pertanyaan berikut dijawab sebelum fitur apa pun ditambahkan, termasuk fitur yang sudah tertulis di dokumen ini.

1. **Apakah ada pengguna nyata yang memintanya?**
   Kalau jawabannya "nanti pasti ada yang minta", itu tebakan — dan tebakan menunggu.
2. **Apakah syarat masuk fasenya sudah terpenuhi?**
   Kalau belum, fitur ini sedang menyalip antrean. Ada alasan mengapa urutannya dibuat.
3. **Apa yang jadi lebih rumit selamanya kalau ini dibangun?**
   Setiap fitur menambah beban perawatan yang tidak pernah hilang. Kalau bebannya tidak sebanding, jawabannya tidak.

> **Satu saja jawabannya ragu — tunggu.** Fitur yang benar-benar dibutuhkan akan diminta lagi.

### 14.1 Tanda-tanda mulai tersesat

- Fitur baru dikerjakan padahal ukuran fase sebelumnya belum tercapai.
- Ada abstraksi yang dibuat untuk platform atau kebutuhan yang belum ada wujudnya.
- Fitur ditiru dari Buffer hanya karena Buffer punya, bukan karena ada yang meminta.
- Rilis ditunda demi fitur yang belum diminta siapa pun.
- Ada kegagalan publikasi yang dibiarkan tanpa diketahui sebabnya — itu masalah yang lebih mendesak daripada fitur mana pun.
- Sudah lebih dari sebulan tidak berbicara dengan satu pun pengguna.

---

## 15 — Teknis

### 15.1 Tumpukan

| Bagian | Pilihan | Alasan |
|---|---|---|
| Kerangka | Laravel + Blade | Auth, queue, scheduler, dan mail sudah bawaan |
| Database | MySQL | Skema kecil, tidak butuh yang khusus |
| Antrian | Driver `database` | Cukup untuk volume MVP, tanpa layanan tambahan |
| Penjadwal | Satu cron ke `schedule:run` | Satu baris crontab untuk seluruh penjadwalan |
| Worker | Supervisor menjalankan `queue:work` | Alasan utama memilih VPS, bukan shared hosting |
| OAuth | Laravel Socialite | Driver Facebook sudah tersedia |
| Media | Storage lokal + `storage:link` | Instagram mengambil gambar dari URL publik |
| Antarmuka | Blade + Tailwind, sedikit Alpine | Halaman sederhana, tidak butuh SPA |
| Server | VPS 1 vCPU / 2 GB, Ubuntu LTS | Cukup sampai sekitar Fase 3 |

### 15.2 Skema data MVP

**`social_accounts`**

| Kolom | Catatan |
|---|---|
| `user_id` | Pemilik akun di sistem |
| `ig_user_id` | ID akun Instagram Business |
| `ig_username` | Hanya untuk ditampilkan di antarmuka |
| `fb_page_id` | Page Facebook yang tertaut |
| `access_token` | Disimpan terenkripsi |
| `token_expires_at` | Pemicu pembaruan token |

**`posts`**

| Kolom | Catatan |
|---|---|
| `social_account_id` | Relasi ke akun terhubung |
| `caption` | Isi caption |
| `media_path` | Satu foto per post (lihat bagian 16) |
| `scheduled_at` | Waktu terbit, disimpan UTC |
| `status` | `scheduled`, `publishing`, `published`, `failed` |
| `ig_media_id` | Hasil dari Instagram setelah terbit |
| `error_message` | Alasan kegagalan |
| `published_at` | Waktu terbit sebenarnya |

### 15.3 Cara publikasi berjalan

1. **Penjadwal memeriksa antrian** — Setiap menit, cari post berstatus `scheduled` yang waktunya sudah lewat, ubah statusnya jadi `publishing`, lalu lempar ke antrian. Perubahan status inilah yang mencegah satu post terkirim dua kali.
2. **Job mengirim ke Instagram** — Buat wadah media dengan URL foto dan caption, lalu terbitkan. Kalau Instagram menjawab bahwa medianya belum siap, job dibiarkan gagal dan dicoba ulang — tidak perlu menulis logika menunggu sendiri sampai Fase 2.
3. **Percobaan ulang** — Tiga kali percobaan dengan jeda yang makin panjang, memakai mekanisme bawaan antrian Laravel.
4. **Selesai atau gagal** — Berhasil → simpan ID media dan waktu terbit. Habis percobaan → status `failed`, simpan alasannya, kirim email ke pemilik.

### 15.4 Konvensi penamaan

Sasarannya satu: developer Indonesia mana pun yang membuka proyek ini — termasuk yang baru bergabung enam bulan lagi — bisa menebak isi sebuah fungsi hanya dari namanya.

| Aturan | Alasan |
|---|---|
| Nama kode — class, fungsi, variabel, tabel, kolom — memakai **bahasa Inggris sederhana** | Laravel dan API Instagram keduanya berbahasa Inggris. Mencampur dua bahasa dalam satu baris justru memperlambat pembacaan. |
| **Tanpa singkatan.** `$socialAccount`, bukan `$sa` | Singkatan adalah sumber kebingungan terbesar, jauh melebihi soal pilihan bahasa. |
| **Kata sehari-hari**, bukan kosakata rumit. `publishPost()`, bukan `orchestratePublication()` | Kosakata Inggris tingkat lanjut adalah hambatan yang nyata; kata sederhana dipahami semua orang. |
| **Bahasa Indonesia** untuk teks antarmuka, pesan error, komentar yang menjelaskan *alasan*, README, dan pesan commit | Di tempat inilah bahasa Indonesia benar-benar membantu — menjelaskan konteks, bukan menamai variabel. |
| Semua teks antarmuka dikumpulkan di `lang/id/` | Fitur bawaan Laravel. Copy antarmuka terkumpul di satu tempat, tidak berserakan di Blade. |

**Hindari:**

```php
$sa = SocialAccount::find($id);
$p = new Post();
$p->sched = $req->dt;

function prosesJadwalPosting($d) {
    // cek dulu
}
```

**Pakai ini:**

```php
$socialAccount = SocialAccount::find($id);
$post = new Post();
$post->scheduled_at = $request->scheduled_at;

function publishPost(Post $post) {
    // Instagram menolak caption > 2200 karakter,
    // potong lebih dulu
}
```

### 15.5 Istilah domain

| Istilah di dokumen & antarmuka | Nama di kode |
|---|---|
| Akun terhubung | `SocialAccount` |
| Postingan | `Post` |
| Antrian konten | `upcomingPosts` |
| Menerbitkan | `publish` |
| Wadah media Instagram | `mediaContainer` |
| Waktu terbit | `scheduled_at` |
| Ruang kerja *(Fase 3)* | `Workspace` |

### 15.6 Zona waktu

Waktu disimpan dalam UTC sesuai bawaan Laravel, ditampilkan dalam WIB. Pemilihan zona waktu per pengguna ditunda sampai ada pengguna di luar WIB yang mengeluh.

### 15.7 Keamanan

| Aspek | Penanganan |
|---|---|
| Token akses | Disimpan terenkripsi lewat *cast* bawaan Laravel, tidak pernah ditampilkan di antarmuka maupun log |
| Otorisasi | Pengguna hanya bisa membuka akun dan post miliknya sendiri |
| Unggahan | Validasi tipe berkas dan ukuran, nama berkas dibuat ulang oleh sistem |
| Koneksi | HTTPS wajib |
| Cadangan | Dump database harian ke penyimpanan di luar server |

> **Catatan.** Lima poin di atas tidak ikut disederhanakan meskipun ini MVP, dan tidak ikut ditunda ke fase mana pun. Selebihnya boleh seadanya — bagian ini tidak.

---

## 16 — Utang Teknis Terencana

Setiap penyederhanaan di MVP dicatat di sini beserta kapan harus dibayar dan tanda bahwa waktunya sudah tiba. Utang yang dicatat adalah keputusan; utang yang tidak dicatat adalah kelalaian yang ditemukan orang lain enam bulan kemudian.

| Penyederhanaan | Dibayar di | Tandanya sudah waktunya |
|---|---|---|
| Satu foto per post | Fase 1 | Carousel masuk cakupan |
| Tanpa menunggu status wadah | Fase 2 | Video masuk cakupan |
| Media di disk server | Fase 2 | Disk terpakai >70%, atau video masuk |
| Antrian di database | Fase 3 | Job menumpuk lebih cepat dari yang diproses |
| Kepemilikan per pengguna | Fase 3 | Ada orang kedua yang butuh akses akun yang sama |
| Tanpa verifikasi email | Fase 4 | Ada uang masuk, atau muncul pendaftaran sampah |
| Kode Instagram langsung, tanpa abstraksi | Fase 5 | Platform kedua masuk cakupan |
| Zona waktu WIB tetap | Sesuai kebutuhan | Ada pengguna WITA atau WIT yang mengeluh |
| Satu server tunggal | Sesuai kebutuhan | CPU atau RAM bertahan di atas 70% |

---

## 17 — Risiko

| Tingkat | Risiko | Penanganan |
|---|---|---|
| **Tinggi** | Peninjauan aplikasi oleh Meta memakan 2–4 minggu dan bisa ditolak | Ajukan sedini mungkin. Selama menunggu, uji pakai mode pengembangan dengan akun sendiri sebagai penguji. |
| **Tinggi** | Ketergantungan penuh pada satu platform — kebijakan Meta berubah, produk ikut terdampak | Fase 5 mengurangi ini, tapi tidak menghilangkannya. Risiko ini diterima secara sadar. |
| **Sedang** | Akun klien masih personal, belum Business/Creator | Siapkan panduan konversi singkat di halaman hubungkan akun. |
| **Sedang** | Token kedaluwarsa dan koneksi putus diam-diam | Job pembaruan token terjadwal, plus email peringatan kalau pembaruan gagal. |
| **Sedang** | Meta mengubah API dan publikasi berhenti bekerja | Seluruh pemanggilan API dikumpulkan di satu kelas, supaya perbaikan hanya menyentuh satu berkas. |
| **Rendah** | Batas 100 postingan per akun per 24 jam | Jauh di atas kebutuhan target pengguna. Cukup tampilkan pesan yang jelas kalau sampai kena. |

---

## 18 — Rencana Rilis MVP

Perkiraan kasar untuk satu orang yang mengerjakan paruh waktu. Urutannya yang penting, bukan angkanya.

| Tahap | Hasil | Perkiraan |
|---|---|---|
| **M0** | Proyek Laravel berdiri, autentikasi jalan, VPS siap | 3–4 hari |
| **M1** | Akun Instagram bisa dihubungkan dan tersimpan — **ajukan review Meta di sini** | 1 minggu |
| **M2** | Post bisa disusun, diunggah, dan tersimpan sebagai terjadwal | 1 minggu |
| **M3** | Publikasi otomatis berjalan — penjadwal, antrian, percobaan ulang, email gagal | 1–2 minggu |
| **M4** | Daftar antrian, ubah, batalkan, dan status per post | 1 minggu |
| **M5** | Uji tertutup dengan 3–5 pengguna nyata | 2 minggu |

> **Urutan.** M3 dikerjakan sebelum M4 dengan sengaja. Publikasi otomatis adalah satu-satunya bagian yang bisa membatalkan seluruh ide kalau ternyata tidak bisa diandalkan — jadi ia dibuktikan lebih dulu, sebelum waktu dihabiskan untuk mempercantik antarmuka.

---

## 19 — Keputusan Terbuka

- [ ] **Nama produk** — "Antrian Konten" masih nama kerja sementara.
- [ ] **Lokasi repositori** — Repo baru terpisah, atau menumpang struktur yang sudah ada.
- [ ] **Pengguna pertama** — Klien sendiri yang sudah dikenal, atau dibuka untuk umum sejak awal. Klien sendiri lebih cepat memberi masukan jujur.
- [ ] **Posisi carousel** — Tetap di Fase 1, atau naik ke MVP kalau ternyata klien mayoritas memposting carousel.

---

*Dokumen ini menggantikan blueprint teknis sebelumnya. Belum ada kode yang ditulis. Bagian 06 dan 15 dikunci setelah empat keputusan di bagian 19 selesai dibahas; bagian 07–12 sengaja dibiarkan longgar dan ditinjau ulang setiap kali sebuah fase lulus.*
