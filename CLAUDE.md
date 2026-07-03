# CLAUDE.md — CMS Master

Panduan ini digunakan Claude sebagai referensi konsisten saat membantu development proyek ini.
Selalu baca file ini sebelum membuat atau mengubah kode.

---

## Project Overview

CMS Master adalah template CMS modular berbasis Laravel 13 dengan RBAC.
Detail lengkap ada di [PRD.md](Dokumentasi/PRD.md).

---

## Tech Stack

- **Framework:** Laravel 13, PHP >= 8.2
- **Auth:** Laravel Breeze (standard Laravel structure, BUKAN modul)
- **RBAC:** spatie/laravel-permission
- **Modular:** nwidart/laravel-modules
- **Frontend:** Blade + Livewire + Tailwind CSS
- **Build:** Vite
- **Database:** MySQL 8+

---

## Struktur Folder

```
app/Http/Controllers/Auth/     # Breeze — jangan dipindah ke modul
app/Models/User.php            # Model User — tetap di sini
resources/views/layouts/
  admin.blade.php              # Layout admin panel
  guest.blade.php              # Layout auth (login, register, dll)
  app.blade.php                # Layout frontend

Modules/
  Dashboard/
  Role/
  User/
  Category/                    # Kategori generik, dipakai lintas modul via scopeOfType()
  Team/
  Layanan/
  Keunggulan/
  Menu/                        # Dynamic sidebar menu dari DB
  SiteSetting/                 # Konfigurasi situs (single-row)
  Banner/
  Hero/
  Klien/
  Faq/
  Paket/
  Post/                        # Blog/artikel — admin CRUD + frontend + SEO + rich-editor
  Generator/                   # Generator modul CRUD baru via UI (/admin/generator)
  # Product, Media — belum dibangun
```

### Struktur tiap modul

```
Modules/{Modul}/
  app/
    Http/
      Controllers/
        Admin/                 # Controller admin
        Frontend/              # Controller frontend (jika ada)
      Requests/
        Store{Model}Request.php
        Update{Model}Request.php
    Models/
    Services/                  # Hanya jika ada business logic (relasi/upload/kalkulasi)
    Repositories/              # Hanya jika query kompleks & dipakai banyak tempat
    Providers/{Modul}ServiceProvider.php
  routes/web.php
  database/migrations/
  resources/views/
    admin/
    frontend/
  tests/
    Feature/                   # Test milik modul ini — WAJIB taruh di sini, bukan di root
    Unit/
```

> Test yang spesifik untuk satu modul **wajib** ditaruh di `Modules/{Modul}/tests/Feature/`,
> bukan di `tests/Feature/` root — supaya modul bisa dihapus/dipakai ulang di project lain
> tanpa meninggalkan file test yang nyangkut (lihat bagian **Testing** di bawah).

---

## Aturan Arsitektur

### Controller
- Harus tipis — hanya terima request, panggil service/model, return response
- Jangan taruh business logic di controller
- Selalu gunakan Form Request untuk validasi
- Gunakan 7 method resourceful standar: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

### Service Layer
| Kondisi | Pendekatan |
|---|---|
| CRUD sederhana | Langsung pakai Model di Controller |
| Ada relasi, upload, kalkulasi | Buat `Services/{Model}Service.php` |
| Query kompleks dipakai banyak tempat | Buat `Repositories/{Model}Repository.php` |

### Model
- Relasi antar modul: import Model dari modul lain langsung
  ```php
  use Modules\Category\Models\Category;
  ```
- Selalu definisikan `$fillable`
- Gunakan soft delete jika data tidak boleh hilang permanen

---

## Routing

### Konvensi

```php
// Admin — prefix 'admin', middleware 'auth' saja, name 'admin.'
// Permission check dilakukan di controller via HasMiddleware, BUKAN di route
Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('products', Admin\ProductController::class);
    });

// Frontend
Route::middleware(['auth'])->group(function () {
    Route::resource('products', Frontend\ProductController::class);
});
```

### URL & Named Route

| Area | URL | Named Route |
|---|---|---|
| Admin | `/admin/{resource}` | `admin.{resource}.{action}` |
| Frontend | `/{resource}` | `{resource}.{action}` |
| Auth | `/login`, `/forgot-password`, dll | Breeze default |

