<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RGIN — RyaanCMS Global Intelligence Network
    |--------------------------------------------------------------------------
    | Opt-in connection to the Central Intelligence Cloud.
    | When enabled, locally collected intelligence assets can be pushed
    | to the network and validated assets can be pulled down.
    |
    | Privacy guarantee: Only intelligence METADATA is ever transmitted.
    | Customer data, PII, passwords, and business secrets are never sent.
    |--------------------------------------------------------------------------
    */

    'enabled'  => env('RGIN_ENABLED', false),

    'cloud_url'=> env('RGIN_CLOUD_URL', ''),

    'api_key'  => env('RGIN_API_KEY', ''),

    'node_id'  => env('RGIN_NODE_ID', ''),

    'timeout'  => env('RGIN_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Auto Push
    |--------------------------------------------------------------------------
    | When true, export-ready assets are automatically pushed to the cloud
    | once per day via the scheduler. Set false to push manually only.
    |--------------------------------------------------------------------------
    */
    'auto_push'     => env('RGIN_AUTO_PUSH', false),
    'auto_push_time'=> env('RGIN_AUTO_PUSH_TIME', '03:00'),

    /*
    |--------------------------------------------------------------------------
    | Auto Sync
    |--------------------------------------------------------------------------
    | When true, validated assets are automatically pulled from the cloud
    | once per day and merged into the local intelligence registry.
    |--------------------------------------------------------------------------
    */
    'auto_sync'     => env('RGIN_AUTO_SYNC', false),
    'auto_sync_time'=> env('RGIN_AUTO_SYNC_TIME', '04:00'),

    /*
    |--------------------------------------------------------------------------
    | Quality Gate
    |--------------------------------------------------------------------------
    | Minimum extraction quality score required before an asset is considered
    | export-ready. Default 70. Raise to be more selective.
    |--------------------------------------------------------------------------
    */
    'min_quality' => env('RGIN_MIN_QUALITY', 70),

];
