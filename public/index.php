<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Auto-detect app root (works for both flat cPanel and traditional structure) ─
// Flat:         public_html/ has storage/, vendor/, bootstrap/ alongside index.php
// Traditional:  public/ is separate, app root is one level up
$appRoot = is_dir(__DIR__ . '/vendor') ? __DIR__ : dirname(__DIR__);

// ── Auto-fix permissions (no manual chmod needed on shared hosting) ──────────────
$writableDirs = [
    $appRoot . '/storage',
    $appRoot . '/storage/app',
    $appRoot . '/storage/app/public',
    $appRoot . '/storage/framework',
    $appRoot . '/storage/framework/cache',
    $appRoot . '/storage/framework/cache/data',
    $appRoot . '/storage/framework/sessions',
    $appRoot . '/storage/framework/views',
    $appRoot . '/storage/logs',
    $appRoot . '/bootstrap/cache',
];
foreach ($writableDirs as $dir) {
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    if (!is_writable($dir)) { @chmod($dir, 0755); }
}

// ── Auto-installer: show installer if not yet installed ──────────────────────────
$installedFlag = $appRoot . '/storage/app/.installed';
$installScript = __DIR__ . '/install.php';

if (!file_exists($installedFlag) && file_exists($installScript)) {
    $uri     = $_SERVER['REQUEST_URI'] ?? '/';
    $isAsset = (bool) preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot|map)(\?.*)?$/i', $uri);
    if (!$isAsset && !str_contains($uri, '/install.php')) {
        require $installScript;
        exit;
    }
}

// ── Maintenance mode ─────────────────────────────────────────────────────────────
$maintenance = $appRoot . '/storage/framework/maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

// ── Bootstrap Laravel ────────────────────────────────────────────────────────────
require $appRoot . '/vendor/autoload.php';

(require_once $appRoot . '/bootstrap/app.php')
    ->handleRequest(Request::capture());
