<?php

use App\Models\User;

test('user yang login bisa mengakses dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});

test('guest tidak bisa mengakses dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
