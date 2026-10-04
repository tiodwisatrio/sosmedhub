<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Services\ScheduledPostService;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;

function schedulerUser(array $permissions = ['view']): User
{
    $pairs = [
        'view' => 'scheduler.view',
        'create' => 'scheduler.create',
        'edit' => 'scheduler.edit',
        'delete' => 'scheduler.delete',
    ];

    $names = array_map(fn ($key) => $pairs[$key], $permissions);

    $user = User::factory()->create();
    $user->givePermissionTo(
        array_map(
            fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
            $names
        )
    );

    return $user;
}

function futureWibInput(string $minutes = '+60 minutes'): string
{
    // Jam terbit wajib sejajar slot cron.
    return ScheduledPost::nextSlot(Carbon::now()->addMinutes(60))
        ->setTimezone(ScheduledPost::WIB)
        ->format('Y-m-d\TH:i');
}

function socialAccountFor(User $user, array $attributes = []): SocialAccount
{
    return SocialAccount::create(array_replace([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'username' => 'brand_'.$user->id,
        'display_name' => 'Brand '.$user->id,
        'status' => SocialAccount::STATUS_ACTIVE,
    ], $attributes));
}

test('halaman antrian penjadwalan menolak user tanpa permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.index'))
        ->assertForbidden();
});

test('halaman antrian penjadwalan menampilkan post yang akan terbit', function () {
    $user = schedulerUser();
    $otherUser = schedulerUser();
    $wib = ScheduledPost::WIB;
    $monday = Carbon::now($wib)->copy()->startOfWeek(Carbon::MONDAY)->addWeek();
    $scheduled = $monday->copy()->addDays(2)->setTime(10, 0);

    ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'caption' => 'Postingan minggu ini.',
        'scheduled_at' => $scheduled->copy()->utc(),
    ]);

    ScheduledPost::factory()->create([
        'user_id' => $otherUser->id,
        'caption' => 'Postingan user lain.',
        'scheduled_at' => $scheduled->copy()->utc(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.index', ['week' => $monday->toDateString()]))
        ->assertOk()
        ->assertSee('Postingan minggu ini.')
        ->assertDontSee('Postingan user lain.')
        ->assertSee('Riwayat');
});

test('halaman form penjadwalan bisa diakses user dengan permission create', function () {
    $user = schedulerUser(['view', 'create']);
    socialAccountFor($user);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('Jadwalkan Postingan');
});

test('postingan berhasil dijadwalkan dan disimpan dalam UTC', function () {
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);
    $inputWib = futureWibInput();

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), [
            'caption' => 'Promo minggu depan.',
            'social_account_id' => $account->id,
            'media' => [UploadedFile::fake()->image('promo.jpg', 800, 800)],
            'scheduled_at' => $inputWib,
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'))
        ->assertSessionHas('success');

    $post = ScheduledPost::first();

    expect($post)
        ->not->toBeNull()
        ->and($post->user_id)->toBe($user->id)
        ->and($post->social_account_id)->toBe($account->id)
        ->and($post->status)->toBe(ScheduledPost::STATUS_SCHEDULED)
        ->and($post->media_path)->not->toBeNull();

    $expectedUtc = Carbon::parse($inputWib, ScheduledPost::WIB)->utc();
    expect($post->scheduled_at->eq($expectedUtc))->toBeTrue();
});

test('postingan bisa membawa beberapa foto (carousel)', function () {
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), [
            'caption' => 'Album produk lengkap.',
            'social_account_id' => $account->id,
            'media' => [
                UploadedFile::fake()->image('foto-1.jpg', 800, 800),
                UploadedFile::fake()->image('foto-2.jpg', 800, 800),
            ],
            'scheduled_at' => futureWibInput(),
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'));

    $post = ScheduledPost::first();

    expect($post->media)->toHaveCount(2);
    expect($post->media->pluck('position')->all())->toBe([0, 1]);
});

test('postingan menolak lebih dari 10 foto', function () {
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), [
            'caption' => 'Terlalu banyak foto.',
            'social_account_id' => $account->id,
            'media' => collect(range(1, 11))->map(
                fn () => UploadedFile::fake()->image('foto.jpg', 800, 800)
            )->all(),
            'scheduled_at' => futureWibInput(),
        ])
        ->assertSessionHasErrors('media');

    expect(ScheduledPost::count())->toBe(0);
});

test('postingan menolak waktu terbit di masa lalu', function () {
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);
    $pastWib = Carbon::now()->setTimezone(ScheduledPost::WIB)->subHour()->format('Y-m-d\TH:i');

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), [
            'caption' => 'Promo kemarin.',
            'social_account_id' => $account->id,
            'media' => [UploadedFile::fake()->image('promo.jpg', 800, 800)],
            'scheduled_at' => $pastWib,
        ])
        ->assertSessionHasErrors('scheduled_at');

    expect(ScheduledPost::count())->toBe(0);
});

