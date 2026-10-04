<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('seeder membuat akun developer dari konfigurasi .env', function () {
    config([
        'sosmedhub.developer.name' => 'Dev Uji',
        'sosmedhub.developer.email' => 'dev-uji@example.com',
        'sosmedhub.developer.password' => 'rahasia-uji-123',
    ]);

    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', 'dev-uji@example.com')->firstOrFail();

    expect($user->name)->toBe('Dev Uji')
        ->and(Hash::check('rahasia-uji-123', $user->password))->toBeTrue()
        ->and(Hash::check('default', $user->password))->toBeFalse()
        ->and($user->isDeveloper())->toBeTrue();
});

test('seeder menolak membuat akun developer tanpa password', function () {
    config([
        'sosmedhub.developer.email' => 'dev-uji@example.com',
        'sosmedhub.developer.password' => null,
    ]);

    expect(fn () => $this->seed(DatabaseSeeder::class))
        ->toThrow(RuntimeException::class, 'DEVELOPER_PASSWORD');

    expect(User::where('email', 'dev-uji@example.com')->exists())->toBeFalse();
});

test('seeder tidak mengubah password akun developer yang sudah ada', function () {
    $existing = User::factory()->create([
        'email' => 'dev-uji@example.com',
        'password' => Hash::make('password-lama'),
    ]);

    config([
        'sosmedhub.developer.email' => 'dev-uji@example.com',
        'sosmedhub.developer.password' => null,
    ]);

    $this->seed(DatabaseSeeder::class);

    expect(Hash::check('password-lama', $existing->fresh()->password))->toBeTrue()
        ->and($existing->fresh()->isDeveloper())->toBeTrue();
});

test('seeder menolak berjalan tanpa DEVELOPER_EMAIL', function () {
    config(['sosmedhub.developer.email' => null]);

    expect(fn () => $this->seed(DatabaseSeeder::class))
        ->toThrow(RuntimeException::class, 'DEVELOPER_EMAIL');
});
