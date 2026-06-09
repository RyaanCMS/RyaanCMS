<?php
/**
 * One-time migration runner — DELETE THIS FILE AFTER USE
 * Visit: https://yourdomain.com/run-migrations.php?token=ryaan2026
 */

if (($_GET['token'] ?? '') !== 'ryaan2026') {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:#991b1b">Access denied. Add ?token=ryaan2026 to the URL.</h2>');
}

$appRoot = is_dir(__DIR__ . '/vendor') ? __DIR__ : dirname(__DIR__);
require $appRoot . '/vendor/autoload.php';
$app = require_once $appRoot . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();
$db  = DB::connection()->getDatabaseName();

$tables = [
    'update_history' => "
        CREATE TABLE IF NOT EXISTS `update_history` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `version` varchar(30) NOT NULL,
            `type` varchar(20) NOT NULL DEFAULT 'core',
            `package_key` varchar(100) DEFAULT NULL,
            `status` enum('downloading','extracting','applying','migrating','success','failed') NOT NULL DEFAULT 'downloading',
            `changelog` text DEFAULT NULL,
            `error_message` text DEFAULT NULL,
            `started_at` timestamp NULL DEFAULT NULL,
            `completed_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `uh_type_pkg` (`type`,`package_key`),
            KEY `uh_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
];

echo '<!DOCTYPE html><html><head><title>Migration Runner</title>
<style>body{font-family:monospace;max-width:700px;margin:40px auto;padding:24px;background:#f8fafc;}
h2{color:#1e293b;} .ok{color:#15803d;} .err{color:#dc2626;} .row{padding:8px 0;border-bottom:1px solid #e2e8f0;}
</style></head><body><h2>RyaanCMS — Migration Runner</h2>';

$allOk = true;
foreach ($tables as $table => $sql) {
    echo "<div class='row'><b>{$table}</b>: ";
    try {
        $exists = DB::select("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = ? AND table_name = ?", [$db, $table]);
        if ($exists[0]->c > 0) {
            echo "<span class='ok'>✓ Already exists — skipped</span>";
        } else {
            DB::statement($sql);
            echo "<span class='ok'>✓ Created successfully</span>";
        }
    } catch (\Throwable $e) {
        echo "<span class='err'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span>";
        $allOk = false;
    }
    echo "</div>";
}

echo '<br>';
if ($allOk) {
    echo '<p class="ok" style="font-size:15px;font-weight:bold;">✓ All done! Now delete this file from your server.</p>';
    echo '<p style="color:#64748b;">Delete: <code>public_html/run-migrations.php</code></p>';
} else {
    echo '<p class="err">Some tables failed. Check the errors above.</p>';
}
echo '</body></html>';
