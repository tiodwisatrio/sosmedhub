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

    /*
    | Persetujuan akun baru. false (bawaan): pendaftar berstatus pending sampai disetujui admin.
    | true: pendaftar langsung disetujui dan masuk ke dashboard. Berguna sementara, misalnya agar
    | reviewer Meta bisa mendaftar sendiri. Matikan lagi setelahnya, karena siapa pun yang menemukan
    | situs bisa mendaftar dan memakai layanan.
    */
    'registration' => [
        'auto_approve' => (bool) env('REGISTRATION_AUTO_APPROVE', false),
    ],

    /*
    | Bahasa antarmuka. Teks sumber ditulis dalam Bahasa Indonesia (lihat CLAUDE.md), sehingga bahasa
    | "id" tidak butuh file terjemahan; bahasa lain memakai lang/{kode}.json dengan kunci teks Indonesia.
    | Pilihan pengguna disimpan di cookie, jadi berlaku juga untuk tamu (misalnya reviewer Meta).
    */
    'default_locale' => env('SOSMEDHUB_DEFAULT_LOCALE', 'id'),

    'locales' => [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ],
];
