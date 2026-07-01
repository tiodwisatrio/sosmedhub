# Panduan Membuat Modul CRUD Baru

Panduan ini menggunakan contoh modul **`Portofolio`** (nama resource: `portofolios`).
Ganti semua kemunculan `Portofolio` / `portofolio` / `portofolios` dengan nama modul kamu.

---

## 1. Generate Scaffold Modul

```bash
php artisan module:make Portofolio
```

Ini membuat folder `Modules/Portofolio/` beserta struktur defaultnya.

---

## 2. Migration

```bash
php artisan module:make-migration create_portofolios_table Portofolio
```

Edit file yang terbentuk di `Modules/Portofolio/database/migrations/`:

```php
Schema::create('portofolios', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->tinyInteger('status')->default(1);
    $table->timestamps();
    $table->softDeletes();
});
```

Jalankan migration:

```bash
php artisan module:migrate Portofolio
```

---

## 3. Model

File: `Modules/Portofolio/app/Models/Portofolio.php`

```php
<?php

namespace Modules\Portofolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portofolio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
```

---

## 4. Form Requests

### StorePortofolioRequest

File: `Modules/Portofolio/app/Http/Requests/StorePortofolioRequest.php`

```php
<?php

namespace Modules\Portofolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortofolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'status'      => ['required', 'in:0,1'],
        ];
    }
}
```

### UpdatePortofolioRequest

File: `Modules/Portofolio/app/Http/Requests/UpdatePortofolioRequest.php`

```php
<?php

namespace Modules\Portofolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortofolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'status'      => ['required', 'in:0,1'],
        ];
    }
}
```

---

## 5. Service

File: `Modules/Portofolio/app/Services/PortofolioService.php`

> Gunakan Service jika ada upload file, kalkulasi, atau logic selain CRUD murni.
> Jika benar-benar hanya insert/update sederhana, boleh langsung dari Controller.

```php
<?php

namespace Modules\Portofolio\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Portofolio\Models\Portofolio;

class PortofolioService
{
    public function store(array $data, ?UploadedFile $image): Portofolio
    {
        if ($image) {
            $data['image'] = $image->store('portofolios', 'public');
        }

        return Portofolio::create($data);
    }

    public function update(Portofolio $portofolio, array $data, ?UploadedFile $image): void
    {
        if ($image) {
            if ($portofolio->image) {
                Storage::disk('public')->delete($portofolio->image);
            }
            $data['image'] = $image->store('portofolios', 'public');
        }

        $portofolio->update($data);
    }

    public function destroy(Portofolio $portofolio): void
    {
        if ($portofolio->image) {
            Storage::disk('public')->delete($portofolio->image);
        }

        $portofolio->delete();
    }
}
```

---

## 6. Controller

Buat folder `Admin/` terlebih dahulu, lalu buat file:

File: `Modules/Portofolio/app/Http/Controllers/Admin/PortofolioController.php`

```php
<?php

namespace Modules\Portofolio\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Portofolio\Http\Requests\StorePortofolioRequest;
use Modules\Portofolio\Http\Requests\UpdatePortofolioRequest;
use Modules\Portofolio\Models\Portofolio;
use Modules\Portofolio\Services\PortofolioService;

class PortofolioController extends Controller implements HasMiddleware
{
    public function __construct(private PortofolioService $service) {}

    /**
     * Permission per action. Middleware ini yang memproteksi route —
     * JANGAN taruh permission di routes/web.php (lihat catatan di section Routes).
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:portofolio.view', only: ['index', 'show']),
            new Middleware('permission:portofolio.create', only: ['create', 'store']),
            new Middleware('permission:portofolio.edit', only: ['edit', 'update']),
            new Middleware('permission:portofolio.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $portofolios = Portofolio::when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('portofolio::admin.index', compact('portofolios'));
    }

    public function create()
    {
        return view('portofolio::admin.create');
    }

    public function store(StorePortofolioRequest $request)
    {
        $data = $request->safe()->except('image');
        $this->service->store($data, $request->file('image'));

        return redirect()->route('admin.portofolios.index')
            ->with('success', 'Portofolio berhasil ditambahkan.');
    }

    public function edit(Portofolio $portofolio)
    {
        return view('portofolio::admin.edit', compact('portofolio'));
    }

    public function update(UpdatePortofolioRequest $request, Portofolio $portofolio)
    {
        $data = $request->safe()->except('image');
        $this->service->update($portofolio, $data, $request->file('image'));

        return redirect()->route('admin.portofolios.index')
            ->with('success', 'Portofolio berhasil diperbarui.');
    }

    public function destroy(Portofolio $portofolio)
    {
        $this->service->destroy($portofolio);

        return redirect()->route('admin.portofolios.index')
            ->with('success', 'Portofolio berhasil dihapus.');
    }
}
```

