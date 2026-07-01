<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function developerUser(): User
{
    $role = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('sidebar admin punya markup drawer mobile', function () {
    $this->actingAs(developerUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('toggleSidebar()', false)
        ->assertSee('mobileOpen', false)
        ->assertSee('md:hidden', false);
});

test('halaman index modul membungkus tabel dengan overflow-x-auto dan header responsive', function () {
    $this->actingAs(developerUser())
        ->get(route('admin.teams.index'))
        ->assertOk()
        ->assertSee('overflow-x-auto', false)
        ->assertSee('flex-col sm:flex-row sm:items-center sm:justify-between', false);
});

test('generator menulis template index yang responsive untuk modul baru', function () {
    $service = new Modules\Generator\Services\ModuleGeneratorService;
    $method = new ReflectionMethod($service, 'buildIndexView');
    $method->setAccessible(true);

    $view = $method->invoke($service, [
        'module' => 'Portofolio',
        'route' => 'portofolios',
        'permission' => 'portofolio',
        'varPlural' => 'portofolios',
        'varSingular' => 'portofolio',
        'fields' => [
            ['name' => 'name', 'label' => 'Nama', 'type' => 'string', 'nullable' => false],
        ],
        'hasUrutan' => false,
        'hasStatus' => false,
    ]);

    expect($view)->toContain('overflow-x-auto')
        ->toContain('flex flex-col sm:flex-row sm:items-center sm:justify-between');
});
