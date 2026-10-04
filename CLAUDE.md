# Sosmedhub Developer Notes

Sosmedhub mengikuti [PRD_Sosmedhub.md](PRD_Sosmedhub.md). MVP harus tetap kecil: satu foto, satu caption, satu akun tujuan, dan publikasi otomatis ke Instagram.

## Prinsip

- Nama kode memakai bahasa Inggris sederhana.
- Teks antarmuka, validasi, dokumentasi, dan pesan commit memakai Bahasa Indonesia.
- Jangan membuat abstraksi multi-platform sebelum platform kedua masuk cakupan.
- Jangan memakai tabel `posts` untuk postingan Instagram karena nama itu pernah dipakai modul blog template. Pakai nama yang eksplisit seperti `scheduled_posts` atau `social_posts`.
- Token akses harus disimpan terenkripsi dan tidak boleh muncul di log atau UI.
- Waktu disimpan UTC dan ditampilkan dalam WIB.

## Modul Yang Dipertahankan

- `Dashboard`
- `User`
- `Role`
- `Menu`
- `SiteSetting`

Modul CMS lama sudah dihapus supaya tidak bentrok dengan domain produk Sosmedhub.
