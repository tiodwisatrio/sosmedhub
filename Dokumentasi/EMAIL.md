# Konfigurasi Email — CMS Craftboard

---

## Stack yang Digunakan

- **Laravel Mail** — built-in mailer Laravel
- **Resend** — layanan transaksional email (3.000 email/bulan gratis)
- **Domain pengirim:** `companyprojectsector21.com`

---

## Konfigurasi `.env`

```env
APP_URL=https://companyprojectsector21.com   # Penting untuk link reset password

MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_ENCRYPTION=tls
MAIL_USERNAME=resend
MAIL_PASSWORD=re_xxxxxxxxxxxxxxxxxxxxxxxxxx   # API Key dari Resend
MAIL_FROM_ADDRESS="noreply@companyprojectsector21.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Catatan:** Jangan commit API Key ke repository. Pastikan `.env` ada di `.gitignore`.

---

## Reset Password

### Cara Kerja

```
User klik "Forgot Password"
    ↓
Masukkan email
    ↓
Laravel kirim email via Resend
    ↓
User terima link reset password
    ↓
Klik link → halaman reset password (sesuai APP_URL)
    ↓
Masukkan password baru → selesai
```

### Penting: APP_URL

Link reset password yang dikirim ke email menggunakan nilai `APP_URL` di `.env`.

| Environment | APP_URL |
|---|---|
| Development (lokal) | `http://craftboard.test` |
| Production | `https://companyprojectsector21.com` |

**Di development:** link reset password hanya bisa dibuka di komputer yang sama tempat Herd berjalan, karena `craftboard.test` adalah domain lokal.

**Di production:** pastikan `APP_URL` diset ke domain production sebelum deploy.

---

## Nama Pengirim Email (Dinamis)

Nama pengirim email (`MAIL_FROM_NAME`) mengikuti `APP_NAME` di `.env`.  
Untuk mengubah nama pengirim tanpa sentuh `.env`, ubah **Nama Aplikasi** di:

**Admin Panel → Setting → Pengaturan Situs**

> Pastikan `APP_NAME` di `.env` juga disamakan agar konsisten sebagai fallback.

---

## Setup Resend (Referensi)

### DNS Record yang Dipasang di cPanel

| Type | Name | Content |
|---|---|---|
| TXT | `resend._domainkey` | `p=MIGfMA...QIDAQAB` (dari Resend) |
| MX | `send` | `feedback-smtp...ses.com` (dari Resend) |
| TXT | `send` | `v=spf1 include:...amazonses.com ~all` (dari Resend) |

DNS dikelola di **cPanel → Zone Editor → companyprojectsector21.com**.

### Menambah/Mengganti Domain di Resend

1. Login ke Resend
2. **Domains** → **Add Domain**
3. Ikuti instruksi DNS → verifikasi
4. **API Keys** → **Create API Key** → update `MAIL_PASSWORD` di `.env`
5. Jalankan `php artisan config:clear`

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Email tidak terkirim | Cek `MAIL_PASSWORD` (API Key) masih valid di Resend |
| Link reset password tidak bisa dibuka | Pastikan `APP_URL` sesuai environment |
| Nama pengirim masih "Company" | Update `APP_NAME` di `.env` + `php artisan config:clear` |
| Domain belum verified di Resend | Cek DNS record sudah benar di cPanel, tunggu propagasi |

---

## Perintah Berguna

```bash
# Clear cache setelah update .env
php artisan config:clear

# Test kirim email via tinker
php artisan tinker --execute '
\Illuminate\Support\Facades\Mail::raw("Test email", function($msg) {
    $msg->to("emailkamu@gmail.com")->subject("Test");
});
echo "Sent";
'
```