---

## 7. Routes

File: `Modules/Portofolio/routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Portofolio\Http\Controllers\Admin\PortofolioController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('portofolios', PortofolioController::class);
    });
```

> ⚠️ **JANGAN** pakai pola `->middleware(['index' => 'permission:...', 'store' => '...'])`
> pada `Route::resource()`. Itu **bukan fitur Laravel** — key (`index`, `store`, dst)
> diabaikan dan **semua** permission diterapkan ke **setiap** route, sehingga user yang
> hanya punya `.view` tetap kena 403. Permission per-action diatur lewat `HasMiddleware`
> di controller (section 6).

---

## 8. Views

### index — `Modules/Portofolio/resources/views/admin/index.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Portofolio')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Portofolio</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <x-admin.search :action="route('admin.portofolios.index')" placeholder="Cari nama portofolio..." />
        @can('portofolio.create')
            <a href="{{ route('admin.portofolios.create') }}">
                <x-admin.button>+ Tambah Portofolio</x-admin.button>
            </a>
        @endcan
    </div>

    <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-slate-50">
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">#</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama</th>
                    <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($portofolios as $portofolio)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3 text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $portofolio->name }}</td>
                        <td class="px-6 py-3">
                            @if ($portofolio->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('portofolio.edit')
                                    <a href="{{ route('admin.portofolios.edit', $portofolio) }}">
                                        <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                    </a>
                                @endcan
                                @can('portofolio.delete')
                                    <form method="POST" action="{{ route('admin.portofolios.destroy', $portofolio) }}"
                                        onsubmit="return confirm('Hapus portofolio ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                    </form>
                                @endcan
                                @cannot('portofolio.edit')
                                    @cannot('portofolio.delete')
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data portofolio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($portofolios->hasPages())
            <div class="px-6 py-4 border-t border-border">
                {{ $portofolios->links() }}
            </div>
        @endif
    </div>
@endsection
```

### create — `Modules/Portofolio/resources/views/admin/create.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Tambah Portofolio')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Portofolio</h1>
@endsection

@section('content')
    <div class="bg-card rounded-xl shadow-card border border-border p-6">
        <form method="POST" action="{{ route('admin.portofolios.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-danger">*</span></label>
                <x-admin.input-text name="name" :value="old('name')" placeholder="Nama portofolio" />
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description') }}</x-admin.textarea>
                @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-admin.file-upload name="image" label="Gambar" hint="JPG, PNG, WEBP maks. 2MB" accept="image/*" :preview="true" />
                @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select
                    name="status"
                    :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                    :selected="old('status', '1')"
                />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-admin.button type="submit">Simpan</x-admin.button>
                <a href="{{ route('admin.portofolios.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>
@endsection
```

### edit — `Modules/Portofolio/resources/views/admin/edit.blade.php`

```blade
@extends('layouts.admin')

@section('title', 'Edit Portofolio')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Portofolio</h1>
@endsection

@section('content')
    <div class="bg-card rounded-xl shadow-card border border-border p-6">
        <form method="POST" action="{{ route('admin.portofolios.update', $portofolio) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-danger">*</span></label>
                <x-admin.input-text name="name" :value="old('name', $portofolio->name)" placeholder="Nama portofolio" />
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <x-admin.textarea name="description" placeholder="Deskripsi singkat...">{{ old('description', $portofolio->description) }}</x-admin.textarea>
                @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-admin.file-upload
                    name="image"
                    label="Gambar"
                    hint="JPG, PNG, WEBP maks. 2MB. Kosongkan jika tidak ingin mengganti."
                    accept="image/*"
                    :preview="true"
                    :existing="$portofolio->image ? Storage::url($portofolio->image) : null"
                />
                @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select
                    name="status"
                    :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                    :selected="old('status', $portofolio->status)"
                />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-admin.button type="submit">Perbarui</x-admin.button>
                <a href="{{ route('admin.portofolios.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>
@endsection
```

---

## 9. Permission Seeder

Buka `database/seeders/DatabaseSeeder.php`, tambahkan permission modul baru ke array `$permissions` yang sudah ada:

```php
$permissions = [
    'category.view', 'category.create', 'category.edit', 'category.delete',
    // ... permission lain ...

    // tambah baris ini:
    'portofolio.view', 'portofolio.create', 'portofolio.edit', 'portofolio.delete',
];
```

Jalankan seeder:

```bash
php artisan db:seed
```

---

## 10. Tambah ke Sidebar Menu

Sidebar membaca menu dari database. Jalankan perintah berikut via tinker:

```bash
php artisan tinker --execute '
\DB::table("menus")->insert([
    "label"          => "Portofolio",
    "route_name"     => "admin.portofolios.index",
    "active_pattern" => "admin.portofolios.*",
    "permission"     => "portofolio.view",
    "icon"           => "briefcase",
    "urutan"         => 10,
    "is_active"      => 1,
    "created_at"     => now(),
    "updated_at"     => now(),
]);
'
```

### Cara pilih icon

Kolom `icon` diisi nama icon dari **[Heroicons](https://heroicons.com)** (style Outline).

1. Buka heroicons.com
2. Cari icon yang sesuai
3. Salin namanya — contoh: `home`, `user-group`, `briefcase`, `star`, `cog-6-tooth`

Beberapa contoh nama yang umum dipakai:

| Nama | Kegunaan |
|---|---|
| `home` | Dashboard |
| `user-group` | Tim / Pengguna |
| `user` | Profil |
| `briefcase` | Layanan / Produk |
| `star` | Unggulan / Favorit |
| `tag` | Kategori |
| `bars-3` | Menu |
| `shield-check` | Role & Akses |
| `cog-6-tooth` | Pengaturan |
| `document-text` | Artikel / Post |
| `photo` | Media / Galeri |

> Setelah modul berjalan, kamu bisa kelola urutan & icon menu langsung dari halaman **Menu** di admin panel — tidak perlu tinker lagi.

---

## 11. Verifikasi

```bash
# Cek route terdaftar
php artisan route:list --name=admin.portofolios

# Rebuild CSS jika ada class Tailwind baru
npm run build
```

---

## Checklist

- [ ] `module:make` — scaffold modul
- [ ] Migration dibuat & dijalankan
- [ ] Model dengan `$fillable` dan `$casts`
- [ ] `StoreRequest` dan `UpdateRequest`
- [ ] `Service` (jika ada upload / logic)
- [ ] `Admin/Controller` `implements HasMiddleware` + method `middleware()` (permission per-action)
- [ ] `routes/web.php` cukup `Route::resource(...)` — TANPA keyed middleware
- [ ] View: `admin/index.blade.php` — tombol Tambah/Edit/Hapus dibungkus `@can`
- [ ] View: `admin/create.blade.php`
- [ ] View: `admin/edit.blade.php`
- [ ] Permission ditambahkan ke seeder & dijalankan
- [ ] Menu sidebar ditambahkan ke tabel `menus`

---

## Konvensi Penting

| Item | Convention | Contoh |
|---|---|---|
| Nama modul | PascalCase | `Portofolio` |
| Resource URL | kebab-case plural | `portofolios` |
| Named route | `admin.{resource}.{action}` | `admin.portofolios.index` |
| View namespace | `{modul-lowercase}::admin.{action}` | `portofolio::admin.index` |
| Permission | `{resource}.{action}` | `portofolio.view` |
| Migration | di dalam `Modules/{Modul}/database/migrations/` | ← jangan di `database/migrations/` |