> Registrasi publik (`/register`) **sengaja dihapus** — CMS ini bukan aplikasi publik yang
> boleh didaftar siapa saja. Akun baru cuma bisa dibuat lewat modul User oleh admin, atau
> lewat seeder/tinker untuk akun `developer`.

---

## RBAC

### Hirarki Role
```
developer        ← tertinggi, bypass semua permission, TIDAK BISA dihapus
super-admin      ← manage role, permission & user
admin            ← akses penuh admin panel
[role dinamis]   ← dibuat bebas via UI (spv, editor, viewer, dll)
```

### Jenis Role
| Jenis | Contoh | Keterangan |
|---|---|---|
| Protected | `developer` | Di-seed, tidak muncul di UI, tidak bisa dihapus |
| Default | `super-admin`, `admin` | Di-seed, bisa diubah via UI |
| Dinamis | `spv`, `editor`, dll | Dibuat & dikelola via UI oleh super-admin |

### Aturan Wajib untuk Role Developer
- Sembunyikan dari list UI role
- Blokir delete/edit via middleware
- Bypass semua permission via `Gate::before`

```php
Gate::before(function ($user, $ability) {
    if ($user->hasRole('developer')) {
        return true;
    }
});
```

### Format Permission
`{resource}.{action}` — contoh: `product.create`, `user.delete`

4 action standar per modul: `view`, `create`, `edit`, `delete`
Pengecualian modul khusus: `site-setting.view`, `site-setting.edit` (hanya 2)

Permission juga dinamis — bisa ditambah via UI oleh super-admin.

### Penggunaan di Controller (HasMiddleware)
```php
// Gunakan HasMiddleware di controller, BUKAN middleware di route
class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:product.view', only: ['index', 'show']),
            new Middleware('permission:product.create', only: ['create', 'store']),
            new Middleware('permission:product.edit', only: ['edit', 'update']),
            new Middleware('permission:product.delete', only: ['destroy']),
        ];
    }
}
```

### Form Request `authorize()` — WAJIB cek permission, bukan `return true`
`HasMiddleware` di controller adalah satu-satunya penjaga akses kalau `authorize()` selalu
`true` — kalau suatu route baru lupa didaftarkan middleware-nya, request itu langsung
tanpa proteksi sama sekali. Setiap Form Request harus jadi lapisan kedua yang independen:

```php
public function authorize(): bool
{
    return $this->user()?->can('product.create') ?? false;
}
```
Store → cek `.create`, Update → cek `.edit`. Modul yang dibuat lewat Generator
otomatis dapat pola ini (lihat `ModuleGeneratorService::buildRequest()`).

### Seeder Permission
Setiap modul baru wajib tambah permission-nya di `database/seeders/DatabaseSeeder.php`:
```php
'product.view', 'product.create', 'product.edit', 'product.delete',
```

---

## Naming Convention

| Item | Convention | Contoh |
|---|---|---|
| Model | PascalCase singular | `Product` |
| Controller | PascalCase + Controller | `ProductController` |
| Service | PascalCase + Service | `ProductService` |
| Request | Store/Update + Model + Request | `StoreProductRequest` |
| Tabel | snake_case plural | `products` |
| Foreign key | `{model}_id` | `category_id` |
| Pivot table | alphabetical | `category_product` |
| View admin | `admin/{resource}/{action}` | `admin/products/index` |
| View frontend | `frontend/{resource}/{action}` | `frontend/products/index` |

---

## Response Convention

- Setelah `store` / `update` / `destroy` → redirect ke `index`
- Flash message key: `success` atau `error`

```php
return redirect()->route('admin.products.index')
    ->with('success', 'Produk berhasil disimpan.');
```

---

## Modul Khusus

### Menu (Dynamic Sidebar)
- Data menu disimpan di tabel `menus` — bisa dikelola via admin UI
- Mendukung parent-child (1 level): parent = dropdown, child = link
- Field `permission` di model `Menu` — menu hanya tampil jika user punya permission tersebut
- `canSee()` di-filter di `MenuServiceProvider` sebelum dikirim ke view
- Drag-and-drop: reorder dalam level + pindah antar level (cross-level)
- `$sidebarMenus` di-share ke `layouts.admin` via `View::composer` di `MenuServiceProvider`

