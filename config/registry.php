<?php

return [
    /*
     | Mode: 'client' (customer installation) or 'registry' (central server).
     | Set REGISTRY_MODE=registry in .env only on the central ryaancms.com server.
     */
    'mode' => env('REGISTRY_MODE', 'client'),

    /*
     | URL of the central registry. Customer installations pull packs from here.
     */
    'url' => env('REGISTRY_URL', 'https://registry.ryaancms.com'),

    /*
     | Customer license key — required to download premium packs.
     */
    'license_key' => env('REGISTRY_LICENSE_KEY'),

    /*
     | Auto-sync on boot (checks version headers, downloads only changed packs).
     */
    'auto_sync' => env('REGISTRY_AUTO_SYNC', false),

    /*
     | How often (seconds) to check for updates. 0 = every request (dev only).
     */
    'sync_interval' => env('REGISTRY_SYNC_INTERVAL', 86400), // 24h default
];
