<?php

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Providers\SchedulerServiceProvider;
use Modules\Scheduler\Services\SchedulerHeartbeat;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function slotUser(array $permissions = ['scheduler.view', 'scheduler.create', 'scheduler.edit']): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(
        array_map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']), $permissions)
    );

    return $user;
}

function slotAccount(User $user): SocialAccount
{
    return SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'provider_account_id' => 'ig-slot-'.$user->id,
        'username' => 'slot_'.$user->id,
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);
}

function wib(string $time): Carbon
{
    return Carbon::parse($time, ScheduledPost::WIB);
}

beforeEach(fn () => config(['scheduler.slot_minutes' => 15]));

afterEach(fn () => Carbon::setTestNow());

dataset('slot berikutnya', [
    'tepat di slot pindah ke slot setelahnya' => ['2026-10-06 09:00:00', '2026-10-06 09:15'],
    'di tengah slot' => ['2026-10-06 09:07:30', '2026-10-06 09:15'],
    'detik terakhir sebelum slot' => ['2026-10-06 09:14:59', '2026-10-06 09:15'],
    'melewati pergantian jam' => ['2026-10-06 09:52:00', '2026-10-06 10:00'],
    'melewati pergantian hari' => ['2026-10-06 23:50:00', '2026-10-07 00:00'],
]);

it('menghitung slot 15 menit berikutnya', function (string $from, string $expected) {
    $next = ScheduledPost::nextSlot(wib($from));

    expect($next->copy()->setTimezone(ScheduledPost::WIB)->format('Y-m-d H:i'))->toBe($expected)
        ->and(ScheduledPost::isOnSlot($next))->toBeTrue();
})->with('slot berikutnya');

it('mengikuti nilai slot dari konfigurasi dan menolak nilai yang bukan pembagi 60', function () {
    config(['scheduler.slot_minutes' => 5]);
    expect(ScheduledPost::nextSlot(wib('2026-10-06 09:07:00'))->format('H:i'))->toBe('09:10')
        ->and(ScheduledPost::slotExamples())->toBe('09.00, 09.05, 09.10, atau 09.15');

    config(['scheduler.slot_minutes' => 7]);
    expect(ScheduledPost::slotMinutes())->toBe(15)
        ->and(ScheduledPost::slotExamples())->toBe('09.00, 09.15, 09.30, atau 09.45');
});

it('slot WIB tetap sejajar di UTC', function () {
    $utc = wib('2026-10-06 09:15:00')->utc();

    expect($utc->format('H:i'))->toBe('02:15')
        ->and(ScheduledPost::isOnSlot($utc))->toBeTrue()
        ->and(ScheduledPost::isOnSlot(wib('2026-10-06 09:10:00')->utc()))->toBeFalse();
});

it('menolak jam di luar slot saat membuat dan mengubah jadwal', function () {
    Storage::fake('public');
    $user = slotUser();
    $account = slotAccount($user);
    $tomorrow = now(ScheduledPost::WIB)->addDay()->format('Y-m-d');

    $payload = fn (string $time) => [
        'caption' => 'Uji slot.',
        'social_account_id' => $account->id,
        'media' => [UploadedFile::fake()->image('foto.jpg', 800, 800)],
        'scheduled_at' => "{$tomorrow}T{$time}",
    ];

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), $payload('09:10'))
        ->assertSessionHasErrors(['scheduled_at' => 'Pilih jam dengan kelipatan 15 menit, misalnya 09.00, 09.15, 09.30, atau 09.45.']);
    expect(ScheduledPost::count())->toBe(0);

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), $payload('09:15'))
        ->assertSessionHasNoErrors();
    $post = ScheduledPost::firstOrFail();

    $this->actingAs($user)->put(route('admin.scheduled-posts.update', $post), [
        'caption' => 'Uji slot.',
        'social_account_id' => $account->id,
        'scheduled_at' => "{$tomorrow}T09:20",
    ])->assertSessionHasErrors('scheduled_at');
});

