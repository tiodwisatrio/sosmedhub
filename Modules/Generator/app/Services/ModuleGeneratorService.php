<?php

namespace Modules\Generator\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Process\Process;

class ModuleGeneratorService
{
    /** @var array<int, array{name: string, output: string, ok: bool}> */
    private array $steps = [];

    /**
     * Generate a complete CRUD module from the given blueprint.
     *
     * @param  array{name: string, fields: array, has_status?: bool, has_urutan?: bool, create_menu?: bool, menu_icon?: ?string, menu_parent_id?: ?int}  $input
     * @return array{module: string, table: string, route: string, permission: string, steps: array}
     */
    public function generate(array $input): array
    {
        $module = Str::studly($input['name']);
        $table = Str::plural(Str::snake($module));
        $route = Str::plural(Str::kebab($module));
        $permission = Str::kebab($module);
        $varSingular = Str::camel($module);
        $varPlural = Str::camel(Str::plural($module));
        $viewNs = Str::lower($module);

        if (File::exists(base_path("Modules/{$module}"))) {
            throw new RuntimeException("Modul {$module} sudah ada. Hapus dulu atau gunakan nama lain.");
        }

        $fields = $this->normalizeFields($input['fields']);
        $imageFields = array_values(array_filter($fields, fn ($f) => $f['type'] === 'image'));
        $hasImage = count($imageFields) > 0;
        $hasStatus = (bool) ($input['has_status'] ?? false);
        $hasUrutan = (bool) ($input['has_urutan'] ?? false);

        $ctx = compact(
            'module', 'table', 'route', 'permission', 'varSingular', 'varPlural',
            'viewNs', 'fields', 'imageFields', 'hasImage', 'hasStatus', 'hasUrutan'
        );

        // 1. Scaffold via nwidart
        $this->runProcess('Scaffold modul', [$this->phpBin(), base_path('artisan'), 'module:make', $module, '--no-interaction']);

        // 2. Tulis semua file (filesystem only — aman in-process)
        $this->writeFiles($ctx);
        $this->steps[] = ['name' => 'Tulis file modul', 'output' => 'Model, Request, Controller, Routes, Views berhasil ditulis.', 'ok' => true];

        // 2.5 Rapikan format PHP (non-fatal)
        $this->runProcessSoft('Format kode (pint)', [$this->phpBin(), base_path('vendor/bin/pint'), "Modules/{$module}", '--format', 'agent']);

        // 3. Dump autoload supaya class modul baru terdaftar
        $this->runProcess('composer dump-autoload', [...$this->composerCommand(), 'dump-autoload', '-o', '--no-interaction'], 300);

        // 4. Migrate (proses terpisah — autoloader baru ke-load)
        $this->runProcess('Migrasi tabel', [$this->phpBin(), base_path('artisan'), 'module:migrate', $module, '--no-interaction']);

        // 5. Permission
        $this->createPermissions($permission);
        $this->steps[] = ['name' => 'Permission', 'output' => "Dibuat: {$permission}.view, .create, .edit, .delete + sync ke role developer.", 'ok' => true];

        // 5.5 Catat permission ke DatabaseSeeder.php supaya tidak hilang saat migrate:fresh --seed
        $syncedToSeeder = $this->syncPermissionsToSeeder($permission);
        $this->steps[] = [
            'name' => 'Sinkron ke seeder',
            'output' => $syncedToSeeder
                ? 'Permission ditambahkan ke database/seeders/DatabaseSeeder.php.'
                : 'Permission sudah ada di database/seeders/DatabaseSeeder.php, dilewati.',
            'ok' => true,
        ];

        // 6. Menu sidebar
        if ($input['create_menu'] ?? false) {
            $this->createMenu($input, $route, $permission);
            $this->steps[] = ['name' => 'Menu sidebar', 'output' => 'Entri menu berhasil dibuat.', 'ok' => true];
        }

        return [
            'module' => $module,
            'table' => $table,
            'route' => $route,
            'permission' => $permission,
            'steps' => $this->steps,
        ];
    }

