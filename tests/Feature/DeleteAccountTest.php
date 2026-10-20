<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
});

function userWithData(): array
{
    $user = User::factory()->create(['password' => Hash::make('password'), 'avatar' => 'avatars/foto.jpg']);
    Storage::disk('public')->put('avatars/foto.jpg', 'avatar');

    $account = SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'provider_account_id' => fake()->numerify('################'),
        'username' => 'toko',
        'access_token' => 'rahasia',
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'social_account_id' => $account->id]);
    $post->media()->delete();

    Storage::disk('public')->put('scheduled-posts/foto.jpg', 'isi');
    Storage::disk('public')->put('scheduled-posts/thumbs/foto.jpg', 'isi');
    $post->media()->create([
        'media_path' => 'scheduled-posts/foto.jpg',
        'thumbnail_path' => 'scheduled-posts/thumbs/foto.jpg',
        'position' => 0,
    ]);

    return [$user, $account, $post];
}

test('profile page shows the delete account section for clients but not developers', function () {
    $client = User::factory()->create();

    $this->actingAs($client)->get('/profile')
        ->assertOk()
        ->assertSeeVolt('profile.delete-user-form');

    $developer = User::factory()->create();
    $developer->assignRole(Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']));

    $this->actingAs($developer)->get('/profile')
        ->assertOk()
        ->assertDontSeeVolt('profile.delete-user-form');
});

test('a client can delete their account and all their data', function () {
    [$user, $account, $post] = userWithData();
    [$other, $otherAccount, $otherPost] = userWithData();
    Storage::disk('public')->put('scheduled-posts/lain.jpg', 'isi');
    $otherPost->media()->update(['media_path' => 'scheduled-posts/lain.jpg', 'thumbnail_path' => null]);

    Volt::actingAs($user)->test('profile.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();

    expect(User::find($user->id))->toBeNull()
        ->and(SocialAccount::find($account->id))->toBeNull()
        ->and(ScheduledPost::find($post->id))->toBeNull()
        ->and(Storage::disk('public')->exists('scheduled-posts/foto.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('scheduled-posts/thumbs/foto.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('avatars/foto.jpg'))->toBeFalse();

    expect(User::find($other->id))->not->toBeNull()
        ->and(SocialAccount::find($otherAccount->id))->not->toBeNull()
        ->and(ScheduledPost::find($otherPost->id))->not->toBeNull()
        ->and(Storage::disk('public')->exists('scheduled-posts/lain.jpg'))->toBeTrue();
});

test('the account is kept when the password is wrong', function () {
    [$user] = userWithData();

    Volt::actingAs($user)->test('profile.delete-user-form')
        ->set('password', 'salah')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect(User::find($user->id))->not->toBeNull()
        ->and(Storage::disk('public')->exists('scheduled-posts/foto.jpg'))->toBeTrue();
});

test('a developer account cannot be deleted from the profile page', function () {
    $developer = User::factory()->create(['password' => Hash::make('password')]);
    $developer->assignRole(Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']));

    Volt::actingAs($developer)->test('profile.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertForbidden();

    expect(User::find($developer->id))->not->toBeNull();
});
