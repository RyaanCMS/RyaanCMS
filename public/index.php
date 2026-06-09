<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Auto-installer: redirect if not yet installed ─────────────────────────────
if (!file_exists(__DIR__ . '/../storage/app/.installed') && file_exists(__DIR__ . '/install.php')) {
    // Only redirect for HTML requests (not assets/API)
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $isAsset = preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot|map)(\?.*)?$/i', $uri);
    if (!$isAsset && !str_contains($uri, '/install.php')) {
        require __DIR__ . '/install.php';
        exit;
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
