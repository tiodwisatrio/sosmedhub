<?php

use App\Models\User;
use App\Support\StoredMessage;
use Illuminate\Support\Facades\Notification;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Services\VideoSpec;
use Spatie\Permission\Models\Permission;

describe('pesan tersimpan di database', function () {
    it('tidak mengubah teks saat bahasa sumber aktif', function () {
        app()->setLocale('id');

        expect(StoredMessage::translate('Instagram gagal memproses media.'))->toBe('Instagram gagal memproses media.');
    });

    it('menerjemahkan pesan tetap saat bahasa Inggris aktif', function () {
        app()->setLocale('en');

        expect(StoredMessage::translate('Instagram gagal memproses media.'))->toBe('Instagram failed to process the media.');
    });

    it('menerjemahkan pesan berplaceholder dan mempertahankan nilainya', function () {
        app()->setLocale('en');

        expect(StoredMessage::translate('Instagram belum selesai memproses media dalam 10 menit. Coba jadwalkan ulang.'))
            ->toBe('Instagram has not finished processing the media within 10 minutes. Try rescheduling.')
            ->and(StoredMessage::translate('Instagram menolak permintaan: Invalid OAuth access token'))
            ->toBe('Instagram rejected the request: Invalid OAuth access token');
    });

    it('menerjemahkan pesan gabungan beberapa format beserta awalannya', function () {
        app()->setLocale('en');

        $stored = 'Feed: Instagram gagal memproses media. | Story: Akun Instagram tidak aktif atau token tidak tersedia.';

        expect(StoredMessage::translate($stored))
            ->toBe('Feed: Instagram failed to process the media. | Story: The Instagram account is not active or its token is unavailable.');
    });

    it('membiarkan pesan yang tidak dikenal apa adanya', function () {
        app()->setLocale('en');

        expect(StoredMessage::translate('Pesan lama yang tidak ada templatnya.'))->toBe('Pesan lama yang tidak ada templatnya.')
            ->and(StoredMessage::translate(null))->toBeNull()
            ->and(StoredMessage::translate(''))->toBe('');
    });

    it('dipakai model untuk menampilkan galat sesuai bahasa', function () {
        $post = ScheduledPost::factory()->create(['error_message' => 'Instagram gagal memproses media.']);
        $publication = new ScheduledPostPublication(['error_message' => 'Facebook gagal memproses Reels.']);

        app()->setLocale('id');
        expect($post->displayError())->toBe('Instagram gagal memproses media.');

        app()->setLocale('en');
        expect($post->displayError())->toBe('Instagram failed to process the media.')
            ->and($publication->displayError())->toBe('Facebook failed to process the Reel.');
    });
});

describe('bahasa pengguna', function () {
    it('memakai bahasa pilihan sendiri bila valid, selain itu bawaan', function () {
        expect(User::factory()->make(['locale' => 'en'])->preferredLocale())->toBe('en')
            ->and(User::factory()->make(['locale' => null])->preferredLocale())->toBe('id')
            ->and(User::factory()->make(['locale' => 'xx'])->preferredLocale())->toBe('id');
    });

    it('menyimpan pilihan bahasa di akun saat sudah login', function () {
        $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED, 'locale' => null]);

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();

        expect($user->fresh()->locale)->toBe('en');
    });

    it('memakai bahasa tersimpan di akun bila tidak ada cookie', function () {
        $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED, 'locale' => 'en']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertSee('<html lang="en"', false);
    });

    it('mengutamakan cookie perangkat daripada pilihan tersimpan di akun', function () {
        $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED, 'locale' => 'en']);

        $this->actingAs($user)->withCookie('locale', 'id')->get(route('admin.dashboard'))->assertSee('<html lang="id"', false);
    });
});

describe('email notifikasi', function () {
    it('dikirim dalam bahasa pilihan penerima', function () {
        Notification::fake();

        $user = User::factory()->create(['locale' => 'en']);
        $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_FAILED]);

        $user->notify(new PostFailedNotification($post));

        Notification::assertSentTo($user, PostFailedNotification::class,
            fn ($notification, $channels, $notifiable, $locale) => $locale === 'en');
    });

    it('menulis isi email dalam bahasa Inggris dengan galat yang diterjemahkan', function () {
        $user = User::factory()->create(['locale' => 'en']);
        $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_FAILED, 'caption' => 'Halo']);
        $post->publications()->delete();
        $post->publications()->create([
            'format' => ScheduledPost::FORMAT_FEED,
            'status' => ScheduledPostPublication::STATUS_FAILED,
            'error_message' => 'Instagram gagal memproses media.',
        ]);

        app()->setLocale('en');
        $mail = (new PostFailedNotification($post->fresh()))->toMail($user);
        $text = implode("\n", [$mail->subject, $mail->greeting, ...$mail->introLines, $mail->actionText]);

        expect($text)->toContain('Your Instagram post failed to publish')
            ->and($text)->toContain('Hello '.$user->name)
            ->and($text)->toContain('Cause: Instagram failed to process the media.')
            ->and($text)->toContain('Reschedule');
    });
});

describe('pesan validasi dan flash', function () {
    it('menulis pesan syarat video dalam bahasa aktif dengan pemisah desimal yang sesuai', function () {
        $info = ['duration' => 20.0, 'video_codec' => 'h264', 'audio_codec' => 'aac', 'has_audio' => true, 'fps' => 30.0];

        app()->setLocale('id');
        $id = VideoSpec::problem($info, 150 * 1048576, 'story');

        app()->setLocale('en');
        $en = VideoSpec::problem($info, (int) (1.5 * 1048576) + 100 * 1048576, 'story');

        expect($id)->toContain('melebihi batas 100 MB untuk Story')
            ->and($en)->toContain('exceeds the 100 MB limit for Story')
            ->and($en)->toContain('101.5');
    });

    it('menampilkan pesan flash dalam bahasa pilihan', function () {
        $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED]);
        $user->givePermissionTo(array_map(
            fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
            ['scheduler.view', 'scheduler.edit', 'scheduler.delete']
        ));
        $post = ScheduledPost::factory()->create([
            'user_id' => $user->id,
            'status' => ScheduledPost::STATUS_SCHEDULED,
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->withCookie('locale', 'en')
            ->delete(route('admin.scheduled-posts.destroy', $post))
            ->assertSessionHas('success', 'Post deleted successfully.');
    });

    it('memakai pesan validasi bawaan Laravel dalam Bahasa Indonesia', function () {
        app()->setLocale('id');

        $validator = validator(['email' => ''], ['email' => 'required']);

        expect($validator->errors()->first('email'))->toBe('email wajib diisi.');

        app()->setLocale('en');

        expect(validator(['email' => ''], ['email' => 'required'])->errors()->first('email'))->toBe('The email field is required.');
    });
});
