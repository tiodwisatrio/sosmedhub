<?php

namespace Modules\Scheduler\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Scheduler\Models\CalendarEvent;

class CalendarEventSeeder extends Seeder
{
    /**
     * Hari besar bertanggal tetap, diisi untuk tahun-tahun ini.
     */
    private const FIXED_YEARS = [2026, 2027, 2028, 2029, 2030];

    /**
     * Libur nasional bertanggal tetap, diisi sama seperti hari besar umum.
     * Libur yang bergeser tiap tahun (Idulfitri, Nyepi, Waisak, dan seterusnya)
     * ada di MOVING_EVENTS karena baru ditetapkan lewat SKB 3 Menteri.
     */
    private const FIXED_EVENTS = [
        ['01-01', 'Tahun Baru Masehi', CalendarEvent::TYPE_HOLIDAY],
        ['05-01', 'Hari Buruh Internasional', CalendarEvent::TYPE_HOLIDAY],
        ['06-01', 'Hari Lahir Pancasila', CalendarEvent::TYPE_HOLIDAY],
        ['08-17', 'Hari Kemerdekaan Republik Indonesia', CalendarEvent::TYPE_HOLIDAY],
        ['12-25', 'Hari Raya Natal', CalendarEvent::TYPE_HOLIDAY],

        ['02-09', 'Hari Pers Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['03-08', 'Hari Perempuan Internasional', CalendarEvent::TYPE_OBSERVANCE],
        ['04-21', 'Hari Kartini', CalendarEvent::TYPE_OBSERVANCE],
        ['05-02', 'Hari Pendidikan Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['05-20', 'Hari Kebangkitan Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['06-05', 'Hari Lingkungan Hidup Sedunia', CalendarEvent::TYPE_OBSERVANCE],
        ['07-23', 'Hari Anak Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['10-01', 'Hari Kesaktian Pancasila', CalendarEvent::TYPE_OBSERVANCE],
        ['10-02', 'Hari Batik Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['10-28', 'Hari Sumpah Pemuda', CalendarEvent::TYPE_OBSERVANCE],
        ['11-10', 'Hari Pahlawan', CalendarEvent::TYPE_OBSERVANCE],
        ['11-25', 'Hari Guru Nasional', CalendarEvent::TYPE_OBSERVANCE],
        ['12-22', 'Hari Ibu', CalendarEvent::TYPE_OBSERVANCE],
    ];

    /**
     * Libur yang tanggalnya bergeser tiap tahun, mengikuti SKB 3 Menteri.
     * 2026: SKB No. 1497/2025, 2/2025, 5/2025. 2027: SKB No. 1205/2026, 3/2026, 2/2026.
     * Tambahkan tahun berikutnya di sini setelah SKB-nya terbit (biasanya September).
     */
    private const MOVING_EVENTS = [
        // 2026 (libur tetap seperti 1 Jan, 1 Mei, 1 Jun, 17 Agu, 25 Des ada di FIXED_EVENTS)
        ['2026-01-16', 'Isra Mikraj Nabi Muhammad SAW', CalendarEvent::TYPE_HOLIDAY],
        ['2026-02-17', 'Tahun Baru Imlek 2577 Kongzili', CalendarEvent::TYPE_HOLIDAY],
        ['2026-03-19', 'Hari Suci Nyepi (Tahun Baru Saka 1948)', CalendarEvent::TYPE_HOLIDAY],
        ['2026-03-21', 'Idulfitri 1447 H', CalendarEvent::TYPE_HOLIDAY],
        ['2026-03-22', 'Idulfitri 1447 H', CalendarEvent::TYPE_HOLIDAY],
        ['2026-04-03', 'Wafat Yesus Kristus', CalendarEvent::TYPE_HOLIDAY],
        ['2026-04-05', 'Kebangkitan Yesus Kristus (Paskah)', CalendarEvent::TYPE_HOLIDAY],
        ['2026-05-14', 'Kenaikan Yesus Kristus', CalendarEvent::TYPE_HOLIDAY],
        ['2026-05-27', 'Iduladha 1447 H', CalendarEvent::TYPE_HOLIDAY],
        ['2026-05-31', 'Hari Raya Waisak 2570 BE', CalendarEvent::TYPE_HOLIDAY],
        ['2026-06-16', 'Tahun Baru Islam 1448 H', CalendarEvent::TYPE_HOLIDAY],
        ['2026-08-25', 'Maulid Nabi Muhammad SAW', CalendarEvent::TYPE_HOLIDAY],

        ['2026-02-16', 'Cuti Bersama Tahun Baru Imlek', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-03-18', 'Cuti Bersama Hari Suci Nyepi', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-03-20', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-03-23', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-03-24', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-05-15', 'Cuti Bersama Kenaikan Yesus Kristus', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-05-28', 'Cuti Bersama Iduladha', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2026-12-24', 'Cuti Bersama Natal', CalendarEvent::TYPE_JOINT_LEAVE],

        // 2027
        ['2027-01-05', 'Isra Mikraj Nabi Muhammad SAW', CalendarEvent::TYPE_HOLIDAY],
        ['2027-02-06', 'Tahun Baru Imlek 2578 Kongzili', CalendarEvent::TYPE_HOLIDAY],
        ['2027-03-08', 'Hari Suci Nyepi (Tahun Baru Saka 1949)', CalendarEvent::TYPE_HOLIDAY],
        ['2027-03-10', 'Idulfitri 1448 H', CalendarEvent::TYPE_HOLIDAY],
        ['2027-03-11', 'Idulfitri 1448 H', CalendarEvent::TYPE_HOLIDAY],
        ['2027-03-26', 'Wafat Yesus Kristus', CalendarEvent::TYPE_HOLIDAY],
        ['2027-03-28', 'Kebangkitan Yesus Kristus (Paskah)', CalendarEvent::TYPE_HOLIDAY],
        ['2027-05-06', 'Kenaikan Yesus Kristus', CalendarEvent::TYPE_HOLIDAY],
        ['2027-05-17', 'Iduladha 1448 H', CalendarEvent::TYPE_HOLIDAY],
        ['2027-05-20', 'Hari Raya Waisak 2571 BE', CalendarEvent::TYPE_HOLIDAY],
        ['2027-06-06', 'Tahun Baru Islam 1449 H', CalendarEvent::TYPE_HOLIDAY],
        ['2027-08-15', 'Maulid Nabi Muhammad SAW', CalendarEvent::TYPE_HOLIDAY],
        ['2027-12-26', 'Isra Mikraj Nabi Muhammad SAW', CalendarEvent::TYPE_HOLIDAY],

        ['2027-02-05', 'Cuti Bersama Tahun Baru Imlek', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-03-09', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-03-12', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-03-15', 'Cuti Bersama Idulfitri', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-03-25', 'Cuti Bersama Wafat Yesus Kristus', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-05-18', 'Cuti Bersama Iduladha', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-05-19', 'Cuti Bersama Waisak', CalendarEvent::TYPE_JOINT_LEAVE],
        ['2027-12-24', 'Cuti Bersama Natal', CalendarEvent::TYPE_JOINT_LEAVE],
    ];

    public function run(): void
    {
        $rows = [];

        foreach (self::FIXED_YEARS as $year) {
            foreach (self::FIXED_EVENTS as [$monthDay, $name, $type]) {
                $rows[] = ['date' => "{$year}-{$monthDay}", 'name' => $name, 'type' => $type];
            }
        }

        foreach (self::MOVING_EVENTS as [$date, $name, $type]) {
            $rows[] = ['date' => $date, 'name' => $name, 'type' => $type];
        }

        $now = now();
        $rows = array_map(fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now], $rows);

        // Aman dijalankan berulang: baris yang sama hanya diperbarui.
        CalendarEvent::upsert($rows, ['date', 'name'], ['type', 'updated_at']);
    }
}