    /**
     * @param  array<int, array>  $fields
     * @return array<int, array{name: string, label: string, type: string, nullable: bool}>
     */
    private function normalizeFields(array $fields): array
    {
        return array_values(array_map(fn ($f) => [
            'name' => Str::snake(trim($f['name'])),
            'label' => e(trim($f['label'])),
            'type' => $f['type'],
            'nullable' => (bool) ($f['nullable'] ?? false),
        ], $fields));
    }

    private function writeFiles(array $ctx): void
    {
        $base = base_path("Modules/{$ctx['module']}");

        // Buang controller default bawaan scaffold
        File::delete("{$base}/app/Http/Controllers/{$ctx['module']}Controller.php");

        File::ensureDirectoryExists("{$base}/app/Http/Controllers/Admin");
        File::ensureDirectoryExists("{$base}/app/Http/Requests");
        File::ensureDirectoryExists("{$base}/app/Models");
        File::ensureDirectoryExists("{$base}/resources/views/admin");

        $timestamp = date('Y_m_d_His');
        File::put("{$base}/database/migrations/{$timestamp}_create_{$ctx['table']}_table.php", $this->buildMigration($ctx));
        File::put("{$base}/app/Models/{$ctx['module']}.php", $this->buildModel($ctx));
        File::put("{$base}/app/Http/Requests/Store{$ctx['module']}Request.php", $this->buildRequest($ctx, 'Store'));
        File::put("{$base}/app/Http/Requests/Update{$ctx['module']}Request.php", $this->buildRequest($ctx, 'Update'));

        if ($ctx['hasImage']) {
            File::ensureDirectoryExists("{$base}/app/Services");
            File::put("{$base}/app/Services/{$ctx['module']}Service.php", $this->buildService($ctx));
        }

        File::put("{$base}/app/Http/Controllers/Admin/{$ctx['module']}Controller.php", $this->buildController($ctx));
        File::put("{$base}/routes/web.php", $this->buildRoutes($ctx));
        File::put("{$base}/routes/api.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Modul {$ctx['module']} tidak menyediakan endpoint API.\n");
        File::put("{$base}/resources/views/admin/index.blade.php", $this->buildIndexView($ctx));
        File::put("{$base}/resources/views/admin/create.blade.php", $this->buildFormView($ctx, 'create'));
        File::put("{$base}/resources/views/admin/edit.blade.php", $this->buildFormView($ctx, 'edit'));
    }

    private function buildMigration(array $ctx): string
    {
        $lines = [];
        foreach ($ctx['fields'] as $f) {
            $lines[] = '            '.$this->migrationColumn($f);
        }
        if ($ctx['hasUrutan']) {
            $lines[] = "            \$table->unsignedInteger('urutan')->default(0);";
        }
        if ($ctx['hasStatus']) {
            $lines[] = "            \$table->tinyInteger('status')->default(1);";
        }
        $columns = implode("\n", $lines);

        return <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('{$ctx['table']}', function (Blueprint \$table) {
                    \$table->id();
        {$columns}
                    \$table->timestamps();
                    \$table->softDeletes();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('{$ctx['table']}');
            }
        };

        PHP;
    }

    private function migrationColumn(array $f): string
    {
        $n = $f['name'];
        $nullable = $f['nullable'] ? '->nullable()' : '';

        return match ($f['type']) {
            'text', 'richtext' => "\$table->text('{$n}')->nullable();",
            'integer' => "\$table->integer('{$n}'){$nullable};",
            'date' => "\$table->date('{$n}'){$nullable};",
            'boolean' => "\$table->boolean('{$n}')->default(false);",
            'image' => "\$table->string('{$n}')->nullable();",
            default => "\$table->string('{$n}'){$nullable};",
        };
    }

