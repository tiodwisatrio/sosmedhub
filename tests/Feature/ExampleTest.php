<?php

use App\Models\User;

it('menampilkan beranda publik untuk tamu', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Terbit sesuai jadwal.')
        ->assertSee(route('login'), false)
        ->assertSee(route('privacy'), false);
});

it('menampilkan beranda untuk user yang sudah disetujui dengan tombol ke dashboard', function () {
    $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Terbit sesuai jadwal.')
        ->assertSee('Buka dashboard')
        ->assertSee(route('admin.dashboard'), false)
        ->assertDontSee('Daftar akun baru')
        ->assertDontSee('Akun baru disetujui admin terlebih dahulu.');
});

it('menampilkan tautan status persetujuan, bukan dashboard, untuk user yang belum disetujui', function () {
    $user = User::factory()->create(['approval_status' => User::APPROVAL_PENDING]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Lihat status persetujuan')
        ->assertSee(route('approval.pending'), false)
        ->assertDontSee('Buka dashboard');
});
