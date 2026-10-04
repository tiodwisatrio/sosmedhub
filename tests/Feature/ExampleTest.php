<?php

use App\Models\User;

it('menampilkan beranda publik untuk tamu', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Terbit sesuai jadwal.')
        ->assertSee(route('login'), false)
        ->assertSee(route('privacy'), false);
});

it('mengarahkan user yang sudah masuk dari beranda ke dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('admin.dashboard'));
});
