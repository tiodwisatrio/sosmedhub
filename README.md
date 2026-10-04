# Sosmedhub

Sosmedhub adalah aplikasi web untuk menjadwalkan postingan Instagram berbahasa Indonesia.

MVP berfokus pada satu alur utama:

1. Pengguna daftar dan masuk.
2. Pengguna menghubungkan akun Instagram Business lewat Facebook.
3. Pengguna membuat postingan satu foto dan caption.
4. Pengguna memilih tanggal serta jam terbit.
5. Sistem menerbitkan postingan otomatis dan memberi tahu jika gagal.

Dokumen produk utama ada di [PRD_Sosmedhub.md](PRD_Sosmedhub.md).

## Stack

- Laravel + Blade
- Tailwind CSS
- MySQL
- Queue database
- Laravel scheduler

## Setup Lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Untuk development:

```bash
composer run dev
```
