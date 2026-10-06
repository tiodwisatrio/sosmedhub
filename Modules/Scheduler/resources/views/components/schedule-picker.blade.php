@props(['min'])

{{--
    Pemilih tanggal dan jam terbit: tanggal, jam 00-23, dan menit per slot cron, dipisah dan
    selalu 24 jam (input datetime-local mengikuti locale browser, jadi bisa muncul AM/PM).

    $min: waktu paling awal yang boleh dipilih, format Y-m-d\TH:i (WIB).

    Dipakai di dalam x-data yang menyediakan: schedDate ('Y-m-d'), schedHour ('00'-'23'),
    schedMinute, scheduled_at (getter gabungan), dan schedulePreview. Nilai terkirim lewat
    input tersembunyi bernama scheduled_at, sama seperti sebelumnya.
--}}

@php
    $slot = \Modules\Scheduler\Models\ScheduledPost::slotMinutes();
    $pad = fn (int $n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $hours = array_map($pad, range(0, 23));
    $minutes = array_map($pad, range(0, 59, $slot));
    $minDate = substr($min, 0, 10);
    $minLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m-d\TH:i', $min)->locale('id')->translatedFormat('j F Y, H.i');
    $hasError = $errors->has('scheduled_at');
    $field = 'w-full rounded-md border px-3 py-2 text-sm text-slate-800 bg-white focus:outline-none focus:ring-1 transition-colors duration-150 '
        . ($hasError
            ? 'border-danger focus:border-danger focus:ring-danger/20'
            : 'border-border focus:border-primary focus:ring-primary/20');
@endphp

<div role="group" aria-labelledby="schedule-label">
    <p id="schedule-label" class="block text-sm font-medium text-slate-700 mb-1.5">
        Tanggal & Jam Terbit <span class="text-danger ml-0.5">*</span>
    </p>

    <input type="hidden" name="scheduled_at" :value="scheduled_at">

    <div class="grid grid-cols-[minmax(0,1fr)_5rem_auto_5rem] items-end gap-2">
        <div>
            <label for="schedule-date" class="block text-xs text-slate-500 mb-1">Tanggal</label>
            <input id="schedule-date" type="date" x-model="schedDate" min="{{ $minDate }}" required class="{{ $field }}">
        </div>

        <div>
            <label for="schedule-hour" class="block text-xs text-slate-500 mb-1">Jam</label>
            <select id="schedule-hour" x-model="schedHour" class="{{ $field }} tabular-nums">
                @foreach ($hours as $hour)
                    <option value="{{ $hour }}">{{ $hour }}</option>
                @endforeach
            </select>
        </div>

        <span aria-hidden="true" class="pb-2 text-slate-400">:</span>

        <div>
            <label for="schedule-minute" class="block text-xs text-slate-500 mb-1">Menit</label>
            <select id="schedule-minute" x-model="schedMinute" class="{{ $field }} tabular-nums">
                @foreach ($minutes as $minute)
                    <option value="{{ $minute }}">{{ $minute }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <p class="mt-2 text-xs font-medium text-slate-600" x-text="schedulePreview"></p>

    <p x-cloak x-show="scheduled_at && scheduled_at < @js($min)" role="alert" class="mt-1.5 text-xs text-danger">
        Waktu ini sudah lewat. Pilih setelah {{ $minLabel }} WIB.
    </p>

    @error('scheduled_at')
        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
    @enderror

    <p class="mt-1.5 text-xs text-slate-400">
        {{ \Modules\Scheduler\Models\ScheduledPost::slotHint() }}
    </p>
</div>
