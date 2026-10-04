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
];
