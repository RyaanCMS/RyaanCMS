<?php

return [
    'current'        => env('APP_VERSION', '1.0.0'),

    /*
     | Update manifest — served from the public GitHub repo.
     | Any release pushed to RyaanCMS/RyaanCMS updates versions.json,
     | and every installed instance picks it up automatically.
     | Override with UPDATE_MANIFEST_URL in .env for private/self-hosted mirrors.
     */
    'manifest_url'   => env('UPDATE_MANIFEST_URL',
        'https://raw.githubusercontent.com/RyaanCMS/RyaanCMS/main/versions.json'),

    'check_interval' => 3600,
];