### SiteSetting (Single-Row Config)
- Tabel `site_settings` selalu berisi tepat **satu baris**
- Akses via `SiteSetting::current()` — auto-create jika belum ada
- Field: `app_name`, `deskripsi`, `alamat`, `no_telp`, `no_whatsapp`, `email`, `logo_atas`, `logo_bawah`, `icon`, `og_image`, `iframe_map`
- Field sosmed (nama + link per platform): `instagram`, `facebook`, `tiktok`, `youtube`, `x`
- Field marketplace (nama + link per platform): `shopee`, `tokopedia`, `blibli`
- `$siteSetting` di-share ke **semua view** via `View::share()` di `SiteSettingServiceProvider`
- Gambar disimpan di `storage/public/site-settings/`
- Permission: `site-setting.view`, `site-setting.edit` (hanya 2, tidak ada create/delete)
- Route: hanya `GET index` dan `PUT update` — tidak ada create/store/destroy
- Field `iframe_map` **hanya menerima URL** embed Google Maps (`starts_with:https://www.google.com/maps/embed`),
  BUKAN kode `<iframe>` mentah — tag-nya dibangun di server (`kontak.blade.php`), bukan `{!! !!}`
  dari input user (celah XSS yang sudah pernah terjadi & ditutup).

### Post (Blog/Artikel)
- Field `content` pakai komponen `<x-admin.rich-editor>` (TinyMCE via CDN jsdelivr, tanpa API key —
  `license_key: 'gpl'`). Toolbar dibatasi: heading H2–H4 saja (H1 dipakai judul post, jangan duplikat).
- **Wajib** disanitasi pakai `mews/purifier` (`Purifier::clean()`) di `PostService::store()`/`update()`
  sebelum disimpan — HTML dari editor tetap bisa disusupi `<script>` kalau tidak dibersihkan.
  `config/purifier.php` → `HTML.Allowed` sudah ditambah `h2,h3,h4,blockquote` supaya format dari
  toolbar tidak ikut hilang saat disanitasi.
- Frontend: `/posts` (index, published only, paginate) dan `/posts/{post:slug}` (show, 404 kalau draft).
- Pakai `SoftDeletes` seperti modul konten lain.

### Generator (Module Builder)
- `/admin/generator` — bikin modul CRUD baru (Model, Controller, Request, View, migration, route, seeder
  permission) lewat form UI, tanpa nulis kode manual.
- **Hanya untuk role `developer`** (`generator.view`, `generator.create`).
- Field `label` di form **wajib** divalidasi ketat (`regex:/^[\p{L}\p{N} .,()\-]+$/u`) dan di-`e()`-escape
  sebelum ditulis ke file view yang di-generate — kalau tidak, karakter `{`/`}`/`<` di label bisa jadi
  Blade/PHP yang benar-benar dieksekusi saat view itu dirender (RCE, bukan sekadar XSS). **Jangan pernah
  longgarkan validasi ini.**
- Belum dikunci ke environment non-produksi — sebaiknya jangan dijalankan di server live.

### Error Pages (`resources/views/errors/`)
- 404, 403, 419, 429 — pakai `<x-navbar>`/`<x-footer>`, gaya visual sama dengan frontend publik,
  plus `noindex,nofollow` dan `<x-seo-meta>`.
- 500 — **sengaja tidak** pakai `$siteSetting`/Tailwind build/komponen apa pun (full inline CSS,
  hardcode `config('app.name')`) supaya tidak ikut collapse kalau penyebab 500-nya sendiri masalah
  database/asset.
- 401, 503 belum dibuat.

### SEO
- `<x-seo-meta>` (`resources/views/components/seo-meta.blade.php`) — title, meta description, canonical,
  Open Graph, Twitter Card. Fallback ke `SiteSetting::app_name/deskripsi/og_image` kalau halaman tidak
  kasih nilai spesifik. Pasang di `<head>` tiap halaman frontend/error.
- `/sitemap.xml` (route `sitemap`, `routes/web.php`) — pakai `spatie/laravel-sitemap`, generate dinamis
  (bukan file statis): homepage, kontak, layanan, semua post published.
- `/robots.txt` — dinamis juga (bukan file `public/robots.txt`), block `/admin`, `/login`, dll, arahkan
  ke sitemap.
- Post detail pakai JSON-LD `BlogPosting`.

---

## Urutan Development

