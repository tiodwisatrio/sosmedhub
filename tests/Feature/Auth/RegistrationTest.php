<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new frontend registration becomes pending client', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Client Baru')
        ->set('email', 'client@example.test')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $user = User::firstWhere('email', 'client@example.test');

    expect($user)->not->toBeNull()
        ->and($user->approval_status)->toBe(User::APPROVAL_PENDING)
        ->and($user->hasRole('client'))->toBeTrue();

    expect(Role::where('name', 'client')->exists())->toBeTrue();

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('approval.pending', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('pendaftaran langsung disetujui dan masuk ke dashboard bila REGISTRATION_AUTO_APPROVE aktif', function () {
    config(['sosmedhub.registration.auto_approve' => true]);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Reviewer Meta')
        ->set('email', 'reviewer@example.test')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $user = User::firstWhere('email', 'reviewer@example.test');

    expect($user->approval_status)->toBe(User::APPROVAL_APPROVED)
        ->and($user->hasRole('client'))->toBeTrue();

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    $this->get(route('admin.dashboard'))->assertOk();
});

test('pendaftaran tetap pending secara bawaan', function () {
    expect(config('sosmedhub.registration.auto_approve'))->toBeFalse();
});

test('salinan halaman publik tidak menyebut persetujuan admin bila auto-approve aktif', function () {
    $this->get('/')->assertSee('Akun baru disetujui admin terlebih dahulu.');
    $this->get(route('terms'))->assertSee('Pendaftaran akun baru perlu disetujui admin.');

    config(['sosmedhub.registration.auto_approve' => true]);

    $this->get('/')->assertDontSee('Akun baru disetujui admin terlebih dahulu.');
    $this->get(route('terms'))->assertDontSee('Pendaftaran akun baru perlu disetujui admin.');
    $this->get(route('privacy'))->assertDontSee('Akun baru harus disetujui admin sebelum dapat dipakai.');
});
