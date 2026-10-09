<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Services\VideoTrimmer;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => Storage::fake('public'));

/**
 * VideoTrimmer palsu: tanpa ffmpeg. Mencatat rentang yang diminta dan menghasilkan MP4 pendek yang sah.
 */
function trimFake(bool $available = true, ?array &$requests = null): void
{
    $requests = [];

    app()->instance(VideoTrimmer::class, new class($available, $requests) extends VideoTrimmer
    {
        public function __construct(private bool $available, private array &$requests) {}

        public function isAvailable(): bool
        {
            return $this->available;
        }

        public function trim(string $source, float $start, float $end): string
        {
            $this->requests[] = [$start, $end];

            return mp4File(fakeMp4(seconds: $end - $start));
        }
    });
}

function trimUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        ['scheduler.view', 'scheduler.create', 'scheduler.edit', 'scheduler.delete']
    ));

    return $user;
}

function trimPost(User $user, array $trim = [], float $seconds = 90): TestResponse
{
    $account = SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'instagram', 'provider_account_id' => 'trim-'.$user->id,
        'username' => 'trim_'.$user->id, 'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    return test()->actingAs($user)->post(route('admin.scheduled-posts.store'), array_filter([
        'social_account_id' => $account->id,
        'scheduled_at' => ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i'),
        'formats' => ['story'],
        'media_story' => [new UploadedFile(mp4File(fakeMp4(seconds: $seconds)), 'panjang.mp4', 'video/mp4', null, true)],
        'trim_story' => $trim ? [0 => $trim] : null,
    ]));
}

it('menolak video Story lebih dari 60 detik tanpa rentang potong', function () {
    trimFake();

    trimPost(trimUser())->assertSessionHasErrors('media_story.0');

    expect(ScheduledPost::query()->count())->toBe(0);
});

it('memotong video Story panjang sesuai rentang dan menyimpan hanya potongannya', function () {
    trimFake(requests: $requests);

    trimPost(trimUser(), ['start' => 12.5, 'end' => 72.5])->assertSessionHasNoErrors();

    $media = ScheduledPost::query()->firstOrFail()->media()->firstOrFail();

    expect($requests)->toBe([[12.5, 72.5]])
        ->and($media->format)->toBe('story')
        ->and($media->durationSeconds())->toBe(60.0);
    Storage::disk('public')->assertExists($media->media_path);
});

it('menolak rentang lebih dari 60 detik, kurang dari 3 detik, atau di luar durasi video', function (array $trim) {
    trimFake(requests: $requests);

    trimPost(trimUser(), $trim)->assertSessionHasErrors('media_story.0');

    expect($requests)->toBeEmpty()->and(ScheduledPost::query()->count())->toBe(0);
})->with([
    'terlalu panjang' => [['start' => 0, 'end' => 61]],
    'terlalu pendek' => [['start' => 10, 'end' => 11]],
    'melewati akhir video' => [['start' => 50, 'end' => 100]],
    'mulai negatif' => [['start' => -5, 'end' => 20]],
]);

it('mengabaikan rentang dan menolak durasi panjang bila ffmpeg tidak tersedia', function () {
    trimFake(available: false, requests: $requests);

    trimPost(trimUser(), ['start' => 0, 'end' => 60])->assertSessionHasErrors('media_story.0');

    expect($requests)->toBeEmpty();
});

it('memberi pesan jelas bila hasil potongan lebih besar dari batas Story', function () {
    trimFake();
    config(['scheduler.video.story_max_mb' => 0]);

    trimPost(trimUser(), ['start' => 0, 'end' => 30])
        ->assertSessionHasErrors('media_story');

    expect(ScheduledPost::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('form menawarkan pemotongan hanya saat ffmpeg tersedia', function (bool $available) {
    trimFake($available);
    $user = trimUser();
    SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'instagram', 'provider_account_id' => 'form-'.$user->id,
        'username' => 'form_'.$user->id, 'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('data-trim-editor', false)
        // @js menulis konfigurasi sebagai JSON ber-escape (\u0022 untuk tanda kutip).
        ->assertSee('trimEnabled\\u0022:'.($available ? 'true' : 'false'), false);
})->with([true, false]);

it('VideoTrimmer memanggil ffmpeg dengan rentang yang tepat dan mengenkode ulang ke H.264 + AAC', function () {
    Process::fake(function ($process) {
        // ffmpeg palsu menulis berkas keluaran (argumen terakhir).
        file_put_contents(end($process->command), 'mp4');

        return Process::result();
    });
    config(['scheduler.video.ffmpeg' => '/usr/bin/ffmpeg']);

    $output = (new VideoTrimmer)->trim('/tmp/sumber.mp4', 12.5, 72.5);

    Process::assertRan(function ($process) use ($output) {
        $command = $process->command;

        return $command[0] === '/usr/bin/ffmpeg'
            && array_search('-ss', $command) < array_search('-i', $command)
            && $command[array_search('-ss', $command) + 1] === '12.500'
            && $command[array_search('-t', $command) + 1] === '60.000'
            && in_array('libx264', $command, true)
            && in_array('aac', $command, true)
            && in_array('+faststart', $command, true)
            && end($command) === $output;
    });

    @unlink($output);
});

it('VideoTrimmer melempar galat dan tidak meninggalkan berkas bila ffmpeg gagal', function () {
    Process::fake(fn () => Process::result(errorOutput: 'Invalid data found', exitCode: 1));

    expect(fn () => (new VideoTrimmer)->trim('/tmp/rusak.mp4', 0, 10))
        ->toThrow(RuntimeException::class, 'gagal dipotong');
});
