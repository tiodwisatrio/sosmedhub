<?php

use App\Models\User;
use Modules\Post\Http\Requests\StorePostRequest;
use Modules\SiteSetting\Http\Requests\UpdateSiteSettingRequest;
use Spatie\Permission\Models\Permission;

test('form request authorize() menolak user tanpa permission terkait', function () {
    $user = User::factory()->create();

    $request = new StorePostRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeFalse();
});

test('form request authorize() mengizinkan user dengan permission terkait', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(
        Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web'])
    );

    $request = new StorePostRequest;
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});

test('form request authorize() menolak jika belum login sama sekali', function () {
    $request = new UpdateSiteSettingRequest;
    $request->setUserResolver(fn () => null);

    expect($request->authorize())->toBeFalse();
});
