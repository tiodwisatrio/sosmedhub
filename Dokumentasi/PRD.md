# Product Requirements Document (PRD)
## CMS Master — Laravel 13 Modular CMS with RBAC

**Version:** 1.0  
**Date:** 2026-06-23  
**Status:** Draft

---

## 1. Overview

CMS Master adalah sebuah Content Management System berbasis Laravel 13 yang bersifat modular, maintainable, dan dilengkapi dengan Role-Based Access Control (RBAC). Sistem ini dirancang sebagai template CMS yang dapat dikembangkan lebih lanjut sesuai kebutuhan proyek.

---

## 2. Tujuan

- Menyediakan template CMS yang modular dan mudah dikembangkan
- Memisahkan concerns antara autentikasi, otorisasi, dan fitur domain
- Mendukung multi-role dan multi-permission secara granular
- Memisahkan tampilan admin dan frontend secara jelas

---

## 3. Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 13 |
| PHP | >= 8.2 |
| Database | MySQL 8+ |
| Authentication | Laravel Breeze |
| RBAC | spatie/laravel-permission |
| Modular | nwidart/laravel-modules |
| Frontend Admin | Blade + Livewire |
| CSS | Tailwind CSS |
| Build Tool | Vite |

---

## 4. Arsitektur

### 4.1 Prinsip Arsitektur

- **Modular** — setiap fitur domain dikemas dalam modul mandiri
- **Thin Controller** — controller hanya menerima request dan return response
- **Service Layer** — business logic ada di `Services/`, bukan di controller atau model
- **Form Request** — validasi dipisah dari controller
- **Policy** — otorisasi per model, terintegrasi dengan Spatie Permission
- **Repository** *(opsional)* — digunakan jika query kompleks dan dipakai di banyak tempat

### 4.2 Aturan Service Layer

| Kondisi | Pendekatan |
|---|---|
| CRUD sederhana tanpa logic tambahan | Langsung pakai Model di Controller |
| Ada relasi, upload file, kalkulasi | Tambahkan `Services/` |
| Query kompleks dipakai banyak tempat | Tambahkan `Repositories/` |

---

## 5. Struktur Folder

```
app/                              # Core Laravel (BUKAN modul)
├── Http/
│   ├── Controllers/
│   │   └── Auth/                 # Breeze — login, register, dll
│   └── Middleware/
├── Models/
│   └── User.php                  # Model User tetap di sini
└── Providers/

Modules/
├── Dashboard/                    # Halaman dashboard admin
├── Role/                         # Manajemen role & permission (RBAC)
├── User/                         # Manajemen user oleh admin
├── Category/                     # Kategori generik, dipakai lintas modul via scopeOfType()
├── Team/                         # Data tim
├── Layanan/                      # Layanan perusahaan
├── Keunggulan/                   # Keunggulan perusahaan
├── Menu/                         # Dynamic sidebar menu dari database
├── SiteSetting/                  # Konfigurasi situs (single-row)
├── Banner/                       # Banner promosi
├── Hero/                         # Section hero homepage
├── Klien/                        # Logo klien/partner
├── Faq/                          # FAQ
├── Paket/                        # Paket harga/layanan
├── Post/                         # Blog/artikel — admin CRUD + frontend + SEO + rich-editor
├── Generator/                    # Generator modul CRUD baru via UI (/admin/generator)
└── (Product, Media)              # Belum dibangun

resources/
├── views/
│   ├── auth/                     # Views Breeze
│   └── layouts/
│       ├── admin.blade.php       # Layout admin
│       └── app.blade.php         # Layout frontend
```

### 5.1 Struktur Tiap Modul

```
Modules/{NamaModul}/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                # Controller untuk admin panel
│   │   └── Frontend/             # Controller untuk halaman publik (jika ada)
│   └── Requests/
│       ├── Store{Model}Request.php
│       └── Update{Model}Request.php
├── Models/
│   └── {Model}.php
├── Services/                     # Hanya jika ada business logic
│   └── {Model}Service.php
├── Repositories/                 # Hanya jika query kompleks
│   └── {Model}Repository.php
├── Routes/
│   └── web.php
├── Database/
│   └── Migrations/
├── Resources/
│   └── views/
│       ├── admin/                # Views admin panel
│       └── frontend/             # Views publik (jika ada)
└── Providers/
    └── {Modul}ServiceProvider.php
```

---

## 6. Routing

### 6.1 Konvensi Route