test('postingan menolak foto yang bukan jpeg', function () {
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), [
            'caption' => 'Promo dengan berkas salah.',
            'social_account_id' => $account->id,
            'media' => [UploadedFile::fake()->create('dokumen.pdf', 100)],
            'scheduled_at' => futureWibInput(),
        ])
        ->assertSessionHasErrors(['media.0']);
});

test('postingan menolak foto png dan menerima jpeg', function () {
    Storage::fake('public');
    $user = schedulerUser(['view', 'create']);
    $account = socialAccountFor($user);

    $payload = fn ($file) => [
        'caption' => 'Uji format foto.',
        'social_account_id' => $account->id,
        'media' => [$file],
        'scheduled_at' => futureWibInput(),
    ];

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), $payload(UploadedFile::fake()->image('foto.png', 800, 800)))
        ->assertSessionHasErrors(['media.0']);

    expect(ScheduledPost::count())->toBe(0);

    $this->actingAs($user)
        ->post(route('admin.scheduled-posts.store'), $payload(UploadedFile::fake()->image('foto.jpg', 800, 800)))
        ->assertSessionHasNoErrors();

    expect(ScheduledPost::count())->toBe(1);
});

test('halaman edit bisa diakses user dengan permission edit', function () {
    $user = schedulerUser(['view', 'edit']);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'scheduled_at' => Carbon::now()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.edit', $post))
        ->assertOk()
        ->assertSee('Ubah Postingan');
});

test('postingan bisa diubah selama belum terbit', function () {
    $user = schedulerUser(['view', 'edit']);
    $account = socialAccountFor($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'caption' => 'Caption lama.',
        'scheduled_at' => Carbon::now()->addDay(),
    ]);

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $post), [
            'caption' => 'Caption baru.',
            'social_account_id' => $account->id,
            'scheduled_at' => futureWibInput('+2 hours'),
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'));

    expect($post->fresh()->caption)->toBe('Caption baru.');
});

test('edit bisa menghapus foto lama dan menambah foto baru', function () {
    $user = schedulerUser(['view', 'edit']);
    $account = socialAccountFor($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'scheduled_at' => Carbon::now()->addDay(),
    ]);

    $post->media()->create(['media_path' => 'scheduled-posts/lama.jpg', 'position' => 1]);
    $toDelete = $post->media()->first();
    $pathToDelete = $toDelete->media_path;

    Storage::disk('public')->put($pathToDelete, 'gambar-lama');

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $post), [
            'caption' => 'Foto diperbarui.',
            'social_account_id' => $account->id,
            'media' => [UploadedFile::fake()->image('baru.jpg', 800, 800)],
            'remove_media' => [$toDelete->id],
            'scheduled_at' => futureWibInput('+2 hours'),
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'));

    $fresh = $post->fresh()->load('media');

    expect($fresh->media)->toHaveCount(2)
        ->and($fresh->media->pluck('position')->all())->toBe([0, 1])
        ->and(Storage::disk('public')->exists($pathToDelete))->toBeFalse();
});

test('postingan yang sudah terbit tidak bisa diubah atau dibatalkan', function () {
    $user = schedulerUser(['view', 'edit']);
    $account = socialAccountFor($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'status' => ScheduledPost::STATUS_PUBLISHED,
        'published_at' => Carbon::now()->subDay(),
    ]);

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $post), [
            'caption' => 'Kebobolan.',
            'social_account_id' => $account->id,
            'scheduled_at' => futureWibInput(),
        ])
        ->assertForbidden();
});

test('postingan terjadwal bisa dibatalkan', function () {
    $user = schedulerUser(['view', 'edit']);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'scheduled_at' => Carbon::now()->addDay(),
    ]);

    $this->actingAs($user)
        ->patch(route('admin.scheduled-posts.cancel', $post))
        ->assertRedirect(route('admin.scheduled-posts.index'));

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_CANCELLED);
});

test('postingan bisa dihapus oleh user dengan permission delete', function () {
    $user = schedulerUser(['view', 'delete']);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'scheduled_at' => Carbon::now()->addDay(),
    ]);
    $path = $post->media()->first()->media_path;
    Storage::disk('public')->put($path, 'gambar');

    $this->actingAs($user)
        ->delete(route('admin.scheduled-posts.destroy', $post))
        ->assertRedirect(route('admin.scheduled-posts.index'));

    expect(ScheduledPost::find($post->id))->toBeNull()
        ->and(ScheduledPostMedia::where('scheduled_post_id', $post->id)->exists())->toBeFalse()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

