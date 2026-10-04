<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Menu\Database\Seeders\MenuDatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
            'site-setting.view', 'site-setting.edit',
            'social-account.view', 'social-account.create', 'social-account.edit', 'social-account.delete',
            'scheduler.view', 'scheduler.create', 'scheduler.edit', 'scheduler.delete',
        ];

        $legacyPermissions = [
            'paket.view', 'paket.create', 'paket.edit', 'paket.delete',
            'category.view', 'category.create', 'category.edit', 'category.delete',
            'team.view', 'team.create', 'team.edit', 'team.delete',
            'layanan.view', 'layanan.create', 'layanan.edit', 'layanan.delete',
            'keunggulan.view', 'keunggulan.create', 'keunggulan.edit', 'keunggulan.delete',
            'tentang-kami.view', 'tentang-kami.edit',
            'post.view', 'post.create', 'post.edit', 'post.delete',
            'banner.view', 'banner.create', 'banner.edit', 'banner.delete',
            'hero.view', 'hero.create', 'hero.edit', 'hero.delete',
            'klien.view', 'klien.create', 'klien.edit', 'klien.delete',
            'faq.view', 'faq.create', 'faq.edit', 'faq.delete',
            'generator.view', 'generator.create',
        ];

        Permission::whereIn('name', $legacyPermissions)->delete();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
        $developer->syncPermissions(Permission::all());

        $client = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $client->syncPermissions(
            Permission::whereIn('name', [
                'scheduler.view',
                'scheduler.create',
                'scheduler.edit',
                'scheduler.delete',
                'social-account.view',
                'social-account.create',
                'social-account.edit',
                'social-account.delete',
            ])->get()
        );

        $user = User::firstOrCreate(
            ['email' => 'tiodwisatrio27@gmail.com'],
            [
                'name' => 'Tio Dwi Satrio',
                'password' => Hash::make('default'),
                'email_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'status' => true,
            'approval_status' => User::APPROVAL_APPROVED,
            'approved_at' => $user->approved_at ?? now(),
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_reason' => null,
            'suspended_at' => null,
        ])->save();

        $user->syncRoles([$developer]);

        $this->call(MenuDatabaseSeeder::class);
    }
}
