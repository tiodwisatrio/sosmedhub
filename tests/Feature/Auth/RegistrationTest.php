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