it('form buat memakai tanggal, jam 24 jam, dan menit per slot yang terpisah', function () {
    Carbon::setTestNow(wib('2026-10-06 09:07:00'));
    $user = slotUser();
    slotAccount($user);

    $response = $this->actingAs($user)->get(route('admin.scheduled-posts.create'))->assertOk();
    $html = $response->getContent();

    // tiga kontrol terpisah, bukan datetime-local (yang mengikuti locale dan bisa menampilkan AM/PM)
    $response
        ->assertSee('id="schedule-date"', false)
        ->assertSee('type="date"', false)
        ->assertSee('min="2026-10-06"', false)
        ->assertDontSee('datetime-local', false)
        ->assertDontSee('AM')
        ->assertSee('Menit kelipatan 15 (09.00, 09.15, 09.30, atau 09.45)');

    preg_match('/<select id="schedule-hour".*?<\/select>/s', $html, $hour);
    preg_match('/<select id="schedule-minute".*?<\/select>/s', $html, $minute);

    expect(preg_match_all('/<option value="(\d{2})"/', $hour[0], $h))->toBe(24)
        ->and($h[1][0])->toBe('00')
        ->and($h[1][23])->toBe('23');

    preg_match_all('/<option value="(\d{2})"/', $minute[0], $m);
    expect($m[1])->toBe(['00', '15', '30', '45']);

    // nilai awal: slot berikutnya (09.07 -> 09.15) dipecah ke tiga nilai
    $response
        // preset jam dibuang
        ->assertDontSee('Akhir malam')
        ->assertDontSee('Jika jam preset');

    // nilai awal: slot berikutnya (09.07 -> 09.15) dipecah ke tiga nilai
    $cfg = composerConfig($html);
    expect($cfg['schedDate'])->toBe('2026-10-06')
        ->and($cfg['schedHour'])->toBe('09')
        ->and($cfg['schedMinute'])->toBe('15');
});

it('opsi menit mengikuti slot dari konfigurasi', function () {
    config(['scheduler.slot_minutes' => 30]);
    $user = slotUser();
    slotAccount($user);

    $html = $this->actingAs($user)->get(route('admin.scheduled-posts.create'))->getContent();
    preg_match('/<select id="schedule-minute".*?<\/select>/s', $html, $minute);
    preg_match_all('/<option value="(\d{2})"/', $minute[0], $m);

    expect($m[1])->toBe(['00', '30']);
});

it('form memakai nilai lama saat validasi gagal dan mengabaikan bentuk yang salah', function () {
    Carbon::setTestNow(wib('2026-10-06 09:07:00'));
    $user = slotUser();
    slotAccount($user);

    $cfg = composerConfig($this->actingAs($user)->withSession(['_old_input' => ['scheduled_at' => '2026-10-20T14:30']])
        ->get(route('admin.scheduled-posts.create'))->getContent());
    expect($cfg['schedDate'])->toBe('2026-10-20')
        ->and($cfg['schedHour'])->toBe('14')
        ->and($cfg['schedMinute'])->toBe('30');

    $cfg = composerConfig($this->actingAs($user)->withSession(['_old_input' => ['scheduled_at' => 'ngawur']])
        ->get(route('admin.scheduled-posts.create'))->getContent());
    expect($cfg['schedDate'])->toBe('2026-10-06')
        ->and($cfg['schedMinute'])->toBe('15');
});

it('form ubah mengganti waktu lampau dengan slot berikutnya', function () {
    Carbon::setTestNow(wib('2026-10-06 09:07:00'));
    $user = slotUser();
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => slotAccount($user)->id,
        'status' => ScheduledPost::STATUS_FAILED,
        'scheduled_at' => wib('2026-10-05 20:00:00')->utc(),
    ]);

    $cfg = composerConfig($this->actingAs($user)->get(route('admin.scheduled-posts.edit', $post))->assertOk()->getContent());
    expect($cfg['schedDate'])->toBe('2026-10-06')
        ->and($cfg['schedHour'])->toBe('09')
        ->and($cfg['schedMinute'])->toBe('15');
});

it('duplikasi mengisi waktu usulan yang sejajar slot', function () {
    $user = slotUser(['scheduler.view', 'scheduler.create', 'scheduler.edit']);
    $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_PUBLISHED]);

    $this->actingAs($user)->post(route('admin.scheduled-posts.duplicate', $post));

    $copy = ScheduledPost::where('id', '!=', $post->id)->firstOrFail();
    expect(ScheduledPost::isOnSlot($copy->scheduled_at))->toBeTrue();
});

it('scheduler mengirim ke antrean sebelum memproses antrean, dengan kunci berumur pendek', function () {
    $events = collect(app(Schedule::class)->events());

    $dispatchIndex = $events->search(fn ($e) => str_contains($e->command, 'scheduler:dispatch-due'));
    $workerIndex = $events->search(fn ($e) => str_contains($e->command, 'queue:work'));

    expect($dispatchIndex)->not->toBeFalse()
        ->and($workerIndex)->not->toBeFalse()
        ->and($dispatchIndex)->toBeLessThan($workerIndex);

    $worker = $events[$workerIndex];
    expect($worker->command)->toContain('--stop-when-empty')
        ->and($worker->command)->not->toContain('--stop-when-empty=')
        ->and($worker->command)->toContain('--max-time=240')
        ->and($events[$dispatchIndex]->withoutOverlapping)->toBeTrue()
        ->and($events[$dispatchIndex]->expiresAt)->toBe(10)
        ->and($worker->expiresAt)->toBeLessThanOrEqual(15);

    $refresh = $events->first(fn ($e) => str_contains($e->command, 'social-accounts:refresh-tokens'));
    expect($refresh->expiresAt)->toBe(60);
});

