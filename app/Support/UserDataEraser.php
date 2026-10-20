<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Services\MediaDisk;

/**
 * Menghapus akun beserta seluruh datanya: akun sosial (token), jadwal, publikasi, dan media di database
 * (lewat cascade), lalu file foto/video di disk, avatar, sesi, dan token reset password.
 * Postingan yang sudah terbit di Instagram atau Facebook tidak disentuh.
 */
class UserDataEraser
{
    public function erase(User $user): void
    {
        $mediaPaths = ScheduledPostMedia::query()
            ->whereHas('scheduledPost', fn ($query) => $query->where('user_id', $user->id))
            ->get(['media_path', 'thumbnail_path'])
            ->flatMap(fn (ScheduledPostMedia $item) => [$item->media_path, $item->thumbnail_path])
            ->filter()
            ->all();

        $avatar = $user->avatar;

        DB::transaction(function () use ($user) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            $user->delete();
        });

        // File dihapus setelah database berhasil, supaya kegagalan hapus data tidak menyisakan baris tanpa file.
        MediaDisk::get()->delete($mediaPaths);

        if ($avatar) {
            Storage::disk('public')->delete($avatar);
        }
    }
}
