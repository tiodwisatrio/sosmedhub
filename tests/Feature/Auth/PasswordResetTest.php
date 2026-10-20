<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response
        ->assertSeeVolt('pages.auth.forgot-password')
        ->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password link request blocks the ip for 30 minutes after five requests in a minute', function () {
    Notification::fake();

    $users = User::factory()->count(6)->create();

    foreach ($users->take(5) as $user) {
        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();
    }

    $blockedUser = $users->last();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $blockedUser->email)
        ->call('sendPasswordResetLink')
        ->assertHasErrors('email');

    $this->travel(29)->minutes();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $blockedUser->email)
        ->call('sendPasswordResetLink')
        ->assertHasErrors('email');

    $this->travel(2)->minutes();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $blockedUser->email)
        ->call('sendPasswordResetLink')
        ->assertHasNoErrors();

    Notification::assertSentTimes(ResetPassword::class, 6);
});

test('requests spread over more than a minute do not block the ip', function () {
    Notification::fake();

    foreach (User::factory()->count(6)->create() as $user) {
        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();

        $this->travel(20)->seconds();
    }
});

test('password reset submissions share the same ip block as link requests', function () {
    foreach (range(1, 5) as $_) {
        Volt::test('pages.auth.reset-password', ['token' => 'invalid-token'])
            ->set('email', 'someone@example.com')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('resetPassword');
    }

    Volt::test('pages.auth.forgot-password')
        ->set('email', 'someone@example.com')
        ->call('sendPasswordResetLink')
        ->assertHasErrors('email');

    Volt::test('pages.auth.reset-password', ['token' => 'invalid-token'])
        ->set('email', 'someone@example.com')
        ->set('password', 'new-password-123')
        ->set('password_confirmation', 'new-password-123')
        ->call('resetPassword')
        ->assertHasErrors('email');
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response
            ->assertSeeVolt('pages.auth.reset-password')
            ->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    Volt::test('pages.auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $component = Volt::test('pages.auth.reset-password', ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('resetPassword');

        $component
            ->assertRedirect('/login')
            ->assertHasNoErrors();

        return true;
    });
});