it('mencatat detak setiap kali scheduler berjalan', function () {
    Carbon::setTestNow(wib('2026-10-06 09:15:00'));
    $heartbeat = app(SchedulerHeartbeat::class);
    expect($heartbeat->lastRunAt())->toBeNull()->and($heartbeat->isStale())->toBeTrue();

    $this->artisan('scheduler:dispatch-due')->assertSuccessful();

    expect($heartbeat->lastRunAt()->equalTo(now()))->toBeTrue()
        ->and($heartbeat->isStale())->toBeFalse();

    Carbon::setTestNow(wib('2026-10-06 09:45:00'));
    expect($heartbeat->isStale())->toBeFalse();

    Carbon::setTestNow(wib('2026-10-06 09:46:30'));
    expect($heartbeat->isStale())->toBeTrue();
});

it('menampilkan peringatan scheduler di dashboard hanya untuk developer', function () {
    $developer = User::factory()->create();
    $developer->assignRole(Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']));
    $client = User::factory()->create();

    $this->actingAs($developer)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Scheduler belum pernah berjalan.');

    $this->actingAs($client)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Scheduler belum pernah berjalan.');

    app(SchedulerHeartbeat::class)->beat();

    $this->actingAs($developer)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Scheduler belum pernah berjalan.')
        ->assertDontSee('Scheduler tidak berjalan sejak');
});

it('slot 1 menit: menit bebas dipilih, jam tidak dibatasi, dan teks bantuan tidak ditampilkan', function () {
    config(['scheduler.slot_minutes' => 1]);
    Carbon::setTestNow(wib('2026-10-06 09:07:30'));
    $user = slotUser();
    slotAccount($user);

    expect(ScheduledPost::slotHint())->toBe('Pilih menit bebas. Postingan terbit pada menit yang dipilih, bisa mundur kurang dari satu menit.')
        ->and(ScheduledPost::nextSlot()->setTimezone(ScheduledPost::WIB)->format('H:i'))->toBe('09:08')
        ->and(ScheduledPost::isOnSlot(wib('2026-10-06 09:13:00')))->toBeTrue();

    $html = $this->actingAs($user)->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        // Slot 1 menit tidak perlu penjelasan di form; teksnya tetap tersedia dari slotHint().
        ->assertDontSee('Pilih menit bebas.')
        ->assertDontSee('kelipatan 1')
        ->assertSee('min="2026-10-06"', false)
        ->getContent();

    expect(composerConfig($html)['schedMinute'])->toBe('08');

    preg_match('/<select id="schedule-minute".*?<\/select>/s', $html, $minute);
    preg_match_all('/<option value="(\d{2})"/', $minute[0], $m);

    expect($m[1])->toHaveCount(60)
        ->and($m[1][0])->toBe('00')
        ->and($m[1][59])->toBe('59');
});

it('slot 1 menit: server menerima menit sembarang dan peringatan scheduler lebih cepat', function () {
    config(['scheduler.slot_minutes' => 1]);
    Storage::fake('public');
    $user = slotUser();
    $account = slotAccount($user);
    $tomorrow = now(ScheduledPost::WIB)->addDay()->format('Y-m-d');

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), [
        'caption' => 'Menit bebas.',
        'social_account_id' => $account->id,
        'media' => [UploadedFile::fake()->image('foto.jpg', 800, 800)],
        'scheduled_at' => "{$tomorrow}T09:07",
    ])->assertSessionHasNoErrors();

    expect(ScheduledPost::count())->toBe(1);

    // detak dianggap macet setelah dua slot + 1 menit = 3 menit
    $heartbeat = app(SchedulerHeartbeat::class);
    Carbon::setTestNow(wib('2026-10-06 09:00:00'));
    $heartbeat->beat();
    Carbon::setTestNow(wib('2026-10-06 09:02:30'));
    expect($heartbeat->isStale())->toBeFalse();
    Carbon::setTestNow(wib('2026-10-06 09:03:30'));
    expect($heartbeat->isStale())->toBeTrue();
});

it('worker antrean dari scheduler bisa dimatikan untuk VPS dengan Supervisor', function () {
    $commands = fn () => collect(app(Schedule::class)->events())->map(fn ($e) => $e->command);
    $provider = new SchedulerServiceProvider(app());

    $schedule = new Schedule;
    config(['scheduler.run_worker' => false]);
    (fn () => $this->configureSchedules($schedule))->call($provider);

    $registered = collect($schedule->events())->map(fn ($e) => $e->command);

    expect($registered->contains(fn ($c) => str_contains($c, 'scheduler:dispatch-due')))->toBeTrue()
        ->and($registered->contains(fn ($c) => str_contains($c, 'scheduler:prune-media')))->toBeTrue()
        ->and($registered->contains(fn ($c) => str_contains($c, 'queue:work')))->toBeFalse();
});