test('user tidak bisa mengubah postingan milik user lain', function () {
    $user = schedulerUser(['view', 'edit']);
    $otherUser = User::factory()->create();
    $account = socialAccountFor($user);
    $otherAccount = socialAccountFor($otherUser);
    $post = ScheduledPost::factory()->create([
        'user_id' => $otherUser->id,
        'social_account_id' => $otherAccount->id,
        'caption' => 'Punya orang lain.',
        'scheduled_at' => Carbon::now()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.edit', $post))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $post), [
            'caption' => 'Coba ambil alih.',
            'social_account_id' => $account->id,
            'scheduled_at' => futureWibInput('+2 hours'),
        ])
        ->assertForbidden();

    expect($post->fresh()->caption)->toBe('Punya orang lain.');
});

test('service menyimpan waktu WIB menjadi UTC dan menampilkan kembali WIB', function () {
    $user = User::factory()->create();
    $inputWib = '2026-09-01 08:30';

    $post = app(ScheduledPostService::class)->store(
        ['caption' => 'Tes zona waktu.', 'scheduled_at' => $inputWib],
        null,
        $user->id
    );

    expect($post->scheduled_at->format('Y-m-d H:i'))
        ->toBe('2026-09-01 01:30')
        ->and($post->scheduled_at->setTimezone(ScheduledPost::WIB)->format('Y-m-d H:i'))
        ->toBe('2026-09-01 08:30');
});

test('post gagal bisa dijadwalkan ulang lewat halaman ubah', function () {
    $user = schedulerUser(['view', 'edit']);
    $account = socialAccountFor($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'status' => ScheduledPost::STATUS_FAILED,
        'error_message' => 'Media tidak valid',
        'scheduled_at' => now()->subHour(),
    ]);

    $this->actingAs($user)->get(route('admin.scheduled-posts.edit', $post))
        ->assertOk()
        ->assertSee('Media tidak valid');

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $post), [
            'caption' => $post->caption,
            'social_account_id' => $account->id,
            'scheduled_at' => futureWibInput(),
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'));

    $fresh = $post->fresh();
    expect($fresh->status)->toBe(ScheduledPost::STATUS_SCHEDULED)
        ->and($fresh->error_message)->toBeNull();
});

test('post gagal tidak bisa dibatalkan, hanya dijadwalkan ulang atau dihapus', function () {
    $user = schedulerUser(['view', 'edit']);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'status' => ScheduledPost::STATUS_FAILED,
    ]);

    $this->actingAs($user)->patch(route('admin.scheduled-posts.cancel', $post))->assertForbidden();
});

test('duplikasi membuat draf dengan foto salinan', function () {
    Storage::fake('public');
    $user = schedulerUser(['view', 'create', 'edit']);
    $account = socialAccountFor($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'caption' => 'Caption asli.',
        'status' => ScheduledPost::STATUS_PUBLISHED,
    ]);
    Storage::disk('public')->put('scheduled-posts/asli.jpg', 'isi-foto');
    $post->media()->create(['media_path' => 'scheduled-posts/asli.jpg', 'position' => 0]);

    $response = $this->actingAs($user)->post(route('admin.scheduled-posts.duplicate', $post));

    $copy = ScheduledPost::where('id', '!=', $post->id)->firstOrFail();
    $response->assertRedirect(route('admin.scheduled-posts.edit', $copy));

    $copyMedia = $copy->media()->firstOrFail();
    expect($copy->status)->toBe(ScheduledPost::STATUS_DRAFT)
        ->and($copy->caption)->toBe('Caption asli.')
        ->and($copy->social_account_id)->toBe($account->id)
        ->and($copyMedia->media_path)->not->toBe('scheduled-posts/asli.jpg')
        ->and(Storage::disk('public')->exists($copyMedia->media_path))->toBeTrue()
        ->and(Storage::disk('public')->exists('scheduled-posts/asli.jpg'))->toBeTrue();

    $this->actingAs($user)
        ->put(route('admin.scheduled-posts.update', $copy), [
            'caption' => 'Caption asli.',
            'social_account_id' => $account->id,
            'scheduled_at' => futureWibInput(),
        ])
        ->assertRedirect(route('admin.scheduled-posts.index'));

    expect($copy->fresh()->status)->toBe(ScheduledPost::STATUS_SCHEDULED);
});

test('duplikasi menolak post milik user lain dan user tanpa izin membuat', function () {
    $owner = schedulerUser(['view', 'create']);
    $other = schedulerUser(['view', 'create']);
    $viewer = schedulerUser(['view']);
    $post = ScheduledPost::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->post(route('admin.scheduled-posts.duplicate', $post))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.scheduled-posts.duplicate', $post))->assertForbidden();
    expect(ScheduledPost::count())->toBe(1);
});