    private function buildModel(array $ctx): string
    {
        $fillable = [];
        foreach ($ctx['fields'] as $f) {
            $fillable[] = "        '{$f['name']}',";
        }
        if ($ctx['hasUrutan']) {
            $fillable[] = "        'urutan',";
        }
        if ($ctx['hasStatus']) {
            $fillable[] = "        'status',";
        }
        $fillableStr = implode("\n", $fillable);

        $casts = [];
        foreach ($ctx['fields'] as $f) {
            $cast = match ($f['type']) {
                'integer' => 'integer',
                'boolean' => 'boolean',
                'date' => 'date',
                default => null,
            };
            if ($cast) {
                $casts[] = "        '{$f['name']}' => '{$cast}',";
            }
        }
        if ($ctx['hasUrutan']) {
            $casts[] = "        'urutan' => 'integer',";
        }
        if ($ctx['hasStatus']) {
            $casts[] = "        'status' => 'integer',";
        }

        $castsBlock = '';
        if (! empty($casts)) {
            $castsStr = implode("\n", $casts);
            $castsBlock = "\n\n    protected \$casts = [\n{$castsStr}\n    ];";
        }

        return <<<PHP
        <?php

        namespace Modules\\{$ctx['module']}\\Models;

        use Illuminate\Database\Eloquent\Model;
        use Illuminate\Database\Eloquent\SoftDeletes;

        class {$ctx['module']} extends Model
        {
            use SoftDeletes;

            protected \$fillable = [
        {$fillableStr}
            ];{$castsBlock}
        }

        PHP;
    }

