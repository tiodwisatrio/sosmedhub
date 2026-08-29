<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'paket.view', 'paket.create', 'paket.edit', 'paket.delete',
            'category.view', 'category.create', 'category.edit', 'category.delete',
            'team.view', 'team.create', 'team.edit', 'team.delete',
            'layanan.view', 'layanan.create', 'layanan.edit', 'layanan.delete',
            'keunggulan.view', 'keunggulan.create', 'keunggulan.edit', 'keunggulan.delete',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
            'site-setting.view', 'site-setting.edit',
            'tentang-kami.view', 'tentang-kami.edit',
            'post.view', 'post.create', 'post.edit', 'post.delete',
            'banner.view', 'banner.create', 'banner.edit', 'banner.delete',
            'hero.view', 'hero.create', 'hero.edit', 'hero.delete',
            'klien.view', 'klien.create', 'klien.edit', 'klien.delete',
            'faq.view', 'faq.create', 'faq.edit', 'faq.delete',
            'generator.view', 'generator.create',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
        $developer->syncPermissions(Permission::all());

        $user = User::firstOrCreate(
            ['email' => 'tiodwisatrio27@gmail.com'],
            [
                'name' => 'Tio Dwi Satrio',
                'password' => Hash::make('default'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$developer]);
    }
}
