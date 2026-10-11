# Catatan App Review Meta: izin dan bagian aplikasi yang memperagakannya

Catatan pribadi untuk merekam screencast. Aturan Meta yang dipegang:

- Satu rekaman wajib memperlihatkan **tiap izin** yang diminta. Izin tanpa rekaman **tidak disetujui**.
- **Tanpa suara.** Reviewer tidak mendengar audio; jelaskan lewat teks overlay.
- Resolusi 1080p atau lebih, rekam hanya yang perlu terlihat.
- Tunjukkan pengguna **menyetujui izin** di dialog Meta, lalu bagaimana aplikasi memakainya.
- Satu deskripsi tertulis per izin, **tidak boleh disalin-tempel** antar izin.

## Sebelum merekam

- [ ] Rekam di **domain produksi (HTTPS)**, bukan tunnel atau `sosmedhub.test`.
- [ ] Ubah bahasa ke **English** lewat pemilih bahasa (ikon bendera di topbar).
- [ ] Buka dari host yang sama dengan redirect URI (supaya `state` OAuth tidak ditolak).
- [ ] Pakai akun uji sendiri; jangan tampilkan token, email pribadi, atau kunci.
- [ ] Akun Instagram bertipe Business/Creator; ada satu Page Facebook. Untuk `business_management`, Page sebaiknya milik **Business Portfolio**.
- [ ] Sebelum mulai rekam: putuskan akun uji dari aplikasi **dan** cabut aplikasi di pengaturan Instagram/Facebook, supaya dialog izin muncul lagi.
- [ ] `REGISTRATION_AUTO_APPROVE` mati; reviewer memakai akun yang sudah disetujui.

## Peta izin ke bagian aplikasi

### Instagram (use case "Instagram API", login Instagram)

| Izin | Bagian aplikasi | Yang harus terlihat | Caption saran (English) |
|---|---|---|---|
| `instagram_business_basic` | **Akun Sosial** (`/admin/social-accounts`) → Instagram → **Hubungkan** → dialog izin Instagram → kembali ke Akun Sosial | Dialog izin disetujui; kartu akun muncul dengan foto profil, nama, dan `@username` | "Reads the account's username and profile picture so the right account is shown." |
| `instagram_business_content_publish` | **Buat Postingan** (`/admin/scheduled-posts/create`) → pilih akun Instagram → Feed, Story, atau Reels → unggah media → **Jadwalkan Terbit** → tunggu waktu → **Riwayat** berstatus Terbit → buka Instagram, postingan tampil | Form jadwal, pilihan format, jam terbit, status Terbit, dan postingan di Instagram | "Publishes the photo/video the user scheduled to their own Instagram account." |

### Facebook (use case "Kelola segala sesuatu di Halaman Anda" / Facebook Login)

| Izin | Bagian aplikasi | Yang harus terlihat | Caption saran (English) |
|---|---|---|---|
| `pages_show_list` | **Akun Sosial** → Facebook → **Hubungkan** → dialog izin Facebook → halaman **Pilih Halaman Facebook** | Dialog izin disetujui; daftar Page yang dikelola pengguna | "Lists the Pages the user manages so they can pick one to connect." |
| `pages_read_engagement` | Halaman **Pilih Halaman Facebook** (nama dan foto Page) → kartu Page di **Akun Sosial** (nama, foto, ID) | Nama dan foto Page tampil; Page tersambung | "Reads basic Page data (name, picture) and the Page token needed to publish, and the processing status of uploaded videos." |
| `pages_manage_posts` | **Buat Postingan** → pilih **Facebook Page** → Feed (foto), Story (foto/video), atau Reels → **Jadwalkan Terbit** → **Riwayat** Terbit → buka Page, postingan tampil | Form, format, status Terbit, dan postingan di Page | "Publishes the scheduled photo/video to the user's own Facebook Page." |
| `business_management` | Halaman **Pilih Halaman Facebook** memuat Page milik Business Portfolio | Page milik Business Portfolio muncul di daftar (tanpa izin ini daftar kosong) | "Lets Pages owned by the user's Business Portfolio appear in the Page list." |

`public_profile` ikut otomatis lewat Facebook Login dan umumnya tidak perlu diajukan atau direkam.

## Urutan satu rekaman per platform (saran)

**Rekaman Instagram**
1. Login ke aplikasi (English), buka Akun Sosial.
2. Klik Hubungkan Instagram, perlihatkan dialog izin Meta, setujui (`instagram_business_basic`).
3. Kartu akun muncul.
4. Buat Postingan: pilih akun Instagram, Feed + foto, tulis caption, pilih jam terbit (`instagram_business_content_publish`).
5. Ulangi singkat untuk Story dan Reels (format lain yang diminta).
6. Setelah jam tiba: Riwayat berstatus Terbit, buka Instagram dan tunjukkan postingannya.

**Rekaman Facebook**
1. Akun Sosial, Hubungkan Facebook, perlihatkan dialog izin Meta (`pages_show_list`, `pages_read_engagement`, `business_management`, `pages_manage_posts`).
2. Halaman Pilih Halaman Facebook: tunjukkan daftar Page (termasuk yang milik Business Portfolio), pilih satu, Hubungkan.
3. Kartu Page muncul di Akun Sosial.
4. Buat Postingan: pilih Facebook Page, Feed (foto), Story, Reels, jadwalkan (`pages_manage_posts`).
5. Riwayat Terbit, buka Page dan tunjukkan postingannya.

## Hal yang sering bikin ditolak (waspada)

- Dialog izin Meta tidak terlihat di rekaman.
- Ada izin di pengajuan yang tidak diperagakan di rekaman.
- Aplikasi tidak bisa diakses reviewer (login butuh persetujuan admin, domain mati, peringatan "Dangerous" Chrome pada tunnel).
- Kebijakan privasi tidak menyebut Facebook/izin yang diminta (sudah diperbaiki 11 Oktober 2026).
- Deskripsi tertulis sama persis antar izin.

## Yang belum bisa dipastikan dari sisi kode

- `pages_read_engagement` tidak punya layar khusus; yang terlihat hanyalah nama dan foto Page. Penjelasannya sebagian harus lewat caption/teks tertulis.
- Apakah reviewer bisa menghubungkan akun Instagram/Facebook mereka sendiri sebelum aplikasi berstatus Live perlu dicek di dokumentasi Meta.
- Business Verification diperlukan untuk Advanced Access (menurut dokumentasi Instagram Platform); mulai prosesnya lebih awal.
