{{-- Peringatan untuk developer bila cron scheduler tidak berjalan. --}}
@php
    $heartbeat = app(\Modules\Scheduler\Services\SchedulerHeartbeat::class);
    $lastRun = $heartbeat->lastRunAt();
    $slot = \Modules\Scheduler\Models\ScheduledPost::slotMinutes();
@endphp

@if (auth()->user()?->isDeveloper() && $heartbeat->isStale())
    <div class="mb-6 rounded-xl border border-warning/40 bg-warning-light px-5 py-4 text-sm text-warning-text" role="alert">
        @if ($lastRun)
            <p class="font-semibold">{{ __('Scheduler tidak berjalan sejak :date WIB.', ['date' => $lastRun->setTimezone(\Modules\Scheduler\Models\ScheduledPost::WIB)->locale(app()->getLocale())->translatedFormat('d M Y H:i')]) }}</p>
        @else
            <p class="font-semibold">{{ __('Scheduler belum pernah berjalan.') }}</p>
        @endif
        <p class="mt-1">
            {!! __('Postingan terjadwal tidak akan terbit sampai cron berjalan lagi. Cron seharusnya menjalankan :command setiap :slot menit. Periksa menu Cron Jobs di hosting.', [
                'command' => '<code class="rounded bg-white/60 px-1">php artisan schedule:run</code>',
                'slot' => e($slot),
            ]) !!}
        </p>
    </div>
@endif
