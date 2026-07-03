# Template Modul Baru dengan Relasi (belongsTo)

Template generik untuk membuat modul yang berelasi ke modul lain via `belongsTo`.
Ganti semua `{Modul}`, `{modul}`, `{RelatedModul}`, `{related_moduls}` sesuai nama modul yang dibuat.

Contoh di dokumen ini: **Product → Category** (`product belongsTo Category`). Contoh nyata yang
sudah jalan dengan pola ini: **Team → Category**, **Post → Category** (lihat [MODUL-POST.md](MODUL-POST.md)).

> Generator (`/admin/generator`) **belum mendukung relasi** (`belongsTo`/`foreignId`) — kalau
> modul barumu berelasi ke modul lain, wajib manual pakai panduan ini, tidak bisa lewat Generator.

---

## 1. Generate Scaffold

```bash
php artisan module:make {Modul}
```

---

## 2. Migration

```bash
php artisan module:make-migration create_{moduls}_table {Modul}
```

Edit file di `Modules/{Modul}/database/migrations/`:

```php
Schema::create('{moduls}', function (Blueprint $table) {
    $table->id();
    $table->foreignId('{related_modul}_id')->constrained('{related_moduls}')->nullOnDelete();
    $table->string('name');
    $table->string('slug')->unique();   // hapus jika tidak perlu
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->tinyInteger('status')->default(1); // 1 = aktif, 0 = nonaktif
    $table->timestamps();
    $table->softDeletes();
});
```

```bash
php artisan module:migrate {Modul}
```

> Tambah `->nullable()` pada foreign key jika relasi tidak wajib.

---

## 3. Model

File: `Modules/{Modul}/app/Models/{Modul}.php`

```php
<?php

namespace Modules\{Modul}\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\{RelatedModul}\Models\{RelatedModul};

class {Modul} extends Model
{
    use SoftDeletes;

    protected $fillable = [
        '{related_modul}_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
    ];

    public function {relatedModul}(): BelongsTo
    {
        return $this->belongsTo({RelatedModul}::class);
    }

    // Hapus jika tidak butuh slug
    public static function generateSlug(string $title, ?int $exceptId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (
            static::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }
}
```

---

## 4. Form Requests

### Store{Modul}Request

File: `Modules/{Modul}/app/Http/Requests/Store{Modul}Request.php`

```php
<?php

namespace Modules\{Modul}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store{Modul}Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('{modul}.create') ?? false;
    }

    public function rules(): array
    {
        return [
            '{related_modul}_id' => ['required', 'exists:{related_moduls},id'],
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'image'              => ['nullable', 'image', 'max:2048'],
            'status'             => ['required', 'in:0,1'],
        ];
    }
}
```

### Update{Modul}Request

File: `Modules/{Modul}/app/Http/Requests/Update{Modul}Request.php`

```php
<?php

namespace Modules\{Modul}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Update{Modul}Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('{modul}.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            '{related_modul}_id' => ['required', 'exists:{related_moduls},id'],
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'image'              => ['nullable', 'image', 'max:2048'],
            'status'             => ['required', 'in:0,1'],
        ];
    }
}
```

---

## 5. Service

File: `Modules/{Modul}/app/Services/{Modul}Service.php`

```php
<?php

namespace Modules\{Modul}\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\{Modul}\Models\{Modul};

class {Modul}Service
{
    public function store(array $data, ?UploadedFile $image): {Modul}
    {
        // $data['slug'] = {Modul}::generateSlug($data['name']); // aktifkan jika pakai slug

        if ($image) {
            $data['image'] = $image->store('{moduls}', 'public');
        }

        return {Modul}::create($data);
    }

    public function update({Modul} ${modul}, array $data, ?UploadedFile $image): void
    {
        // $data['slug'] = {Modul}::generateSlug($data['name'], ${modul}->id);

        if ($image) {
            if (${modul}->image) {
                Storage::disk('public')->delete(${modul}->image);
            }
            $data['image'] = $image->store('{moduls}', 'public');
        }

        ${modul}->update($data);
    }

    public function destroy({Modul} ${modul}): void
    {
        if (${modul}->image) {
            Storage::disk('public')->delete(${modul}->image);
        }

        ${modul}->delete();
    }
}
```

---

## 6. Controller

File: `Modules/{Modul}/app/Http/Controllers/Admin/{Modul}Controller.php`

