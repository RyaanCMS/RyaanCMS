<?php

return [
    'current'       => env('APP_VERSION', '1.0.0'),
    'manifest_url'  => env('UPDATE_MANIFEST_URL',
        'https://raw.githubusercontent.com/RyaanCMS/RyaanCMS/main/versions.json'),
    'check_interval' => 3600,
];