```php
// Frontend (publik/user biasa)
Route::middleware(['auth'])->group(function () {
    // ...
});

// Admin Panel — middleware hanya 'auth', permission check di controller
Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('users', Admin\UserController::class);
        Route::resource('products', Admin\ProductController::class);
        // ...
    });
```

> **Catatan:** Jangan gunakan `role:admin` di middleware route. Gunakan `HasMiddleware` di controller untuk pengecekan permission yang lebih granular.

### 6.2 URL Convention

| Area | URL Pattern | Contoh |
|---|---|---|
| Frontend | `/{resource}` | `/products` |
| Admin | `/admin/{resource}` | `/admin/products` |
| Auth | `/login`, `/forgot-password`, dll | Breeze default |

> **Registrasi publik (`/register`) sengaja dihapus.** CMS ini bukan aplikasi publik yang
> boleh didaftar siapa saja — akun baru cuma dibuat lewat modul User oleh admin, atau
> lewat seeder/tinker khusus untuk akun `developer`.

### 6.3 Named Route Convention

- Admin: `admin.{resource}.{action}` → `admin.products.index`
- Frontend: `{resource}.{action}` → `products.index`

---

## 7. RBAC (Role-Based Access Control)

### 7.1 Sistem Role

Role bersifat **dinamis** — disimpan di database dan bisa dikelola via UI admin panel.
Pengecualian: role `developer` diproteksi dan tidak bisa dihapus via UI.

### 7.2 Hirarki Role

```
developer        ← tertinggi, bypass semua permission, tidak bisa dihapus
super-admin      ← bisa manage role, permission & user
admin            ← akses penuh admin panel
[role dinamis]   ← misal: spv, editor, viewer — dibuat & dikelola via UI
```

### 7.3 Jenis Role

| Jenis | Contoh | Keterangan |
|---|---|---|
| **Protected** | `developer` | Di-seed, tidak bisa dihapus/diubah via UI |
| **Default** | `super-admin`, `admin` | Di-seed sebagai starter, bisa diubah via UI |
| **Dinamis** | `spv`, `editor`, `viewer` | Dibuat bebas oleh super-admin via UI |

### 7.4 Aturan Proteksi Role Developer

- Role `developer` di-seed via `DatabaseSeeder`
- Tidak muncul di halaman CRUD role (disembunyikan dari UI)
- Middleware custom memblokir delete/edit role `developer`
- Bypass semua pengecekan permission (via `Gate::before`)

```php
// AppServiceProvider atau AuthServiceProvider
Gate::before(function ($user, $ability) {
    if ($user->hasRole('developer')) {
        return true;
    }
});
```

### 7.5 Konvensi Permission

Format: `{resource}.{action}`

4 action standar per modul: `view`, `create`, `edit`, `delete`

Contoh:
- `user.view`, `user.create`, `user.edit`, `user.delete`
- `layanan.view`, `layanan.create`, `layanan.edit`, `layanan.delete`
- `menu.view`, `menu.create`, `menu.edit`, `menu.delete`
- `role.view`, `role.create`, `role.edit`, `role.delete`
- `site-setting.view`, `site-setting.edit` *(hanya 2 — tidak ada create/delete)*

### 7.6 Cara Kerja Permission

```
Seeder jalan
    ↓
Permission terdaftar di tabel 'permissions' (belum aktif ke role manapun)
    ↓
Admin buka Edit Role → assign permission via checklist UI
    ↓
Permission aktif untuk role tersebut
    ↓
User yang punya role baru bisa akses
```

**Pengecualian:** Role `developer` dan `super-admin` langsung di-assign semua permission di seeder.

```php
$developer = Role::create(['name' => 'developer']);
$developer->givePermissionTo(Permission::all());
```

### 7.7 UI Permission Matrix

Halaman edit role menampilkan tabel checklist per modul:

```
Modul           | View | Create | Edit | Delete
----------------|------|--------|------|-------
Layanan         |  ✓   |   ✓    |  ✗   |   ✗
User            |  ✓   |   ✗    |  ✗   |   ✗
Menu            |  ✓   |   ✓    |  ✓   |   ✗
SiteSetting     |  ✓   |   —    |  ✓   |   —
```

*SiteSetting hanya punya `view` dan `edit` — tidak ada create/delete karena single-row.*

Admin centang/uncentang lalu save — Spatie sync permission ke role:

```php
$role->syncPermissions($request->permissions);
```

### 7.8 Aturan Saat Tambah Modul Baru