### Sudah Dibangun
1. Setup project (Laravel 13 + Breeze + Spatie + nwidart)
2. Layout admin & frontend
3. Modul Dashboard
4. Modul Role
5. Modul User
6. Modul Category
7. Modul Team
8. Modul Layanan
9. Modul Keunggulan
10. Modul Menu
11. Modul SiteSetting
12. Modul Banner
13. Modul Hero
14. Modul Klien
15. Modul Faq
16. Modul Paket
17. Modul Post (admin CRUD + frontend + SEO + rich-editor + sanitasi)
18. Modul Generator (builder modul CRUD via UI)
19. Halaman error kustom (404/403/419/429/500)
20. SEO (meta tag, sitemap, robots.txt)
21. Hardening security: hapus registrasi publik, cek `status` user saat login,
    rate limit forgot-password, fix RCE Generator, fix stored XSS `iframe_map`,
    `authorize()` cek permission asli di semua Form Request

### Belum Dibangun
- Modul Product
- Modul Media

### Diketahui belum ideal (bukan bug, keputusan yang ditunda)
- Retensi data `SoftDeletes` belum ada strategi purge otomatis (data soft-deleted menumpuk
  selamanya) — solusi yang direkomendasikan: `Illuminate\Database\Eloquent\Prunable` +
  `php artisan model:prune` terjadwal, retensi ~30 hari. Belum diimplementasikan.
- `.env.example` masih `APP_DEBUG=true` / `APP_ENV=local` — wajib diganti sebelum produksi.
- Kredensial akun `developer` di `DatabaseSeeder.php` masih hardcode di source (bukan env var).
- `SESSION_SECURE_COOKIE` belum dipaksa `true` — perlu diset manual di `.env` produksi.
- Generator belum dikunci ke non-produksi (lihat bagian Generator di atas).

---

## Testing

- Test yang menguji satu modul spesifik **wajib** ditaruh di `Modules/{Modul}/tests/Feature/`
  (bukan `tests/Feature/` root) — mengikuti konvensi resmi `nwidart/laravel-modules`
  (`php artisan module:make-test`). Tujuannya: modul bisa dihapus/dipakai ulang di project lain
  tanpa meninggalkan file test yang nyangkut dan bikin seluruh suite crash.
- Test lintas-modul/tidak dimiliki satu modul (layout admin, navbar/footer root, SEO cross-cutting,
  Auth, Profile) tetap di `tests/Feature/` root.
- `phpunit.xml` dan `tests/Pest.php` sudah di-set untuk men-scan kedua lokasi (glob pattern
  `Modules/*/tests/Feature`) — kalau bikin modul baru, testnya otomatis ke-detect, tidak perlu
  daftar manual lagi.
- Helper function di test (`xxxManager()`, dsb) dideklarasikan `function` biasa di scope global
  Pest — pastikan nama unik lintas file, tidak boleh ada duplikat.
- Jalankan `php artisan test --compact --filter=NamaTest` setelah tiap perubahan, bukan cuma
  test file yang baru diedit — banyak bug tersembunyi (mis. namespace hilang, migration nyasar)
  baru ketahuan justru saat menulis test untuk modul yang belum pernah dites.

---

## Yang TIDAK Boleh Dilakukan

- Jangan taruh Auth di dalam Modules/
- Jangan taruh business logic di Controller atau Model
- Jangan buat migration di `database/migrations/` untuk modul — taruh di `Modules/{Modul}/Database/Migrations/`
- Jangan duplikasi Model antar modul — import langsung dari modulnya
- Jangan buat helper function global jika bisa dijadikan method di Service atau Model
- Jangan skip Form Request untuk validasi
- Jangan `authorize()` selalu `return true` di Form Request — cek permission asli, jangan
  cuma andalkan middleware controller (lihat bagian RBAC)
- Jangan render field richtext/HTML dari input user dengan `{!! !!}` tanpa disanitasi
  (`Purifier::clean()`) dulu saat disimpan

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== volt/core rules ===

# Livewire Volt

- Single-file Livewire components: PHP logic and Blade templates in one file.
- Always check existing Volt components to determine functional vs class-based style.
- IMPORTANT: Always use `search-docs` tool for version-specific Volt documentation and updated code examples.
- IMPORTANT: Activate `volt-development` every time you're working with a Volt or single-file component-related task.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