```php
<?php

namespace Modules\{Modul}\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\{RelatedModul}\Models\{RelatedModul};
use Modules\{Modul}\Http\Requests\Store{Modul}Request;
use Modules\{Modul}\Http\Requests\Update{Modul}Request;
use Modules\{Modul}\Models\{Modul};
use Modules\{Modul}\Services\{Modul}Service;

class {Modul}Controller extends Controller implements HasMiddleware
{
    public function __construct(private {Modul}Service $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:{modul}.view', only: ['index']),
            new Middleware('permission:{modul}.create', only: ['create', 'store']),
            new Middleware('permission:{modul}.edit', only: ['edit', 'update']),
            new Middleware('permission:{modul}.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        ${moduls} = {Modul}::with('{relatedModul}')->latest()->paginate(15);

        return view('{modul}::admin.index', compact('{moduls}'));
    }

    public function create()
    {
        ${relatedModuls} = {RelatedModul}::orderBy('name')->get();

        return view('{modul}::admin.create', compact('{relatedModuls}'));
    }

    public function store(Store{Modul}Request $request)
    {
        $this->service->store(
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.{moduls}.index')
            ->with('success', '{Modul} berhasil ditambahkan.');
    }

    public function edit({Modul} ${modul})
    {
        ${relatedModuls} = {RelatedModul}::orderBy('name')->get();

        return view('{modul}::admin.edit', compact('{modul}', '{relatedModuls}'));
    }

    public function update(Update{Modul}Request $request, {Modul} ${modul})
    {
        $this->service->update(
            ${modul},
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.{moduls}.index')
            ->with('success', '{Modul} berhasil diperbarui.');
    }

    public function destroy({Modul} ${modul})
    {
        $this->service->destroy(${modul});

        return redirect()->route('admin.{moduls}.index')
            ->with('success', '{Modul} berhasil dihapus.');
    }
}
```

---

## 7. Routes

File: `Modules/{Modul}/routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\{Modul}\Http\Controllers\Admin\{Modul}Controller;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('{moduls}', {Modul}Controller::class)->except(['show']);
    });
```

---

## 8. Permission — tambah di `database/seeders/DatabaseSeeder.php`

```php
'{modul}.view', '{modul}.create', '{modul}.edit', '{modul}.delete',
```

Insert manual via tinker:

```bash
php artisan tinker --execute '
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
foreach (["{modul}.view","{modul}.create","{modul}.edit","{modul}.delete"] as $p) {
    \Spatie\Permission\Models\Permission::firstOrCreate(["name" => $p, "guard_name" => "web"]);
}
$dev = \Spatie\Permission\Models\Role::where("name","developer")->first();
$dev->syncPermissions(\Spatie\Permission\Models\Permission::all());
echo "Done";
'
```

---

## 9. Views

### index — `Modules/{Modul}/resources/views/admin/index.blade.php`

Kolom tabel: **Nama**, **{RelatedModul}**, **Status**, **Tanggal**, **Aksi**.

- Badge status: `1` (aktif) → hijau, `0` (nonaktif) → abu
- Pagination: `{{ ${moduls}->links() }}`

### create & edit — form fields:

| Field | Input | Catatan |
|---|---|---|
| {RelatedModul} | `<select>` | Required, dari `${relatedModuls}` |
| Nama | `text` | Required |
| Deskripsi | `textarea` | Opsional |
| Gambar | `file` | Image, max 2MB |
| Status | `<select>` | value `1` = Aktif, `0` = Nonaktif |

Form `edit` — tampilkan gambar lama:

```blade
@if ($post->image)
    <img src="{{ Storage::url($post->image) }}" class="h-24 mb-2">
@endif
<input type="file" name="image">
```

---

## 10. Tambah Menu di Admin

Setelah modul jalan, tambah via **Admin → Setting → Menu**:

| Field | Value |
|---|---|
| Label | `{Modul}` |
| Route Name | `admin.{moduls}.index` |
| Active Pattern | `admin.{moduls}.*` |
| Permission | `{modul}.view` |
| Parent | *(sesuai kebutuhan)* |

---

## Catatan Penting

- **Relasi** menggunakan model langsung dari modulnya: `Modules\{RelatedModul}\Models\{RelatedModul}` — jangan duplikasi
- **Status** pakai `tinyInteger`: `1` = aktif/published, `0` = nonaktif/draft
- **Slug** opsional — aktifkan method `generateSlug` di model dan service jika dibutuhkan
- **Image upload** simpan ke `storage/public/{moduls}/` — jalankan `php artisan storage:link` jika belum
- Permission check lewat `HasMiddleware` di controller, **bukan** di route
- **Migration & Request class wajib punya `namespace` yang benar** — kalau lupa, class-nya
  ke-declare di namespace global dan Laravel fatal error "Class not found" saat controller
  resolve `Store{Modul}Request`/`Update{Modul}Request` (baru ketahuan pas form disubmit,
  bukan pas halaman dibuka). Ini pernah kejadian nyata di salah satu modul.
- **Test** ditaruh di `Modules/{Modul}/tests/Feature/`, bukan `tests/Feature/` root — supaya
  modul bisa dihapus/dipakai ulang tanpa meninggalkan file test yang nyangkut.