Setiap modul baru wajib ditambahkan 4 permission-nya di seeder:
```php
Permission::insert([
    ['name' => 'layanan.view'],
    ['name' => 'layanan.create'],
    ['name' => 'layanan.edit'],
    ['name' => 'layanan.delete'],
]);
```

### 7.9 Penggunaan di Controller

Project ini **tidak** pakai Policy class. Permission dicek dua lapis:

```php
// Lapis 1 — HasMiddleware di controller (BUKAN middleware di route)
class LayananController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:layanan.view', only: ['index', 'show']),
            new Middleware('permission:layanan.create', only: ['create', 'store']),
        ];
    }
}

// Lapis 2 — authorize() di Form Request (independen dari middleware controller,
// supaya tetap aman kalau ada route baru yang lupa didaftarkan middleware-nya)
public function authorize(): bool
{
    return $this->user()?->can('layanan.create') ?? false;
}
```

---

## 8. Modul-Modul

### 8.1 Auth (Standard Laravel — bukan modul)
- Login, Register, Forgot Password, Reset Password
- Dihandle oleh Laravel Breeze
- Views di `resources/views/auth/`

### 8.2 Dashboard
- Halaman utama admin setelah login
- Statistik ringkas (total user, produk, dll)
- Hanya admin yang bisa akses

### 8.3 Role
- CRUD role (dinamis, disimpan di database)
- Assign permission ke role
- Assign role ke user
- Role `developer` diproteksi — tidak muncul di UI, tidak bisa dihapus
- Role `super-admin` dan `admin` di-seed sebagai default
- Menggunakan Spatie Laravel Permission

### 8.4 User
- CRUD user oleh admin
- Assign role ke user
- Filter & search user

### 8.5 Category (Simple CRUD)
- CRUD kategori
- Tidak memerlukan Service layer
- Bisa dipakai oleh modul lain (Product, Post)

### 8.6 Product — belum dibangun
- CRUD produk, relasi `belongsTo` ke Category, upload gambar, Service layer

### 8.7 Post (Blog/Artikel) — sudah dibangun
- CRUD artikel, relasi `belongsTo` ke Category (`Category::ofType('post')`)
- Status: `1` = published, `0` = draft. Pakai `SoftDeletes`.
- Field `content` pakai rich-editor (TinyMCE via CDN, toolbar dibatasi heading H2–H4) —
  **wajib** disanitasi `Purifier::clean()` di `PostService` sebelum disimpan
- Frontend publik: `/posts` (index, published only) dan `/posts/{post:slug}` (detail, 404 kalau draft)
- SEO: meta tag lengkap (`<x-seo-meta>`) + JSON-LD `BlogPosting`, masuk ke `/sitemap.xml`
- Detail lengkap: [MODUL-POST.md](MODUL-POST.md)

### 8.8 Media — belum dibangun
- Upload & manajemen file/gambar terpusat, dipakai modul lain, simpan di `storage/app/public`

### 8.9 Menu (Dynamic Sidebar)
- Menu sidebar dikelola dari database via admin UI
- Mendukung struktur parent-child (1 level kedalaman)
- Parent tanpa `route_name` → tampil sebagai dropdown group
- Field `permission` → menu hanya tampil jika user punya permission tersebut
- Drag-and-drop: reorder dalam level dan pindah antar level
- `$sidebarMenus` di-share ke layout via `View::composer`

### 8.10 SiteSetting
- Konfigurasi situs disimpan dalam **satu baris** di tabel `site_settings`
- Field: `app_name`, `deskripsi`, `alamat`, `no_telp`, `no_whatsapp`, `email`, `logo_atas`,
  `logo_bawah`, `icon`, `og_image`, `iframe_map` (URL embed Google Maps saja, bukan kode
  `<iframe>` mentah — celah XSS yang sudah ditutup)
- Field sosmed (nama + link): `instagram`, `facebook`, `tiktok`, `youtube`, `x`
- Field marketplace (nama + link): `shopee`, `tokopedia`, `blibli`
- Akses via `SiteSetting::current()` (auto-create jika belum ada)
- `$siteSetting` di-share ke **semua view** via `View::share()` di `SiteSettingServiceProvider`
- Upload gambar lewat `SiteSettingService` (bukan inline di controller)
- Hanya ada halaman index yang sekaligus berfungsi sebagai form edit

