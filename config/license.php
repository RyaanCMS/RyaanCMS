<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RyaanCMSCloud License Verification
    |--------------------------------------------------------------------------
    | When a user purchases RyaanCMS from the marketplace, they enter their
    | purchase code here. RyaanCMS calls RyaanCMSCloud to verify the code,
    | receives a license token, and stores it locally.
    |--------------------------------------------------------------------------
    */

    'cloud_url'  => env('LICENSE_CLOUD_URL', 'https://cloud.ryaancms.com'),

    'product_id' => env('LICENSE_PRODUCT_ID', 'ryaancms'),

    'timeout'    => env('LICENSE_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    | If the cloud is unreachable during a ping, the license stays valid for
    | this many hours before locking. Prevents false lockouts on shared hosting.
    |--------------------------------------------------------------------------
    */
    'grace_hours' => env('LICENSE_GRACE_HOURS', 72),

];
