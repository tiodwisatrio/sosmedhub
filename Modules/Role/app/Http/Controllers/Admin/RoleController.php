<?php

namespace Modules\Role\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Role\Http\Requests\StoreRoleRequest;
use Modules\Role\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller implements HasMiddleware
{
    private const PROTECTED = ['developer'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:role.view', only: ['index']),
            new Middleware('permission:role.create', only: ['create', 'store']),
            new Middleware('permission:role.edit', only: ['edit', 'update']),
            new Middleware('permission:role.delete', only: ['destroy']),
        ];
    }

    private array $labels = [
        'category' => 'Kategori',
        'team' => 'Tim',
        'layanan' => 'Layanan',
        'product' => 'Produk',
        'post' => 'Artikel',
        'media' => 'Media',
        'user' => 'Pengguna',
        'role' => 'Role & Akses',
        'menu' => 'Menu',
        'settings' => 'Pengaturan',
    ];

    private function matrix(): array
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->mapWithKeys(fn ($group, $resource) => [
                ($this->labels[$resource] ?? ucfirst($resource)) => $group->pluck('name')->toArray(),
            ])
            ->toArray();
    }

    public function index()
    {
        $roles = Role::where('name', '!=', 'developer')
            ->withCount('users')
            ->get();

        return view('role::admin.index', compact('roles'));
    }

    public function create()
    {
        $matrix = $this->matrix();

        return view('role::admin.create', compact('matrix'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function edit(Role $role)
    {
        abort_if(in_array($role->name, self::PROTECTED), 403);

        $matrix = $this->matrix();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('role::admin.edit', compact('role', 'matrix', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_if(in_array($role->name, self::PROTECTED), 403);

        $role->update(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        abort_if(in_array($role->name, self::PROTECTED), 403);

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
