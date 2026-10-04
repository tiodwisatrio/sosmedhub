<?php

use App\Models\User;
use Modules\Scheduler\Http\Requests\StoreScheduledPostRequest;
use Modules\SiteSetting\Http\Requests\UpdateSiteSettingRequest;
use Modules\User\Http\Requests\StoreUserRequest;
use Spatie\Permission\Models\Permission;

test('form request authorize() menolak user tanpa permission terkait', function () {
    $user = User::factory()->create();

    $request = new StoreUserRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeFalse();
});

test('form request authorize() mengizinkan user dengan permission terkait', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(
        Permission::firstOrCreate(['name' => 'user.create', 'guard_name' => 'web'])
    );

    $request = new StoreUserRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});

test('form request authorize() menolak jika belum login sama sekali', function () {
    $request = new UpdateSiteSettingRequest;
    $request->setUserResolver(fn () => null);

    expect($request->authorize())->toBeFalse();
});

test('form request store postingan menolak user tanpa permission scheduler.create', function () {
    $user = User::factory()->create();

    $request = new StoreScheduledPostRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeFalse();
});

test('form request store postingan mengizinkan user dengan permission scheduler.create', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(
        Permission::firstOrCreate(['name' => 'scheduler.create', 'guard_name' => 'web'])
    );

    $request = new StoreScheduledPostRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});
