<?php

return [
    'name' => 'SocialAccount',

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
        'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v26.0'),
        'status_poll_attempts' => 10,
        'status_poll_seconds' => 3,
        'refresh_before_days' => 10,
        // Daftar dipisah koma di .env. Kosong berarti memakai bawaan.
        'scopes' => array_values(array_filter(array_map('trim', explode(',',
            env('INSTAGRAM_SCOPES') ?: 'instagram_business_basic,instagram_business_content_publish'
        )))),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
        'graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v26.0'),
        // Daftar dipisah koma di .env. Kosong berarti memakai bawaan.
        // business_management: Halaman milik Business Portfolio baru muncul di me/accounts bila izin ini diminta.
        'scopes' => array_values(array_filter(array_map('trim', explode(',',
            env('FACEBOOK_SCOPES') ?: 'pages_show_list,pages_read_engagement,pages_manage_posts,business_management'
        )))),
    ],
];
