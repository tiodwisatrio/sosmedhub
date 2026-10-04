<?php

return [
    /*
    | Akun developer awal yang dibuat oleh DatabaseSeeder. Nilainya dibaca dari .env
    | lewat config agar tetap terbaca saat config di-cache di produksi.
    */
    'developer' => [
        'name' => env('DEVELOPER_NAME', 'Developer'),
        'email' => env('DEVELOPER_EMAIL'),
        'password' => env('DEVELOPER_PASSWORD'),
    ],
];
