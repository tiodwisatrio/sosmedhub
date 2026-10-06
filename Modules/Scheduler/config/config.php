<?php

return [
    'name' => 'Scheduler',

    'timezone' => 'Asia/Jakarta',

    // Kirim email saat post berhasil terbit. Email gagal selalu dikirim.
    'notify_published' => env('SCHEDULER_NOTIFY_PUBLISHED', true),

    /*
    | Jarak antar slot jadwal dalam menit, harus sama dengan interval cron.
    | VPS (cron tiap menit): 1. Shared hosting Rumahweb Medium: 15 (cron tiap 15 menit).
    | Bawaan 15 sengaja yang paling aman: bila .env lupa diisi, jadwal tidak pernah lebih
    | rapat daripada cron.
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

    /*
    | Batas video. Meta mengizinkan Reels sampai 15 menit; batas di sini lebih ketat.
    | Story video: maksimal 60 detik dan 100MB. Reels: maksimal 300MB.
    */
    'video' => [
        'min_seconds' => 3,
        'story_max_seconds' => 60,
        'story_max_mb' => 100,
        'reel_max_seconds' => (int) env('SCHEDULER_REEL_MAX_SECONDS', 180),
        'reel_max_mb' => 300,
        'photo_max_mb' => 8,
        // Video diproses Instagram dalam menit; cek berkala tanpa menahan worker.
        'poll_delay_seconds' => (int) env('SCHEDULER_VIDEO_POLL_SECONDS', 30),
        'poll_max_minutes' => 10,
    ],

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
