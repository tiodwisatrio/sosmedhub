# Dokumentasi Audit Keamanan — Fitur Login (crafthink)

Dokumen ini adalah hasil audit keamanan khusus untuk **fitur autentikasi/login** pada CMS `crafthink`,
lengkap dengan langkah perbaikan yang bisa langsung dijalankan.

Terakhir diperbarui: 2 Juli 2026
Basis analisis: Laravel 13 + Livewire + Breeze (Volt) + spatie/laravel-permission

File terkait yang diaudit:
- `resources/views/livewire/pages/auth/login.blade.php` (komponen login)
- `app/Livewire/Forms/LoginForm.php` (logika autentikasi + rate limit)
- `resources/views/livewire/pages/auth/forgot-password.blade.php`
- `resources/views/livewire/pages/auth/reset-password.blade.php`
- `routes/auth.php`
- `config/session.php`
- `app/Models/User.php`

---

## Ringkasan Eksekutif

Alur login berbasis Laravel Breeze standar dan **fondasinya kuat** (anti brute-force,
anti session-fixation, anti user-enumeration, hashing timing-safe). Bagian tersulit
sudah benar. Yang perlu ditutup lebih ke **kontrol akses** dan **pengerasan konfigurasi**.

Nilai keamanan fitur login saat ini: **7.5 / 10**
Setelah temuan #1, #2, dan #4 diperbaiki: **≈ 9 / 10**

| # | Temuan | Tingkat | Dampak |
|---|--------|---------|--------|
| 1 | Login tidak mengecek `status` akun | SEDANG–TINGGI | Akun nonaktif tetap bisa login |
| 2 | Tidak ada rate limit pada "Lupa Password" | SEDANG | Email bombing / probing |
| 3 | Rate limit login hanya per email+IP | SEDANG | Celah credential stuffing |
| 4 | `SESSION_SECURE_COOKIE` belum dipaksa di produksi | SEDANG | Cookie sesi rawan disadap |
| 5 | Email reset di-prefill dari query string | RENDAH | Input tak tervalidasi |

---

## Bagian yang SUDAH AMAN (jangan diubah)

Diakui lebih dulu agar tidak salah "memperbaiki" yang sudah benar:

- **Rate limiting brute-force** — `LoginForm::authenticate()` membatasi 5 percobaan gagal
  per kombinasi email+IP, lalu mengunci dengan event `Lockout`.
- **Anti session fixation** — `Session::regenerate()` dipanggil setelah login sukses.
- **Anti user enumeration** — kegagalan login selalu membalas pesan generik `auth.failed`,
  tidak membedakan "email tidak ada" vs "password salah".
- **Perbandingan password timing-safe** lewat `Auth::attempt` (hash, bukan `==`).
- **CSRF** ditangani otomatis oleh Livewire/Laravel.
- **Logout benar** — `session()->invalidate()` + `regenerateToken()`.
- **Reset password aman** — token di-`#[Locked]` (anti-tamper), memakai Password broker
  Laravel (token di-hash & ada masa berlaku).
- **Verifikasi email aman** — route pakai `signed` + `throttle:6,1`.

---

## Temuan & Perbaikan

### 1. SEDANG–TINGGI — Login tidak mengecek `status` akun

**Masalah.**
Model `User` memiliki kolom `status` (boolean aktif/nonaktif) yang dapat diatur admin
lewat UserController. Namun `authenticate()` hanya memakai email + password:

```php
Auth::attempt($this->only(['email', 'password']), $this->remember)
```

`status` diabaikan. Akibatnya **menonaktifkan user di panel admin tidak benar-benar
memblokir login-nya.** User yang sudah "dimatikan" (karyawan keluar, akun dicurigai bocor)
tetap bisa masuk selama password benar. Ini membuat fitur nonaktif akun tidak berfungsi
sesuai maksud.

**Perbaikan.**
Di `app/Livewire/Forms/LoginForm.php`, sertakan `status` pada kredensial:

```php
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    $credentials = $this->only(['email', 'password']);
    $credentials['status'] = 1;   // hanya akun aktif yang boleh login

    if (! Auth::attempt($credentials, $this->remember)) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

User nonaktif akan gagal login dengan pesan generik yang sama — tanpa membocorkan bahwa
akunnya ada tapi dinonaktifkan.

**Perbaikan lanjutan (opsional).**
Poin di atas hanya memblokir *saat login*. User yang dinonaktifkan di tengah sesi tetap
aktif sampai sesi habis. Untuk pemutusan langsung, buat middleware yang mengecek status
tiap request di area admin:

```php
// app/Http/Middleware/EnsureUserIsActive.php
public function handle($request, Closure $next)
{
    if (auth()->check() && ! auth()->user()->status) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
    }

    return $next($request);
}
```
Daftarkan middleware ini pada grup route admin.

**Cara uji.** Nonaktifkan sebuah user lewat panel, lalu coba login dengan akun itu →
harus gagal.

---

### 2. SEDANG — Tidak ada rate limit pada "Lupa Password"

**Masalah.**
Route `forgot-password` (`sendPasswordResetLink`) tidak memiliki throttle sama sekali,
berbeda dengan login dan verify-email. Ini membuka peluang:
- membanjiri email korban dengan link reset (email bombing), dan
- penyalahgunaan endpoint untuk probing.

**Perbaikan.**
Tambahkan throttle pada route di `routes/auth.php`:

```php
Volt::route('forgot-password', 'pages.auth.forgot-password')
    ->middleware('throttle:6,1')   // maks 6 permintaan / menit / IP
    ->name('password.request');

Volt::route('reset-password/{token}', 'pages.auth.reset-password')
    ->middleware('throttle:6,1')
    ->name('password.reset');
```

**Cara uji.** Kirim permintaan reset berkali-kali cepat → setelah batas, muncul respons 429.

---

### 3. SEDANG — Rate limit login hanya per email+IP

**Masalah.**
Throttle key-nya `email|ip`. Cukup untuk kebanyakan kasus (default Breeze), tapi:
- serangan *credential stuffing* dengan **banyak email berbeda dari satu IP** tidak
  pernah kena batas (key berubah tiap email), dan
- serangan dengan **banyak IP** bisa menyerang satu akun lebih lama.

**Perbaikan (opsional, sesuai tingkat risiko).**
Tambahkan batas kedua murni per-IP sebagai jaring pengaman. Di `LoginForm.php`:

```php
protected function ensureIsNotRateLimited(): void
{
    $ipKey = 'login-ip|'.request()->ip();

    if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)
        || RateLimiter::tooManyAttempts($ipKey, 20)) {   // maks 20 gagal / IP
        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
```

Dan saat login gagal di `authenticate()`, catat juga IP key:

```php
RateLimiter::hit($this->throttleKey());
RateLimiter::hit('login-ip|'.request()->ip());
```

---

### 4. SEDANG — `SESSION_SECURE_COOKIE` belum dipaksa di produksi

**Masalah.**
`config/session.php` membaca `'secure' => env('SESSION_SECURE_COOKIE')` yang default-nya
`null`. Tanpa disetel `true` di produksi (HTTPS), cookie sesi berpotensi terkirim lewat
koneksi non-HTTPS dan rawan disadap (session hijacking).

**Perbaikan.**
Di `.env` produksi:

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

`http_only` sudah default `true` (baik — cookie tidak bisa dibaca JavaScript).

**Cara uji.** Di produksi, periksa cookie sesi di DevTools → atribut `Secure` dan
`HttpOnly` harus aktif.

---

### 5. RENDAH — Email reset di-prefill dari query string

**Masalah.**
Di komponen `reset-password`, `mount()` mengisi
`$this->email = request()->string('email')` — nilai dari query string dipercaya langsung.
Blade meng-escape output (jadi bukan XSS) dan Password broker tetap memvalidasi pasangan
token–email, sehingga risikonya rendah. Namun input sebaiknya divalidasi agar bersih.

**Perbaikan.**
Validasi format email saat mount:

```php
public function mount(string $token): void
{
    $this->token = $token;

    $email = request()->string('email')->toString();
    $this->email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}
```

---

## Checklist Verifikasi Akhir

- [ ] User nonaktif tidak bisa login (#1).
- [ ] (Opsional) User dinonaktifkan di tengah sesi langsung ter-logout (#1 lanjutan).
- [ ] Endpoint forgot-password & reset-password kena throttle (#2).
- [ ] (Opsional) Batas per-IP aktif di login (#3).
- [ ] `SESSION_SECURE_COOKIE=true` di produksi, cookie tampil `Secure` + `HttpOnly` (#4).
- [ ] Email reset tervalidasi format (#5).
- [ ] Tambah test otomatis: login gagal untuk akun nonaktif, dan throttle bekerja.

---

## Urutan Eksekusi yang Disarankan

1. **Segera:** #1 — satu-satunya lubang "fungsional" yang membuat fitur nonaktif akun
   tidak berjalan sesuai maksud.
2. **Menyusul:** #2 (throttle lupa-password), #4 (secure cookie produksi).
3. **Sesuai kebutuhan:** #3 (rate limit per-IP), #5 (validasi email query).

Setelah #1, #2, dan #4 diterapkan dan diuji, fitur login layak dinilai sekitar **9/10**.
