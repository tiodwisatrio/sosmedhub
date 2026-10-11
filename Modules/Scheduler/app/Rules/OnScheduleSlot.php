<?php

namespace Modules\Scheduler\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Modules\Scheduler\Models\ScheduledPost;
use Throwable;

/**
 * Jam terbit harus jatuh tepat di slot cron, supaya postingan diproses tepat waktu.
 */
class OnScheduleSlot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $time = Carbon::parse((string) $value, ScheduledPost::WIB);
        } catch (Throwable) {
            return; // format tanggal sudah ditangani aturan 'date'
        }

        if (! ScheduledPost::isOnSlot($time)) {
            $fail(__('Pilih jam dengan kelipatan :slot menit, misalnya :examples.', [
                'slot' => ScheduledPost::slotMinutes(),
                'examples' => ScheduledPost::slotExamples(),
            ]));
        }
    }
}
