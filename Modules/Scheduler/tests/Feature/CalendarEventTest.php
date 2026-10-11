<?php

use Illuminate\Support\Carbon;
use Modules\Scheduler\Database\Seeders\CalendarEventSeeder;
use Modules\Scheduler\Models\CalendarEvent;

test('seeder mengisi libur nasional 2026 dan aman dijalankan berulang', function () {
    $this->seed(CalendarEventSeeder::class);
    $count = CalendarEvent::count();

    $this->seed(CalendarEventSeeder::class);

    expect(CalendarEvent::count())->toBe($count);

    // Idulfitri 2026 jatuh pada 21-22 Maret, bukan April.
    expect(CalendarEvent::whereDate('date', '2026-03-21')->value('name'))->toBe('Idulfitri 1447 H');
    expect(CalendarEvent::whereDate('date', '2026-08-17')->value('type'))->toBe(CalendarEvent::TYPE_HOLIDAY);
    expect(CalendarEvent::whereDate('date', '2026-03-20')->value('type'))->toBe(CalendarEvent::TYPE_JOINT_LEAVE);
    expect(CalendarEvent::whereDate('date', '2026-10-28')->value('type'))->toBe(CalendarEvent::TYPE_OBSERVANCE);
});

test('kalender menampilkan hari libur pada tanggalnya dan daftar hari penting setahun', function () {
    Carbon::setTestNow('2026-08-10 09:00:00');
    $this->seed(CalendarEventSeeder::class);

    $this->actingAs(schedulerUser())
        ->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->assertSee('Hari Kemerdekaan Republik Indonesia')
        ->assertSee('Hari Penting 2026')
        // Di luar bulan berjalan, tetap tampil karena daftarnya setahun penuh.
        ->assertSee('Hari Pahlawan')
        ->assertSee('Tahun Baru Imlek 2577 Kongzili');

    Carbon::setTestNow();
});

test('warna hari besar bergilir berurutan dalam setahun', function () {
    Carbon::setTestNow('2026-03-10 09:00:00');
    $this->seed(CalendarEventSeeder::class);

    $events = $this->actingAs(schedulerUser())
        ->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->viewData('yearEvents');

    // 1 Jan (Tahun Baru), 16 Jan (Isra Mikraj), 9 Feb (Pers), 16 Feb (Cuti Imlek), 17 Feb (Imlek), 8 Mar...
    expect($events[0]->hue)->toBe('red')
        ->and($events[1]->hue)->toBe('blue')
        ->and($events[2]->hue)->toBe('green')
        ->and($events[3]->hue)->toBe('purple')
        ->and($events[4]->hue)->toBe('orange');

    // Idulfitri 21-22 Maret jadi satu item berentang, bukan dua.
    $fitri = $events->where('name', 'Idulfitri 1447 H');
    expect($fitri)->toHaveCount(1)
        ->and($fitri->first()->date->toDateString())->toBe('2026-03-21')
        ->and($fitri->first()->end_date->toDateString())->toBe('2026-03-22');

    Carbon::setTestNow();
});