    private function buildRequest(array $ctx, string $type): string
    {
        $rules = [];
        foreach ($ctx['fields'] as $f) {
            $rules[] = "            '{$f['name']}' => [".$this->validationRule($f).'],';
        }
        if ($ctx['hasUrutan']) {
            $rules[] = "            'urutan' => ['nullable', 'integer', 'min:0'],";
        }
        if ($ctx['hasStatus']) {
            $rules[] = "            'status' => ['required', 'in:0,1'],";
        }
        $rulesStr = implode("\n", $rules);

        return <<<PHP
        <?php

        namespace Modules\\{$ctx['module']}\\Http\\Requests;

        use Illuminate\Foundation\Http\FormRequest;

        class {$type}{$ctx['module']}Request extends FormRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [
        {$rulesStr}
                ];
            }
        }

        PHP;
    }

    private function validationRule(array $f): string
    {
        $req = $f['nullable'] ? "'nullable'" : "'required'";

        return match ($f['type']) {
            'text', 'richtext' => "{$req}, 'string'",
            'integer' => "{$req}, 'integer'",
            'date' => "{$req}, 'date'",
            'boolean' => "'nullable', 'boolean'",
            'image' => "'nullable', 'image', 'max:2048'",
            default => "{$req}, 'string', 'max:255'",
        };
    }

    private function buildService(array $ctx): string
    {
        $imageList = implode(', ', array_map(fn ($f) => "'{$f['name']}'", $ctx['imageFields']));

        return <<<PHP
        <?php

        namespace Modules\\{$ctx['module']}\\Services;

        use Illuminate\Http\UploadedFile;
        use Illuminate\Support\Facades\Storage;
        use Modules\\{$ctx['module']}\\Models\\{$ctx['module']};

        class {$ctx['module']}Service
        {
            /**
             * @param  array<string, ?UploadedFile>  \$files
             */
            public function store(array \$data, array \$files = []): {$ctx['module']}
            {
                \$data = \$this->handleUploads(\$data, \$files);

                return {$ctx['module']}::create(\$data);
            }

            /**
             * @param  array<string, ?UploadedFile>  \$files
             */
            public function update({$ctx['module']} \${$ctx['varSingular']}, array \$data, array \$files = []): void
            {
                foreach (\$files as \$field => \$file) {
                    if (\$file && \${$ctx['varSingular']}->{\$field}) {
                        Storage::disk('public')->delete(\${$ctx['varSingular']}->{\$field});
                    }
                }

                \$data = \$this->handleUploads(\$data, \$files);

                \${$ctx['varSingular']}->update(\$data);
            }

            public function destroy({$ctx['module']} \${$ctx['varSingular']}): void
            {
                foreach ([{$imageList}] as \$field) {
                    if (\${$ctx['varSingular']}->{\$field}) {
                        Storage::disk('public')->delete(\${$ctx['varSingular']}->{\$field});
                    }
                }

                \${$ctx['varSingular']}->delete();
            }

            /**
             * @param  array<string, ?UploadedFile>  \$files
             */
            private function handleUploads(array \$data, array \$files): array
            {
                foreach (\$files as \$field => \$file) {
                    if (\$file) {
                        \$data[\$field] = \$file->store('{$ctx['table']}', 'public');
                    }
                }

                return \$data;
            }
        }

        PHP;
    }

    private function buildController(array $ctx): string
    {
        $searchField = $this->searchField($ctx);
        $searchClause = $searchField
            ? "when(request('search'), fn (\$q, \$search) => \$q->where('{$searchField}', 'like', \"%{\$search}%\"))\n                ->"
            : '';
        $orderClause = $ctx['hasUrutan'] ? "orderBy('urutan')->orderBy('id')" : 'latest()';

        $imageKeys = implode(', ', array_map(fn ($f) => "'{$f['name']}'", $ctx['imageFields']));
        $filesArray = implode("\n", array_map(
            fn ($f) => "                    '{$f['name']}' => \$request->file('{$f['name']}'),",
            $ctx['imageFields']
        ));

        if ($ctx['hasImage']) {
            $constructor = "    public function __construct(private {$ctx['module']}Service \$service) {}\n\n";
            $useService = "use Modules\\{$ctx['module']}\\Services\\{$ctx['module']}Service;\n";
            $storeBody = <<<PHP
                    \$this->service->store(
                            \$request->safe()->except([{$imageKeys}]),
                            [
            {$filesArray}
                            ]
                        );
            PHP;
            $updateBody = <<<PHP
                    \$this->service->update(
                            \${$ctx['varSingular']},
                            \$request->safe()->except([{$imageKeys}]),
                            [
            {$filesArray}
                            ]
                        );
            PHP;
            $destroyBody = "        \$this->service->destroy(\${$ctx['varSingular']});";
        } else {
            $constructor = '';
            $useService = '';
            $storeBody = "        {$ctx['module']}::create(\$request->validated());";
            $updateBody = "        \${$ctx['varSingular']}->update(\$request->validated());";
            $destroyBody = "        \${$ctx['varSingular']}->delete();";
        }

        return <<<PHP
        <?php

        namespace Modules\\{$ctx['module']}\\Http\\Controllers\\Admin;

        use App\Http\Controllers\Controller;
        use Illuminate\Routing\Controllers\HasMiddleware;
        use Illuminate\Routing\Controllers\Middleware;
        use Modules\\{$ctx['module']}\\Http\\Requests\\Store{$ctx['module']}Request;
        use Modules\\{$ctx['module']}\\Http\\Requests\\Update{$ctx['module']}Request;
        use Modules\\{$ctx['module']}\\Models\\{$ctx['module']};
        {$useService}
        class {$ctx['module']}Controller extends Controller implements HasMiddleware
        {
        {$constructor}    public static function middleware(): array
            {
                return [
                    new Middleware('permission:{$ctx['permission']}.view', only: ['index', 'show']),
                    new Middleware('permission:{$ctx['permission']}.create', only: ['create', 'store']),
                    new Middleware('permission:{$ctx['permission']}.edit', only: ['edit', 'update']),
                    new Middleware('permission:{$ctx['permission']}.delete', only: ['destroy']),
                ];
            }

            public function index()
            {
                \${$ctx['varPlural']} = {$ctx['module']}::{$searchClause}{$orderClause}
                    ->paginate(15)
                    ->withQueryString();

                return view('{$ctx['viewNs']}::admin.index', compact('{$ctx['varPlural']}'));
            }

            public function create()
            {
                return view('{$ctx['viewNs']}::admin.create');
            }

            public function store(Store{$ctx['module']}Request \$request)
            {
        {$storeBody}

                return redirect()->route('admin.{$ctx['route']}.index')
                    ->with('success', '{$ctx['module']} berhasil ditambahkan.');
            }

            public function edit({$ctx['module']} \${$ctx['varSingular']})
            {
                return view('{$ctx['viewNs']}::admin.edit', compact('{$ctx['varSingular']}'));
            }

            public function update(Update{$ctx['module']}Request \$request, {$ctx['module']} \${$ctx['varSingular']})
            {
        {$updateBody}

                return redirect()->route('admin.{$ctx['route']}.index')
                    ->with('success', '{$ctx['module']} berhasil diperbarui.');
            }

            public function destroy({$ctx['module']} \${$ctx['varSingular']})
            {
        {$destroyBody}

                return redirect()->route('admin.{$ctx['route']}.index')
                    ->with('success', '{$ctx['module']} berhasil dihapus.');
            }
        }

        PHP;
    }

    private function buildRoutes(array $ctx): string
    {
        return <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;
        use Modules\\{$ctx['module']}\\Http\\Controllers\\Admin\\{$ctx['module']}Controller;

        Route::prefix('admin')
            ->middleware(['auth'])
            ->name('admin.')
            ->group(function () {
                Route::resource('{$ctx['route']}', {$ctx['module']}Controller::class)->except(['show']);
            });

        PHP;
    }

    private function buildIndexView(array $ctx): string
    {
        $var = $ctx['varPlural'];
        $singular = $ctx['varSingular'];
        $title = Str::headline($ctx['module']);
        $searchField = $this->searchField($ctx);

        $headCells = "                    <th class=\"text-left px-6 py-3 font-semibold text-slate-600\">No</th>\n";
        $bodyCells = "                        <td class=\"px-6 py-3 text-slate-400\">{{ \$loop->iteration }}</td>\n";

        foreach ($ctx['fields'] as $f) {
            $headCells .= "                    <th class=\"text-left px-6 py-3 font-semibold text-slate-600\">{$f['label']}</th>\n";
            $bodyCells .= '                        '.$this->indexBodyCell($f, $singular)."\n";
        }
        if ($ctx['hasUrutan']) {
            $headCells .= "                    <th class=\"text-left px-6 py-3 font-semibold text-slate-600\">Urutan</th>\n";
            $bodyCells .= "                        <td class=\"px-6 py-3 text-slate-500\">{{ \${$singular}->urutan }}</td>\n";
        }
        if ($ctx['hasStatus']) {
            $headCells .= "                    <th class=\"text-left px-6 py-3 font-semibold text-slate-600\">Status</th>\n";
            $bodyCells .= "                        <td class=\"px-6 py-3\">\n".
                "                            @if (\${$singular}->status)\n".
                "                                <span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text\">Aktif</span>\n".
                "                            @else\n".
                "                                <span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500\">Nonaktif</span>\n".
                "                            @endif\n".
                "                        </td>\n";
        }

        $colspan = 2 + count($ctx['fields']) + ($ctx['hasUrutan'] ? 1 : 0) + ($ctx['hasStatus'] ? 1 : 0);

        $searchBox = $searchField
            ? "<x-admin.search :action=\"route('admin.{$ctx['route']}.index')\" placeholder=\"Cari {$title}...\" />"
            : '<div></div>';

        return <<<BLADE
        @extends('layouts.admin')

        @section('title', '{$title}')

        @section('header')
            <h1 class="text-lg font-semibold text-slate-800">{$title}</h1>
        @endsection

        @section('content')
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                {$searchBox}
                @can('{$ctx['permission']}.create')
                    <a href="{{ route('admin.{$ctx['route']}.create') }}">
                        <x-admin.button>+ Tambah {$title}</x-admin.button>
                    </a>
                @endcan
            </div>

            <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-slate-50">
        {$headCells}                    <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse (\${$var} as \${$singular})
                            <tr class="hover:bg-slate-50 transition-colors">
        {$bodyCells}                        <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('{$ctx['permission']}.edit')
                                            <a href="{{ route('admin.{$ctx['route']}.edit', \${$singular}) }}">
                                                <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                            </a>
                                        @endcan
                                        @can('{$ctx['permission']}.delete')
                                            <form method="POST" action="{{ route('admin.{$ctx['route']}.destroy', \${$singular}) }}"
                                                onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{$colspan}" class="px-6 py-12 text-center text-slate-400">
                                    Belum ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                @if (\${$var}->hasPages())
                    <div class="px-6 py-4 border-t border-border">
                        {{ \${$var}->links() }}
                    </div>
                @endif
            </div>
        @endsection

        BLADE;
    }

    private function indexBodyCell(array $f, string $singular): string
    {
        $n = $f['name'];

        return match ($f['type']) {
            'image' => "<td class=\"px-6 py-3\">@if (\${$singular}->{$n})<img src=\"{{ Storage::url(\${$singular}->{$n}) }}\" class=\"w-10 h-10 rounded-lg object-cover border border-border\">@else<span class=\"text-slate-300\">—</span>@endif</td>",
            'boolean' => "<td class=\"px-6 py-3 text-slate-500\">{{ \${$singular}->{$n} ? 'Ya' : 'Tidak' }}</td>",
            'text', 'richtext' => "<td class=\"px-6 py-3 text-slate-500\">{{ Str::limit(strip_tags(\${$singular}->{$n}), 50) }}</td>",
            'date' => "<td class=\"px-6 py-3 text-slate-500\">{{ \${$singular}->{$n}?->format('d M Y') }}</td>",
            default => "<td class=\"px-6 py-3 text-slate-800\">{{ \${$singular}->{$n} }}</td>",
        };
    }

    private function buildFormView(array $ctx, string $mode): string
    {
        $isEdit = $mode === 'edit';
        $title = Str::headline($ctx['module']);
        $singular = $ctx['varSingular'];
        $heading = $isEdit ? "Edit {$title}" : "Tambah {$title}";
        $action = $isEdit
            ? "{{ route('admin.{$ctx['route']}.update', \${$singular}) }}"
            : "{{ route('admin.{$ctx['route']}.store') }}";
        $method = $isEdit ? "@csrf\n                @method('PUT')" : '@csrf';
        $submit = $isEdit ? 'Perbarui' : 'Simpan';

        $formFields = '';
        foreach ($ctx['fields'] as $f) {
            $formFields .= $this->formField($f, $ctx, $isEdit)."\n\n";
        }
        if ($ctx['hasUrutan']) {
            $value = $isEdit ? "old('urutan', \${$singular}->urutan)" : "old('urutan', 0)";
            $formFields .= <<<BLADE
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                                <x-admin.input-number name="urutan" :value="{$value}" min="0" />
                                @error('urutan') <p class="mt-1 text-xs text-danger">{{ \$message }}</p> @enderror
                            </div>

                BLADE;
        }
        if ($ctx['hasStatus']) {
            $selected = $isEdit ? "old('status', (string) \${$singular}->status)" : "old('status', '1')";
            $formFields .= <<<BLADE
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                                <x-admin.select name="status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" :selected="{$selected}" />
                                @error('status') <p class="mt-1 text-xs text-danger">{{ \$message }}</p> @enderror
                            </div>

                BLADE;
        }
        $formFields = rtrim($formFields);

        return <<<BLADE
        @extends('layouts.admin')

        @section('title', '{$heading}')

        @section('header')
            <h1 class="text-lg font-semibold text-slate-800">{$heading}</h1>
        @endsection

        @section('content')
            <div>
                <div class="bg-card rounded-xl shadow-card border border-border p-6">
                    <form method="POST" action="{$action}" enctype="multipart/form-data" class="space-y-5">
                        {$method}

        {$formFields}

                        <div class="flex items-center gap-3 pt-2">
                            <x-admin.button type="submit">{$submit}</x-admin.button>
                            <a href="{{ route('admin.{$ctx['route']}.index') }}">
                                <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endsection

        BLADE;
    }

    private function formField(array $f, array $ctx, bool $isEdit): string
    {
        $n = $f['name'];
        $label = $f['label'];
        $singular = $ctx['varSingular'];
        $req = $f['nullable'] ? '<span class="text-slate-400 font-normal">(opsional)</span>' : '<span class="text-danger">*</span>';
        $old = $isEdit ? "old('{$n}', \${$singular}->{$n})" : "old('{$n}')";
        $error = "@error('{$n}') <p class=\"mt-1 text-xs text-danger\">{{ \$message }}</p> @enderror";
        $labelHtml = "<label class=\"block text-sm font-medium text-slate-700 mb-1.5\">{$label} {$req}</label>";

        $inner = match ($f['type']) {
            'text' => $isEdit
                ? "<x-admin.textarea name=\"{$n}\">{{ old('{$n}', \${$singular}->{$n}) }}</x-admin.textarea>"
                : "<x-admin.textarea name=\"{$n}\">{{ old('{$n}') }}</x-admin.textarea>",
            'richtext' => $isEdit
                ? "<x-admin.rich-editor name=\"{$n}\" :value=\"\${$singular}->{$n}\" />"
                : "<x-admin.rich-editor name=\"{$n}\" />",
            'integer' => "<x-admin.input-number name=\"{$n}\" :value=\"{$old}\" />",
            'date' => "<x-admin.input-text type=\"date\" name=\"{$n}\" :value=\"{$old}\" />",
            'boolean' => '<x-admin.select name="'.$n.'" :options="[\'1\' => \'Ya\', \'0\' => \'Tidak\']" :selected="'.($isEdit ? "old('{$n}', (string) \${$singular}->{$n})" : "old('{$n}', '0')").'" />',
            'image' => $this->imageField($f, $ctx, $isEdit),
            default => "<x-admin.input-text name=\"{$n}\" :value=\"{$old}\" placeholder=\"{$label}\" />",
        };

        return "                    <div>\n".
            "                        {$labelHtml}\n".
            "                        {$inner}\n".
            "                        {$error}\n".
            '                    </div>';
    }

    private function imageField(array $f, array $ctx, bool $isEdit): string
    {
        $n = $f['name'];
        $singular = $ctx['varSingular'];

        if ($isEdit) {
            return "@if (\${$singular}->{$n})<div class=\"mb-3\"><img src=\"{{ Storage::url(\${$singular}->{$n}) }}\" class=\"h-24 w-auto rounded-lg object-cover border border-border\"></div>@endif\n".
                "                        <x-admin.file-upload name=\"{$n}\" hint=\"Biarkan kosong jika tidak ingin mengubah. Maks. 2MB\" accept=\"image/*\" :preview=\"true\" />";
        }

        return "<x-admin.file-upload name=\"{$n}\" hint=\"JPG, PNG, WEBP maks. 2MB\" accept=\"image/*\" :preview=\"true\" />";
    }

    private function searchField(array $ctx): ?string
    {
        foreach ($ctx['fields'] as $f) {
            if ($f['type'] === 'string') {
                return $f['name'];
            }
        }

        return null;
    }

    private function createPermissions(string $permission): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            Permission::firstOrCreate(['name' => "{$permission}.{$action}", 'guard_name' => 'web']);
        }

        Role::where('name', 'developer')->first()?->syncPermissions(Permission::all());
    }

    /**
     * Tambahkan baris permission modul baru ke database/seeders/DatabaseSeeder.php,
     * supaya permission tidak hilang saat `migrate:fresh --seed` di environment lain.
     */
    private function syncPermissionsToSeeder(string $permission, ?string $seederPath = null): bool
    {
        $seederPath ??= base_path('database/seeders/DatabaseSeeder.php');

        if (! File::exists($seederPath)) {
            return false;
        }

        $content = File::get($seederPath);

        if (str_contains($content, "'{$permission}.view'")) {
            return false;
        }

        $line = "            '{$permission}.view', '{$permission}.create', '{$permission}.edit', '{$permission}.delete',\n";

        $updated = preg_replace(
            '/(\$permissions\s*=\s*\[\n)/',
            '$1'.$line,
            $content,
            1
        );

        if ($updated === null || $updated === $content) {
            return false;
        }

        File::put($seederPath, $updated);

        return true;
    }

    private function createMenu(array $input, string $route, string $permission): void
    {
        $parentId = $input['menu_parent_id'] ?? null;

        $urutan = Menu::where('parent_id', $parentId)->max('urutan') + 1;

        Menu::create([
            'parent_id' => $parentId,
            'label' => Str::headline(Str::studly($input['name'])),
            'route_name' => "admin.{$route}.index",
            'active_pattern' => "admin.{$route}.*",
            'permission' => "{$permission}.view",
            'icon' => $input['menu_icon'] ?? 'archive-box',
            'urutan' => $urutan,
            'is_active' => 1,
        ]);
    }

    private function runProcess(string $name, array $command, int $timeout = 120): void
    {
        $process = new Process($command, base_path(), $this->processEnv(), null, $timeout);
        $process->run();

        $output = trim($process->getOutput()."\n".$process->getErrorOutput());

        $this->steps[] = [
            'name' => $name,
            'output' => $output !== '' ? $output : '(tidak ada output)',
            'ok' => $process->isSuccessful(),
        ];

        if (! $process->isSuccessful()) {
            throw new RuntimeException("Gagal pada langkah: {$name}\n\n{$output}");
        }
    }

    private function runProcessSoft(string $name, array $command, int $timeout = 120): void
    {
        $process = new Process($command, base_path(), $this->processEnv(), null, $timeout);
        $process->run();

        $output = trim($process->getOutput()."\n".$process->getErrorOutput());

        $this->steps[] = [
            'name' => $name,
            'output' => $output !== '' ? $output : '(tidak ada output)',
            'ok' => $process->isSuccessful(),
        ];
    }

    /**
     * Environment yang diwariskan ke subprocess.
     * Symfony Process dengan env array hanya mewariskan var yang kita tentukan,
     * jadi kita set minimal yang dibutuhkan composer dan artisan.
     *
     * @return array<string, string>
     */
    private function processEnv(): array
    {
        $home = $this->homeDir() ?? sys_get_temp_dir();
        $herdBin = $home.'/Library/Application Support/Herd/bin';

        // Gabungkan PATH asli dengan Herd bin supaya composer/php ditemukan
        $currentPath = getenv('PATH') ?: ($_SERVER['PATH'] ?? '/usr/local/bin:/usr/bin:/bin');
        $path = $herdBin.':'.$currentPath;

        return [
            'HOME' => $home,
            'PATH' => $path,
            'COMPOSER_HOME' => $home.'/.composer',
        ];
    }

    private function phpBin(): string
    {
        // Saat dijalankan dari web (PHP-FPM), PHP_BINARY menunjuk ke binary FPM
        // (mis. php84-fpm) yang tidak bisa menjalankan artisan. Cari PHP CLI asli.
        // getenv('HOME') sering kosong di FPM (clear_env=yes) — gunakan posix_getpwuid
        // sebagai sumber home dir yang andal.
        $home = $this->homeDir();

        $candidates = [
            env('PHP_CLI_BIN'),
            $home ? "{$home}/Library/Application Support/Herd/bin/php" : null,
            // Coba sibling CLI dari FPM binary (mis. /path/php84-fpm → /path/php84)
            PHP_BINARY !== '' ? preg_replace('/-fpm$/', '', PHP_BINARY) : null,
            '/opt/homebrew/bin/php',
            '/usr/local/bin/php',
        ];

        foreach ($candidates as $bin) {
            if ($bin && is_executable($bin) && ! str_contains(basename($bin), 'fpm') && ! str_contains(basename($bin), 'cgi')) {
                return $bin;
            }
        }

        // PHP_BINARY terakhir — hanya jika bukan FPM/CGI.
        if (! str_contains(basename(PHP_BINARY), 'fpm') && ! str_contains(basename(PHP_BINARY), 'cgi')) {
            return PHP_BINARY;
        }

        return 'php';
    }

    /**
     * Composer adalah PHP phar. Di konteks FPM PATH bisa tak punya `php`,
     * jadi jalankan via phpBin() langsung.
     *
     * @return array<int, string>
     */
    private function composerCommand(): array
    {
        $home = $this->homeDir();

        $paths = [
            env('COMPOSER_BIN'),
            $home ? "{$home}/Library/Application Support/Herd/bin/composer" : null,
            '/opt/homebrew/bin/composer',
            '/usr/local/bin/composer',
            base_path('composer.phar'),
        ];

        foreach ($paths as $path) {
            if ($path && is_file($path)) {
                return [$this->phpBin(), $path];
            }
        }

        return ['composer'];
    }

    /**
     * Dapatkan home directory user yang sedang menjalankan proses PHP.
     * Tidak bergantung pada environment variable (aman di FPM dengan clear_env=yes).
     */
    private function homeDir(): ?string
    {
        // getenv/SERVER dulu (tersedia di CLI dan FPM tanpa clear_env)
        $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);
        if ($home) {
            return $home;
        }

        // posix_getpwuid selalu tersedia dan tidak butuh env var
        if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $info = posix_getpwuid(posix_getuid());
            if ($info && ! empty($info['dir'])) {
                return $info['dir'];
            }
        }

        return null;
    }
}
