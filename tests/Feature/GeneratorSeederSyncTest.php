<?php

use Illuminate\Support\Facades\File;
use Modules\Generator\Services\ModuleGeneratorService;

function callSyncPermissionsToSeeder(string $permission, string $seederPath): bool
{
    $service = new ModuleGeneratorService;
    $method = new ReflectionMethod(ModuleGeneratorService::class, 'syncPermissionsToSeeder');
    $method->setAccessible(true);

    return $method->invoke($service, $permission, $seederPath);
}

test('generator menambahkan permission baru ke salinan seeder', function () {
    $tempPath = sys_get_temp_dir().'/DatabaseSeederTest_'.uniqid().'.php';
    File::put($tempPath, File::get(base_path('database/seeders/DatabaseSeeder.php')));

    $result = callSyncPermissionsToSeeder('portofolio', $tempPath);

    expect($result)->toBeTrue();

    $content = File::get($tempPath);
    expect($content)->toContain("'portofolio.view', 'portofolio.create', 'portofolio.edit', 'portofolio.delete',");

    File::delete($tempPath);
});

test('generator tidak menduplikasi permission yang sudah ada di seeder', function () {
    $tempPath = sys_get_temp_dir().'/DatabaseSeederTest_'.uniqid().'.php';
    File::put($tempPath, File::get(base_path('database/seeders/DatabaseSeeder.php')));

    $result = callSyncPermissionsToSeeder('layanan', $tempPath);

    expect($result)->toBeFalse();

    $content = File::get($tempPath);
    expect(substr_count($content, "'layanan.view'"))->toBe(1);

    File::delete($tempPath);
});
