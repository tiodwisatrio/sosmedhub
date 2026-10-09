<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Memotong video ke satu rentang waktu dengan ffmpeg (untuk Story yang lebih panjang dari 60 detik).
 * Hasilnya dienkode ulang ke H.264 + AAC dengan faststart supaya potongan presisi dan diterima Instagram.
 *
 * Satu-satunya bagian aplikasi yang membutuhkan ffmpeg. Bila ffmpeg tidak terpasang, pemotongan
 * dimatikan dan video lebih dari 60 detik ditolak seperti biasa.
 */
class VideoTrimmer
{
    private const AVAILABLE_KEY = 'scheduler.ffmpeg-available';

    public function isAvailable(): bool
    {
        // Hanya hasil "ada" yang disimpan: setelah ffmpeg dipasang, fitur langsung aktif tanpa menunggu cache habis.
        if (Cache::get(self::AVAILABLE_KEY)) {
            return true;
        }

        try {
            $available = Process::timeout(10)->run([$this->binary(), '-version'])->successful();
        } catch (Throwable) {
            $available = false;
        }

        if ($available) {
            Cache::put(self::AVAILABLE_KEY, true, 3600);
        }

        return $available;
    }

    /**
     * @return string Path file sementara hasil potongan; pemanggil wajib menghapusnya.
     */
    public function trim(string $source, float $start, float $end): string
    {
        $output = sys_get_temp_dir().'/'.Str::uuid().'.mp4';

        $result = Process::timeout((int) config('scheduler.video.trim_timeout_seconds', 600))->run([
            $this->binary(), '-y', '-nostdin', '-loglevel', 'error',
            '-ss', $this->seconds($start), '-i', $source, '-t', $this->seconds($end - $start),
            '-map', '0:v:0', '-map', '0:a:0?',
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23', '-maxrate', '8M', '-bufsize', '16M', '-pix_fmt', 'yuv420p',
            '-c:a', 'aac', '-b:a', '128k',
            '-movflags', '+faststart',
            $output,
        ]);

        if ($result->failed() || ! is_file($output) || filesize($output) === 0) {
            @unlink($output);
            logger()->warning('ffmpeg gagal memotong video.', ['error' => Str::limit(trim($result->errorOutput()), 500)]);

            throw new RuntimeException('Video gagal dipotong di server. Coba lagi atau pilih rentang lain.');
        }

        return $output;
    }

    private function binary(): string
    {
        return (string) config('scheduler.video.ffmpeg', 'ffmpeg');
    }

    private function seconds(float $value): string
    {
        return number_format(max($value, 0), 3, '.', '');
    }
}
