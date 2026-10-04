<?php

return [
    'name' => 'Scheduler',

    'timezone' => 'Asia/Jakarta',

    // Kirim email saat post berhasil terbit. Email gagal selalu dikirim.
    'notify_published' => env('SCHEDULER_NOTIFY_PUBLISHED', true),

    /*
    | Jarak antar slot jadwal dalam menit, harus sama dengan interval cron.
    | Shared hosting Rumahweb Medium: 15 (cron tiap 15 menit). VPS: 1 atau 5.
    | Hanya pembagi 60 yang diterima; nilai lain dianggap 15.
    */
    'slot_minutes' => (int) env('SCHEDULER_SLOT_MINUTES', 15),

    /*
    | Jalankan worker antrean dari dalam scheduler (shared hosting, tanpa proses permanen).
    | Set false di VPS yang memakai Supervisor untuk queue:work.
    */
    'run_worker' => (bool) env('SCHEDULER_RUN_WORKER', true),

    // Batas waktu worker per putaran. Harus jauh di bawah interval cron dan batas proses host.
    'worker_max_seconds' => (int) env('SCHEDULER_WORKER_MAX_SECONDS', 240),

    'media' => [
        // Lebar versi terbit. Instagram menerima paling lebar 1440px dan menampilkan 1080px.
        'publish_max_width' => 1440,
        'publish_quality' => 85,
        'thumbnail_width' => 400,
        'thumbnail_quality' => 75,
        // Pengaman memori saat memproses foto. Imagick membuka JPEG pada skala kecil, jadi 50MP aman.
        'max_megapixels' => (int) env('SCHEDULER_MAX_MEGAPIXELS', 50),
        'imagick_memory_mb' => (int) env('SCHEDULER_IMAGICK_MEMORY_MB', 256),
        // Versi terbit dihapus sekian hari setelah postingan terbit; thumbnail tetap disimpan.
        'publish_retention_days' => (int) env('SCHEDULER_PUBLISH_RETENTION_DAYS', 30),
    ],
];
