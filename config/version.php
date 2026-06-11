<?php

return [
    'current'        => env('APP_VERSION', '1.0.0'),

    /*
     | Update manifest endpoint.
     | Set UPDATE_MANIFEST_URL in .env to override.
     | Default: Ryaan Technologies update server (private, not GitHub).
     */
    'manifest_url'   => env('UPDATE_MANIFEST_URL', 'https://updates.ryaancms.com/versions.json'),

    'check_interval' => 3600,
];
