<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

it('memberi pesan yang jelas bila total unggahan melebihi batas server', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'scheduler.create', 'guard_name' => 'web']));

    $response = $this->actingAs($user)
        ->from(route('admin.scheduled-posts.create'))
        ->withServerVariables(['CONTENT_LENGTH' => 9_999_999_999_999])
        ->post(route('admin.scheduled-posts.store'));

    $response->assertRedirect(route('admin.scheduled-posts.create'))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'melebihi batas server ('.ini_get('post_max_size').')'));
});

it('permintaan JSON tetap mendapat respons 413 standar', function () {
    $this->withServerVariables(['CONTENT_LENGTH' => 9_999_999_999_999])
        ->postJson('/api/tidak-ada')
        ->assertStatus(413);
});
