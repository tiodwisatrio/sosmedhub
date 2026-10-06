<?php

return [
    'name' => 'SocialAccount',

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
        'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v22.0'),
        'status_poll_attempts' => 10,
        'status_poll_seconds' => 3,
        'refresh_before_days' => 10,
        'scopes' => [
            'instagram_business_basic',
            'instagram_business_content_publish',
        ],
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
        'graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v26.0'),
        'scopes' => [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            // Halaman milik Business Portfolio baru muncul di me/accounts bila izin ini diminta.
            'business_management',
        ],
    ],
];