### 8.11 Banner, Hero, Klien, Faq, Paket
- CRUD konten sederhana untuk homepage/frontend (banner promosi, section hero, logo klien,
  FAQ, paket harga) — pola sama seperti Layanan/Keunggulan (upload gambar via Service, `status`,
  `urutan` untuk sorting, `SoftDeletes`)

### 8.12 Generator (Module Builder)
- `/admin/generator` — bikin modul CRUD baru (Model, Controller, Request, View, migration,
  route, permission) lewat form UI, tanpa nulis kode manual
- **Hanya untuk role `developer`**
- Field `label` divalidasi ketat + di-escape sebelum ditulis ke file view yang di-generate —
  kalau longgar, karakter Blade/HTML di label bisa jadi kode yang benar-benar dieksekusi
  saat view dirender (RCE). Lihat [MODUL-BARU.md](MODUL-BARU.md) untuk alternatif manual.

---

## 9. Konvensi Kode

### 9.1 Naming Convention

| Item | Convention | Contoh |
|---|---|---|
| Model | PascalCase singular | `Product` |
| Controller | PascalCase + Controller | `ProductController` |
| Service | PascalCase + Service | `ProductService` |
| Request | Store/Update + Model + Request | `StoreProductRequest` |
| Migration | snake_case | `create_products_table` |
| View (admin) | `admin/{resource}/{action}` | `admin/products/index` |
| View (frontend) | `frontend/{resource}/{action}` | `frontend/products/index` |

### 9.2 Controller Method Convention

Gunakan 7 method resourceful standar Laravel:
`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

### 9.3 Response Convention

- Setelah `store` / `update` / `destroy` → redirect ke `index` dengan flash message
- Flash message key: `success` atau `error`

---

## 10. Database

### 10.1 Konvensi Tabel

| Item | Convention | Contoh |
|---|---|---|
| Tabel | snake_case plural | `products` |
| Primary key | `id` (auto increment) | - |
| Foreign key | `{model}_id` | `category_id` |
| Pivot table | `{model1}_{model2}` (alphabetical) | `category_product` |
| Timestamps | `created_at`, `updated_at` | - |
| Soft delete | `deleted_at` | jika diperlukan |

### 10.2 Migration Convention

Semua migration modul ada di `Modules/{Modul}/Database/Migrations/`

---

## 11. Flow Development

### Sudah Dibangun
1. Setup project (Laravel 13, Breeze, Spatie, nwidart)
2. Konfigurasi layout admin & frontend
3. Modul Dashboard
4. Modul Role (RBAC management)
5. Modul User
6. Modul Category
7. Modul Team
8. Modul Layanan
9. Modul Keunggulan
10. Modul Menu (dynamic sidebar)
11. Modul SiteSetting
12. Modul Banner, Hero, Klien, Faq, Paket
13. Modul Post (admin CRUD + frontend + SEO + rich-editor + sanitasi)
14. Modul Generator (builder modul CRUD via UI)
15. Halaman error kustom (404/403/419/429/500)
16. SEO (meta tag, sitemap, robots.txt)
17. Hardening security: hapus registrasi publik, cek `status` user saat login, rate limit
    forgot-password, fix RCE Generator, fix stored XSS `iframe_map`, `authorize()` cek
    permission asli di semua Form Request (bukan `return true`)
18. Test coverage untuk seluruh modul, ditaruh per-modul di `Modules/{Modul}/tests/Feature/`

### Belum Dibangun
- Modul Product
- Modul Media

### Diketahui Belum Ideal (bukan bug, keputusan yang ditunda)
- `SoftDeletes` belum ada strategi purge otomatis — data yang dihapus (soft) menumpuk terus.
  Rencana: `Illuminate\Database\Eloquent\Prunable` + `php artisan model:prune` terjadwal
  (retensi ~30 hari), belum diimplementasikan.
- `.env.example` masih `APP_DEBUG=true` / `APP_ENV=local` — wajib diganti sebelum produksi.
- Kredensial akun `developer` di `DatabaseSeeder.php` masih hardcode di source.
- `SESSION_SECURE_COOKIE` belum dipaksa `true` di produksi.
- Generator belum dikunci ke environment non-produksi.
- Error page 401 & 503 belum dibuat.

> Detail teknis & konvensi terbaru selalu merujuk ke [CLAUDE.md](../CLAUDE.md) — dokumen ini
> (PRD) fokus ke gambaran besar, bukan referensi harian.

---

## 12. Out of Scope (v1.0)

- Multi-language / i18n
- API / REST endpoint
- Multi-tenancy
- Email notification
- Audit log
