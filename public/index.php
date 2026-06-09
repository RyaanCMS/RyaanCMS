<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>RyaanCMS Error</title>'
        . '<style>body{font-family:monospace;max-width:900px;margin:40px auto;padding:24px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px}</style></head><body>'
        . '<h2 style="color:#991b1b">Error: ' . htmlspecialchars($e->getMessage()) . '</h2>'
        . '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' <b>Line:</b> ' . $e->getLine() . '</p>'
        . '<pre style="background:#fff;padding:16px;border-radius:6px;overflow:auto;font-size:12px">' . htmlspecialchars($e->getTraceAsString()) . '</pre>'
        . '</body></html>';
    exit;
});

// ── PHP version gate ──────────────────────────────────────────────────────────────
if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    die('<!DOCTYPE html><html><head><title>PHP Version Error</title><style>body{font-family:sans-serif;max-width:600px;margin:80px auto;padding:24px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#991b1b}</style></head><body>'
        . '<h2>PHP 8.2+ Required</h2>'
        . '<p>Current PHP version: <strong>' . PHP_VERSION . '</strong></p>'
        . '<p>Please update PHP in <strong>cPanel &rarr; MultiPHP Manager</strong> — select PHP 8.2 or 8.3 for this domain, then reload this page.</p>'
        . '</body></html>');
}

// ── Auto-detect app root ──────────────────────────────────────────────────────────
$appRoot = is_dir(__DIR__ . '/vendor') ? __DIR__ : dirname(__DIR__);

// ── Auto-fix permissions ──────────────────────────────────────────────────────────
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

// ── Show installer if not yet installed ──────────────────────────────────────────
$installedFlag = $appRoot . '/storage/app/.installed';
$installScript = __DIR__ . '/install.php';

if (!file_exists($installedFlag) && file_exists($installScript)) {
    $uri     = $_SERVER['REQUEST_URI'] ?? '/';
    $isAsset = (bool) preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot|map)(\?.*)?$/i', $uri);
    if (!$isAsset && strpos($uri, '/install.php') === false) {
        header('Location: /install.php');
        exit;
    }
}

// ── Vendor existence check ────────────────────────────────────────────────────────
if (!file_exists($appRoot . '/vendor/autoload.php')) {
    http_response_code(500);
    die('<!DOCTYPE html><html><head><title>Setup Incomplete</title><style>body{font-family:sans-serif;max-width:600px;margin:80px auto;padding:24px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#991b1b}</style></head><body>'
        . '<h2>vendor/ directory missing</h2>'
        . '<p>Please re-download the latest ZIP from <a href="https://github.com/RyaanCMS/RyaanCMS/releases/latest">GitHub Releases</a> and re-extract.</p>'
        . '</body></html>');
}

// ── Maintenance mode ──────────────────────────────────────────────────────────────
$maintenance = $appRoot . '/storage/framework/maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────────
require $appRoot . '/vendor/autoload.php';

(require_once $appRoot . '/bootstrap/app.php')
    ->handleRequest(Request::capture());
