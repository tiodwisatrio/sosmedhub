# Dokumentasi Modul Post

Modul artikel/blog dengan relasi ke `Category`.

---

## 1. Generate Scaffold

```bash
php artisan module:make Post
```

---

## 2. Migration

```bash
php artisan module:make-migration create_posts_table Post
```

Edit file di `Modules/Post/database/migrations/`:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->nullOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content')->nullable();
    $table->string('image')->nullable();
    $table->tinyInteger('status')->default(1); // 1 = published, 0 = draft
    $table->string('author')->nullable();
    $table->timestamps();
});
```

```bash
php artisan module:migrate Post
```

---

## 3. Model

File: `Modules/Post/app/Models/Post.php`

```php
<?php

namespace Modules\Post\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;

class Post extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'image',
        'status',
        'author',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 1;
    }

    // Auto-generate slug unik dari title
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

### StorePostRequest

File: `Modules/Post/app/Http/Requests/StorePostRequest.php`

```php
<?php

namespace Modules\Post\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'status'       => ['required', 'in:0,1'],
            'author'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

### UpdatePostRequest

File: `Modules/Post/app/Http/Requests/UpdatePostRequest.php`

```php
<?php

namespace Modules\Post\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'status'       => ['required', 'in:0,1'],
            'author'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

---

## 5. Service

File: `Modules/Post/app/Services/PostService.php`

```php
<?php

namespace Modules\Post\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Post\Models\Post;

class PostService
{
    public function store(array $data, ?UploadedFile $image): Post
    {
        $data['slug'] = Post::generateSlug($data['title']);

        if ($image) {
            $data['image'] = $image->store('posts', 'public');
        }

        return Post::create($data);
    }

    public function update(Post $post, array $data, ?UploadedFile $image): void
    {
        $data['slug'] = Post::generateSlug($data['title'], $post->id);

        if ($image) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $image->store('posts', 'public');
        }

        $post->update($data);
    }

    public function destroy(Post $post): void
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();
    }
}
```

---

## 6. Controller

File: `Modules/Post/app/Http/Controllers/Admin/PostController.php`

```php
<?php

namespace Modules\Post\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Category\Models\Category;
use Modules\Post\Http\Requests\StorePostRequest;
use Modules\Post\Http\Requests\UpdatePostRequest;
use Modules\Post\Models\Post;
use Modules\Post\Services\PostService;

class PostController extends Controller implements HasMiddleware
{
    public function __construct(private PostService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:post.view', only: ['index']),
            new Middleware('permission:post.create', only: ['create', 'store']),
            new Middleware('permission:post.edit', only: ['edit', 'update']),
            new Middleware('permission:post.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $posts = Post::with('category')->latest()->paginate(15);

        return view('post::admin.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::ofType('post')->orderBy('name')->get();

        return view('post::admin.create', compact('categories'));
    }

    public function store(StorePostRequest $request)
    {
        $this->service->store(
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil ditambahkan.');
    }

    public function edit(Post $post)
    {
        $categories = Category::ofType('post')->orderBy('name')->get();

        return view('post::admin.edit', compact('post', 'categories'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->service->update(
            $post,
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        $this->service->destroy($post);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }
}
```

---

## 7. Routes

File: `Modules/Post/routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Post\Http\Controllers\Admin\PostController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('posts', PostController::class)->except(['show']);
    });
```

---

## 8. Permission — tambah di `database/seeders/DatabaseSeeder.php`

```php
'post.view', 'post.create', 'post.edit', 'post.delete',
```

Insert manual via tinker (jika tidak mau re-seed):

```bash
php artisan tinker --execute '
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
foreach (["post.view","post.create","post.edit","post.delete"] as $p) {
    \Spatie\Permission\Models\Permission::firstOrCreate(["name" => $p, "guard_name" => "web"]);
}
$dev = \Spatie\Permission\Models\Role::where("name","developer")->first();
$dev->syncPermissions(\Spatie\Permission\Models\Permission::all());
echo "Done";
'
```

---

## 9. Views

### index — `Modules/Post/resources/views/admin/index.blade.php`

Kolom tabel: **Judul**, **Kategori**, **Penulis**, **Status**, **Tanggal**, **Aksi**.

- Badge status: `1` (published) → hijau, `0` (draft) → abu
- Pagination: `{{ $posts->links() }}`

### create & edit — form fields:

| Field | Input | Catatan |
|---|---|---|
| Kategori | `<select>` | Required, dari `$categories` |
| Judul | `text` | Required, slug di-generate otomatis |
| Konten | `textarea` | Opsional |
| Gambar | `file` | Image, max 2MB |
| Penulis | `text` | Opsional |
| Status | `<select>` | value `1` = Published, `0` = Draft |

Form `edit` perlu tampilkan gambar lama jika ada:

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
| Label | `Post` |
| Route Name | `admin.posts.index` |
| Active Pattern | `admin.posts.*` |
| Permission | `post.view` |
| Parent | *(sesuai kebutuhan)* |

---

## Catatan Penting

- **Slug** di-generate otomatis dari `title` di `PostService` — tidak perlu input slug di form
- **Status** pakai `tinyInteger`: `1` = published, `0` = draft
- **category_id** wajib diisi (`required`) — gunakan `nullOnDelete` di migration agar record tidak ikut terhapus jika kategori dihapus
- **Relasi Category** dari `Modules\Category\Models\Category` — jangan duplikasi model
- **Filter kategori wajib** di `create()` dan `edit()` gunakan `Category::ofType('post')` — jangan `Category::all()` atau `Category::orderBy()` saja, karena tabel `categories` dipakai bersama oleh beberapa modul (post, team, dll) dengan tipe berbeda
- **Textarea `x-admin.textarea`** harus diisi via slot, bukan prop `value`:
  ```blade
  {{-- Benar — konten jadi slot --}}
  <x-admin.textarea name="content">{{ old('content', $post->content) }}</x-admin.textarea>

  {{-- Salah — value prop tidak dirender di textarea --}}
  <x-admin.textarea name="content" :value="old('content', $post->content)" />
  ```
