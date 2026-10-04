<?php

return [
    'name' => 'Scheduler',

    'timezone' => 'Asia/Jakarta',

    // Kirim email saat post berhasil terbit. Email gagal selalu dikirim.
    'notify_published' => env('SCHEDULER_NOTIFY_PUBLISHED', true),
];
