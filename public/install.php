<?php
/**
 * RyaanCMS — Standalone Web Installer
 * Works without .env, without vendor/, before any Laravel bootstrap.
 * Place at: public/install.php  (auto-served when not installed)
 */

// Auto-detect: flat cPanel (vendor/ next to install.php) or traditional structure
define('BASE_PATH', is_dir(__DIR__ . '/vendor') ? __DIR__ : dirname(__DIR__));
define('INSTALLED_FLAG', BASE_PATH . '/storage/app/.installed');
define('INSTALLER_VERSION', '1.0.0');

// ── Already installed? ────────────────────────────────────────────────────────
if (file_exists(INSTALLED_FLAG)) {
    $envOk = file_exists(BASE_PATH . '/.env')
          && preg_match('/^APP_KEY=base64:.+/m', file_get_contents(BASE_PATH . '/.env'));
    if ($envOk) {
        header('Location: /');
        exit;
    }
    // .env missing or broken — clear stale flag and re-run installer
    @unlink(INSTALLED_FLAG);
}

// ── Handle AJAX actions ───────────────────────────────────────────────────────
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(handleAction($_POST['action'], $_POST));
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACTION HANDLERS
// ══════════════════════════════════════════════════════════════════════════════

function handleAction(string $action, array $data): array
{
    return match ($action) {
        'check_requirements' => checkRequirements(),
        'test_database'      => testDatabase($data),
        'run_install'        => runInstall($data),
        default              => ['success' => false, 'message' => 'Unknown action'],
    };
}

// ── Step 1: Requirements ──────────────────────────────────────────────────────
function checkRequirements(): array
{
    $checks  = [];
    $allGood = true;

    $required = [
        ['label' => 'PHP ≥ 8.2',               'ok' => version_compare(PHP_VERSION, '8.2.0', '>='), 'value' => PHP_VERSION],
        ['label' => 'PDO Extension',            'ok' => extension_loaded('pdo'),          'value' => 'PDO'],
        ['label' => 'PDO MySQL Driver',         'ok' => extension_loaded('pdo_mysql'),    'value' => 'pdo_mysql'],
        ['label' => 'Mbstring',                 'ok' => extension_loaded('mbstring'),     'value' => 'mbstring'],
        ['label' => 'OpenSSL',                  'ok' => extension_loaded('openssl'),      'value' => 'openssl'],
        ['label' => 'Tokenizer',                'ok' => extension_loaded('tokenizer'),    'value' => 'tokenizer'],
        ['label' => 'Fileinfo',                 'ok' => extension_loaded('fileinfo'),     'value' => 'fileinfo'],
        ['label' => 'cURL',                     'ok' => extension_loaded('curl'),         'value' => 'curl'],
        ['label' => 'ZipArchive',               'ok' => class_exists('ZipArchive'),       'value' => 'zip'],
        ['label' => 'storage/ Writable',        'ok' => is_writable(BASE_PATH.'/storage'),'value' => BASE_PATH.'/storage'],
        ['label' => 'bootstrap/cache/ Writable','ok' => is_writable(BASE_PATH.'/bootstrap/cache'),'value' => BASE_PATH.'/bootstrap/cache'],
        ['label' => 'vendor/ Directory',        'ok' => is_dir(BASE_PATH.'/vendor'),      'value' => BASE_PATH.'/vendor'],
        ['label' => '.env.example Exists',      'ok' => file_exists(BASE_PATH.'/.env.example'),'value' => '.env.example'],
    ];

    foreach ($required as $check) {
        if (!$check['ok']) $allGood = false;
        $checks[] = $check;
    }

    // Warnings (not blocking)
    $warnings = [];
    if (!is_dir(BASE_PATH . '/vendor')) {
        $warnings[] = 'vendor/ directory missing. Run <code>composer install</code> via SSH/Terminal before proceeding, or use the "Install Dependencies" button below.';
    }
    if (!file_exists(BASE_PATH . '/.env') && !file_exists(BASE_PATH . '/.env.example')) {
        $warnings[] = '.env.example is missing. Cannot auto-create .env file.';
    }

    // Try to fix permissions automatically
    foreach (['/storage', '/storage/app', '/storage/app/public', '/storage/framework', '/storage/framework/cache', '/storage/framework/sessions', '/storage/framework/views', '/storage/logs', '/bootstrap/cache'] as $dir) {
        $path = BASE_PATH . $dir;
        if (is_dir($path) && !is_writable($path)) {
            @chmod($path, 0775);
        }
    }

    return ['success' => $allGood, 'checks' => $checks, 'warnings' => $warnings];
}

// ── Step 2: Test DB connection ────────────────────────────────────────────────
function testDatabase(array $data): array
{
    $host = trim($data['db_host'] ?? 'localhost');
    $port = trim($data['db_port'] ?? '3306');
    $name = trim($data['db_name'] ?? '');
    $user = trim($data['db_user'] ?? '');
    $pass = $data['db_pass'] ?? '';

    if (!$name || !$user) {
        return ['success' => false, 'message' => 'Database name and username are required.'];
    }

    try {
        // Try connecting WITH database name first
        try {
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            return ['success' => true, 'message' => "Connected to database \"{$name}\" successfully."];
        } catch (PDOException $e) {
            // If DB doesn't exist, try creating it
            if (str_contains($e->getMessage(), 'Unknown database') || str_contains($e->getMessage(), "Can't connect")) {
                $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                return ['success' => true, 'message' => "Database \"{$name}\" created and connected successfully."];
            }
            throw $e;
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
    }
}

// ── Step 3: Run full installation ─────────────────────────────────────────────
function runInstall(array $data): array
{
    $steps  = [];
    $failed = false;

    // ── 1. Ensure vendor/ exists ──────────────────────────────────────────────
    if (!is_dir(BASE_PATH . '/vendor')) {
        // Try composer install if exec() available
        $composerPaths = [
            'composer', 'composer.phar',
            BASE_PATH . '/composer.phar',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
        ];
        $installed = false;
        if (function_exists('exec') || function_exists('shell_exec')) {
            foreach ($composerPaths as $c) {
                $test = function_exists('exec') ? exec("which {$c} 2>/dev/null") : @shell_exec("which {$c} 2>/dev/null");
                if ($test || str_starts_with($c, '/')) {
                    $cmd    = "cd " . escapeshellarg(BASE_PATH) . " && {$c} install --no-dev --optimize-autoloader --no-interaction 2>&1";
                    $output = function_exists('exec') ? exec($cmd) : shell_exec($cmd);
                    if (is_dir(BASE_PATH . '/vendor')) { $installed = true; break; }
                }
            }
        }
        if (!$installed) {
            return ['success' => false, 'steps' => [
                ['label' => 'Vendor Dependencies', 'ok' => false, 'message' => 'vendor/ directory missing and could not run composer install automatically. Please run <code>composer install --no-dev</code> via SSH, then retry.']
            ]];
        }
        $steps[] = ['label' => 'Installed Composer dependencies', 'ok' => true];
    } else {
        $steps[] = ['label' => 'Composer dependencies', 'ok' => true, 'message' => 'Already present'];
    }

    // ── 2. Create / update .env ───────────────────────────────────────────────
    $envPath  = BASE_PATH . '/.env';
    $examplePath = BASE_PATH . '/.env.example';

    if (!file_exists($envPath)) {
        if (file_exists($examplePath)) {
            copy($examplePath, $envPath);
        } else {
            file_put_contents($envPath, generateMinimalEnv());
        }
    }

    // Write all settings to .env
    $appName = trim($data['app_name'] ?? 'RyaanCMS');
    $appUrl  = rtrim(trim($data['app_url'] ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'))), '/');
    $env     = trim($data['app_env'] ?? 'production');

    $envValues = [
        'APP_NAME'     => '"' . str_replace('"', '', $appName) . '"',
        'APP_ENV'      => $env,
        'APP_DEBUG'    => $env === 'local' ? 'true' : 'false',
        'APP_URL'      => $appUrl,
        'DB_HOST'      => trim($data['db_host'] ?? 'localhost'),
        'DB_PORT'      => trim($data['db_port'] ?? '3306'),
        'DB_DATABASE'  => trim($data['db_name'] ?? ''),
        'DB_USERNAME'  => trim($data['db_user'] ?? ''),
        'DB_PASSWORD'  => trim($data['db_pass'] ?? ''),
    ];

    writeEnvValues($envValues);
    $steps[] = ['label' => 'Created .env configuration', 'ok' => true];

    // ── 3. Generate APP_KEY ───────────────────────────────────────────────────
    $envContent = file_get_contents($envPath);
    if (!preg_match('/^APP_KEY=base64:.+/m', $envContent)) {
        $key = 'base64:' . base64_encode(random_bytes(32));
        writeEnvValues(['APP_KEY' => $key]);
        $steps[] = ['label' => 'Generated application key', 'ok' => true];
    } else {
        $steps[] = ['label' => 'Application key', 'ok' => true, 'message' => 'Already set'];
    }

    // ── 4. Bootstrap Laravel & run migrations ─────────────────────────────────
    try {
        // Clear any cached config that might have wrong values
        @unlink(BASE_PATH . '/bootstrap/cache/config.php');
        @unlink(BASE_PATH . '/bootstrap/cache/routes-v7.php');
        @unlink(BASE_PATH . '/bootstrap/cache/packages.php');
        @unlink(BASE_PATH . '/bootstrap/cache/services.php');

        require_once BASE_PATH . '/vendor/autoload.php';
        $app    = require BASE_PATH . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $exitCode = $kernel->call('migrate', ['--force' => true]);
        $output   = $kernel->output();

        if ($exitCode !== 0 && !str_contains($output, 'Nothing to migrate')) {
            throw new \RuntimeException("Migration exited with code {$exitCode}. Output: " . substr($output, 0, 300));
        }
        $steps[] = ['label' => 'Database migrations completed', 'ok' => true];
    } catch (\Throwable $e) {
        $steps[] = ['label' => 'Database migrations', 'ok' => false, 'message' => $e->getMessage()];
        return ['success' => false, 'steps' => $steps];
    }

    // ── 5. Create super admin user ────────────────────────────────────────────
    try {
        $name     = trim($data['admin_name']  ?? 'Admin');
        $email    = trim($data['admin_email'] ?? '');
        $password = $data['admin_password']   ?? '';

        if (!$email || !$password) {
            throw new \RuntimeException('Admin email and password are required.');
        }

        // Check if user exists (idempotent)
        $existing = \App\Models\User::where('email', $email)->first();
        if ($existing) {
            $existing->update(['password' => \Illuminate\Support\Facades\Hash::make($password), 'role' => 'admin']);
            $steps[] = ['label' => 'Super admin account updated', 'ok' => true];
        } else {
            \App\Models\User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role'     => 'admin',
            ]);
            $steps[] = ['label' => 'Super admin account created', 'ok' => true];
        }
    } catch (\Throwable $e) {
        $steps[] = ['label' => 'Admin account', 'ok' => false, 'message' => $e->getMessage()];
        return ['success' => false, 'steps' => $steps];
    }

    // ── 6. Clear caches ───────────────────────────────────────────────────────
    try {
        $kernel->call('config:clear');
        $kernel->call('cache:clear');
        $kernel->call('view:clear');
        $steps[] = ['label' => 'Application caches cleared', 'ok' => true];
    } catch (\Throwable $e) {
        $steps[] = ['label' => 'Cache clear (non-fatal)', 'ok' => true, 'message' => 'Skipped: ' . $e->getMessage()];
    }

    // ── 7. Write installed flag ────────────────────────────────────────────────
    @file_put_contents(INSTALLED_FLAG, date('Y-m-d H:i:s') . ' UTC');
    $steps[] = ['label' => 'Installation locked', 'ok' => true];

    return ['success' => true, 'steps' => $steps];
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function writeEnvValues(array $values): void
{
    $envPath = BASE_PATH . '/.env';
    $content = file_exists($envPath) ? file_get_contents($envPath) : '';

    foreach ($values as $key => $value) {
        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
            $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $value, $content);
        } else {
            $content .= "\n" . $key . '=' . $value;
        }
    }

    file_put_contents($envPath, $content);
}

function generateMinimalEnv(): string
{
    return <<<ENV
APP_NAME="RyaanCMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ryaancms
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
ENV;
}

// Auto-detect app URL
$detectedUrl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install RyaanCMS</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'inter',system-ui,sans-serif;background:linear-gradient(135deg,#f0f4ff 0%,#f5f3ff 50%,#ecfdf5 100%);min-height:100vh;color:#1e293b}
.wrap{max-width:680px;margin:0 auto;padding:32px 16px}
.brand{text-align:center;margin-bottom:32px}
.brand-icon{width:56px;height:56px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 8px 24px rgba(99,102,241,.3)}
.brand-icon svg{width:30px;height:30px;color:#fff;stroke:currentColor}
.brand h1{font-size:24px;font-weight:800;color:#1e293b;letter-spacing:-.02em}
.brand p{font-size:13px;color:#64748b;margin-top:4px}
/* Steps */
.steps{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px}
.step{display:flex;flex-direction:column;align-items:center;gap:4px}
.step-circle{width:36px;height:36px;border-radius:50%;border:2px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#94a3b8;transition:all .3s}
.step.active .step-circle{background:linear-gradient(135deg,#6366f1,#8b5cf6);border-color:#6366f1;color:#fff;box-shadow:0 4px 12px rgba(99,102,241,.3)}
.step.done .step-circle{background:#ecfdf5;border-color:#a7f3d0;color:#059669}
.step-label{font-size:10px;font-weight:600;color:#94a3b8;letter-spacing:.02em}
.step.active .step-label{color:#6366f1}
.step.done .step-label{color:#059669}
.step-line{width:48px;height:2px;background:#e2e8f0;margin-bottom:14px;transition:background .3s}
.step-line.done{background:#a7f3d0}
/* Card */
.card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px}
.card-header{padding:20px 24px 18px;border-bottom:1px solid #f1f5f9}
.card-header h2{font-size:16px;font-weight:700;color:#1e293b}
.card-header p{font-size:12.5px;color:#64748b;margin-top:3px}
.card-body{padding:24px}
/* Form */
.field-group{margin-bottom:18px}
.field-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:6px}
.field-label .req{color:#f43f5e}
.field-input{width:100%;font-size:14px;color:#1e293b;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 13px;outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;font-family:inherit}
.field-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field-hint{font-size:11.5px;color:#94a3b8;margin-top:5px}
/* Checks */
.check-list{display:flex;flex-direction:column;gap:8px}
.check-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;font-size:13px}
.check-item.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.check-item.fail{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.check-item.warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
.check-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.check-item.ok .check-dot{background:#22c55e}
.check-item.fail .check-dot{background:#ef4444}
.check-item.warn .check-dot{background:#f59e0b}
/* Buttons */
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:11px;font-size:13.5px;font-weight:700;border:none;cursor:pointer;transition:all .15s;font-family:inherit}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.35)}
.btn-primary:hover{box-shadow:0 6px 20px rgba(99,102,241,.45);transform:translateY(-1px)}
.btn-primary:disabled{background:#e2e8f0;color:#94a3b8;box-shadow:none;transform:none;cursor:not-allowed}
.btn-outline{background:#fff;color:#64748b;border:1.5px solid #e2e8f0}
.btn-outline:hover{background:#f8fafc}
.btn-green{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 4px 14px rgba(16,185,129,.3)}
.btn-green:hover{box-shadow:0 6px 20px rgba(16,185,129,.4);transform:translateY(-1px)}
/* Footer */
.card-footer{padding:16px 24px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between}
/* Progress */
.progress-wrap{padding:16px 24px}
.progress-bar{height:4px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin-bottom:16px}
.progress-fill{height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:99px;transition:width .4s ease}
.install-step-list{display:flex;flex-direction:column;gap:8px}
.install-step{display:flex;align-items:center;gap:10px;font-size:13px;padding:8px 10px;border-radius:8px;border:1px solid transparent}
.install-step.pending{color:#94a3b8}
.install-step.running{background:#eef2ff;border-color:#c7d2fe;color:#4338ca}
.install-step.success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.install-step.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
.spin{animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
/* Alert */
.alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.alert-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
code{background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:11.5px;font-family:monospace}
/* Done */
.done-circle{width:80px;height:80px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:3px solid #a7f3d0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
/* Responsive */
@media(max-width:480px){.field-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h1>Install RyaanCMS</h1>
        <p>World's First AI Business Operating System Builder · v<?= INSTALLER_VERSION ?></p>
    </div>

    <!-- Step Indicator -->
    <div class="steps" id="stepIndicator">
        <?php
        $stepLabels = ['Requirements','Configure','Database','Admin','Installing','Done'];
        for ($i = 0; $i < count($stepLabels); $i++):
        ?>
        <?php if ($i > 0): ?><div class="step-line" id="line<?= $i ?>"></div><?php endif; ?>
        <div class="step active" id="step<?= $i ?>" data-index="<?= $i ?>">
            <div class="step-circle"><?= $i + 1 ?></div>
            <span class="step-label"><?= $stepLabels[$i] ?></span>
        </div>
        <?php endfor; ?>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 0 — REQUIREMENTS
    ═══════════════════════════════════════ -->
    <div id="panel-0">
        <div class="card">
            <div class="card-header">
                <h2>Server Requirements</h2>
                <p>Checking your server for required PHP extensions and writable directories</p>
            </div>
            <div class="card-body">
                <div id="req-loading" style="text-align:center;padding:24px 0;color:#94a3b8;font-size:13px;">
                    <svg class="spin" style="width:20px;height:20px;display:inline-block;margin-bottom:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <div>Checking requirements…</div>
                </div>
                <div id="req-list" class="check-list" style="display:none"></div>
                <div id="req-warnings" style="margin-top:12px"></div>
            </div>
            <div class="card-footer">
                <span style="font-size:12px;color:#94a3b8;">All checks must pass to continue</span>
                <button class="btn btn-primary" id="btn-req" disabled onclick="goTo(1)">
                    Continue →
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 1 — APP CONFIGURATION
    ═══════════════════════════════════════ -->
    <div id="panel-1" style="display:none">
        <div class="card">
            <div class="card-header">
                <h2>Application Configuration</h2>
                <p>Set your application name and URL</p>
            </div>
            <div class="card-body">
                <div class="field-group">
                    <label class="field-label">Application Name <span class="req">*</span></label>
                    <input type="text" class="field-input" id="app_name" value="RyaanCMS" placeholder="My Company CMS">
                </div>
                <div class="field-group">
                    <label class="field-label">Application URL <span class="req">*</span></label>
                    <input type="url" class="field-input" id="app_url" value="<?= htmlspecialchars($detectedUrl) ?>" placeholder="https://yourdomain.com">
                    <p class="field-hint">Auto-detected from your current domain. Update if needed.</p>
                </div>
                <div class="field-group">
                    <label class="field-label">Environment</label>
                    <select class="field-input" id="app_env">
                        <option value="production">Production (recommended)</option>
                        <option value="local">Local / Development</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-outline" onclick="goTo(0)">← Back</button>
                <button class="btn btn-primary" onclick="goTo(2)">Continue →</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 2 — DATABASE
    ═══════════════════════════════════════ -->
    <div id="panel-2" style="display:none">
        <div class="card">
            <div class="card-header">
                <h2>Database Setup</h2>
                <p>Enter your MySQL database credentials. The database will be created automatically if it doesn't exist.</p>
            </div>
            <div class="card-body">
                <div id="db-alert"></div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Database Host <span class="req">*</span></label>
                        <input type="text" class="field-input" id="db_host" value="localhost" placeholder="localhost">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Port</label>
                        <input type="text" class="field-input" id="db_port" value="3306" placeholder="3306">
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Database Name <span class="req">*</span></label>
                    <input type="text" class="field-input" id="db_name" placeholder="ryaancms">
                    <p class="field-hint">Will be created automatically if it doesn't exist (if user has CREATE privilege).</p>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Username <span class="req">*</span></label>
                        <input type="text" class="field-input" id="db_user" placeholder="root">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Password</label>
                        <input type="password" class="field-input" id="db_pass" placeholder="(leave blank if none)">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-outline" onclick="goTo(1)">← Back</button>
                <button class="btn btn-primary" id="btn-dbtest" onclick="testDb()">
                    <svg id="db-spin" class="spin" style="width:14px;height:14px;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Test & Continue →
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 3 — ADMIN ACCOUNT
    ═══════════════════════════════════════ -->
    <div id="panel-3" style="display:none">
        <div class="card">
            <div class="card-header">
                <h2>Super Admin Account</h2>
                <p>Create the primary administrator account for your RyaanCMS installation</p>
            </div>
            <div class="card-body">
                <div id="admin-alert"></div>
                <div class="field-group">
                    <label class="field-label">Full Name <span class="req">*</span></label>
                    <input type="text" class="field-input" id="admin_name" placeholder="John Smith">
                </div>
                <div class="field-group">
                    <label class="field-label">Email Address <span class="req">*</span></label>
                    <input type="email" class="field-input" id="admin_email" placeholder="admin@yourdomain.com">
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Password <span class="req">*</span></label>
                        <input type="password" class="field-input" id="admin_password" placeholder="Min. 8 characters" minlength="8">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Confirm Password <span class="req">*</span></label>
                        <input type="password" class="field-input" id="admin_password2" placeholder="Re-enter password">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-outline" onclick="goTo(2)">← Back</button>
                <button class="btn btn-primary" onclick="validateAdmin()">Install Now →</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 4 — INSTALLING
    ═══════════════════════════════════════ -->
    <div id="panel-4" style="display:none">
        <div class="card">
            <div class="card-header">
                <h2>Installing RyaanCMS</h2>
                <p>Please wait — this may take 30–60 seconds on first run</p>
            </div>
            <div class="progress-wrap">
                <div class="progress-bar"><div class="progress-fill" id="progress-fill" style="width:0%"></div></div>
                <div class="install-step-list" id="install-steps">
                    <div class="install-step pending" id="ist-0">Setting up environment…</div>
                    <div class="install-step pending" id="ist-1">Generating application key…</div>
                    <div class="install-step pending" id="ist-2">Running database migrations…</div>
                    <div class="install-step pending" id="ist-3">Creating admin account…</div>
                    <div class="install-step pending" id="ist-4">Clearing caches…</div>
                    <div class="install-step pending" id="ist-5">Finalizing installation…</div>
                </div>
                <div id="install-error" style="margin-top:16px;display:none"></div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         STEP 5 — DONE
    ═══════════════════════════════════════ -->
    <div id="panel-5" style="display:none">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px 24px">
                <div class="done-circle">
                    <svg style="width:40px;height:40px;color:#059669;stroke:currentColor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 style="font-size:22px;font-weight:800;color:#1e293b;margin-bottom:8px;">Installation Complete!</h2>
                <p style="color:#64748b;font-size:14px;max-width:440px;margin:0 auto 28px;">RyaanCMS has been installed successfully. Log in with your admin credentials to get started.</p>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:24px;text-align:left;max-width:360px;margin-left:auto;margin-right:auto;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">What's next?</p>
                    <ul style="font-size:13px;color:#475569;line-height:1.8;list-style:none;">
                        <li>✅ Database migrated</li>
                        <li>✅ Admin account created</li>
                        <li>✅ App key generated</li>
                        <li>⚡ Add an AI provider key in Settings → AI Providers to enable code generation</li>
                    </ul>
                </div>

                <a id="login-link" href="/login"
                   style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 16px rgba(99,102,241,.35);">
                    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Go to Login
                </a>
            </div>
        </div>
    </div>

</div><!-- /.wrap -->

<script>
let currentStep = 0;
let installData = {};

// ── Navigation ─────────────────────────────────────────────────────────────
function goTo(step) {
    document.getElementById('panel-' + currentStep).style.display = 'none';
    document.getElementById('panel-' + step).style.display = 'block';
    currentStep = step;
    updateStepIndicator(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepIndicator(active) {
    for (let i = 0; i <= 5; i++) {
        const el   = document.getElementById('step' + i);
        const line = document.getElementById('line' + i);
        el.className = 'step ' + (i < active ? 'done' : (i === active ? 'active' : ''));
        if (i > 0 && line) line.className = 'step-line ' + (i <= active ? 'done' : '');

        const circle = el.querySelector('.step-circle');
        if (i < active) {
            circle.innerHTML = '<svg style="width:14px;height:14px;stroke:currentColor" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } else {
            circle.textContent = i + 1;
        }
    }
}

// ── Step 0: Requirements ───────────────────────────────────────────────────
async function checkRequirements() {
    const res  = await post({ action: 'check_requirements' });
    const list = document.getElementById('req-list');
    const warn = document.getElementById('req-warnings');
    document.getElementById('req-loading').style.display = 'none';
    list.style.display = 'flex';

    list.innerHTML = res.checks.map(c => `
        <div class="check-item ${c.ok ? 'ok' : 'fail'}">
            <span class="check-dot"></span>
            <span style="flex:1">${c.label}</span>
            <span style="font-size:11.5px;font-family:monospace;opacity:.8">${c.value}</span>
            <span style="font-size:11px;font-weight:700">${c.ok ? '✓' : '✗'}</span>
        </div>
    `).join('');

    if (res.warnings && res.warnings.length) {
        warn.innerHTML = res.warnings.map(w =>
            `<div class="alert alert-warn" style="margin-top:8px">${w}</div>`
        ).join('');
    }

    if (res.success) {
        document.getElementById('btn-req').disabled = false;
    }
}

// ── Step 2: Test Database ──────────────────────────────────────────────────
async function testDb() {
    const btn = document.getElementById('btn-dbtest');
    const spin = document.getElementById('db-spin');
    const alert = document.getElementById('db-alert');

    btn.disabled = true;
    spin.style.display = 'inline-block';
    alert.innerHTML = '';

    const res = await post({
        action:  'test_database',
        db_host: document.getElementById('db_host').value,
        db_port: document.getElementById('db_port').value,
        db_name: document.getElementById('db_name').value,
        db_user: document.getElementById('db_user').value,
        db_pass: document.getElementById('db_pass').value,
    });

    btn.disabled = false;
    spin.style.display = 'none';

    if (res.success) {
        alert.innerHTML = `<div class="alert alert-success">✓ ${res.message}</div>`;
        setTimeout(() => goTo(3), 800);
    } else {
        alert.innerHTML = `<div class="alert alert-error">✗ ${res.message}</div>`;
    }
}

// ── Step 3: Validate Admin ─────────────────────────────────────────────────
function validateAdmin() {
    const alert = document.getElementById('admin-alert');
    const name  = document.getElementById('admin_name').value.trim();
    const email = document.getElementById('admin_email').value.trim();
    const pw    = document.getElementById('admin_password').value;
    const pw2   = document.getElementById('admin_password2').value;

    if (!name || !email || !pw) {
        alert.innerHTML = '<div class="alert alert-error">All fields are required.</div>';
        return;
    }
    if (pw.length < 8) {
        alert.innerHTML = '<div class="alert alert-error">Password must be at least 8 characters.</div>';
        return;
    }
    if (pw !== pw2) {
        alert.innerHTML = '<div class="alert alert-error">Passwords do not match.</div>';
        return;
    }

    // Store and proceed
    installData.admin_name     = name;
    installData.admin_email    = email;
    installData.admin_password = pw;
    alert.innerHTML = '';
    goTo(4);
    runInstallation();
}

// ── Step 4: Run Installation ───────────────────────────────────────────────
const installStepLabels = [
    'Setting up environment',
    'Generating application key',
    'Running database migrations',
    'Creating admin account',
    'Clearing caches',
    'Finalizing installation',
];

async function runInstallation() {
    // Animate steps
    animateInstallSteps(0);

    const res = await post({
        action:         'run_install',
        app_name:       document.getElementById('app_name').value,
        app_url:        document.getElementById('app_url').value,
        app_env:        document.getElementById('app_env').value,
        db_host:        document.getElementById('db_host').value,
        db_port:        document.getElementById('db_port').value,
        db_name:        document.getElementById('db_name').value,
        db_user:        document.getElementById('db_user').value,
        db_pass:        document.getElementById('db_pass').value,
        admin_name:     installData.admin_name,
        admin_email:    installData.admin_email,
        admin_password: installData.admin_password,
    });

    // Render results
    if (res.steps) {
        res.steps.forEach((s, i) => {
            const el = document.getElementById('ist-' + i);
            if (!el) return;
            el.className = 'install-step ' + (s.ok ? 'success' : 'error');
            el.innerHTML = (s.ok ? '✓ ' : '✗ ') + s.label + (s.message ? ' <span style="opacity:.7;font-size:11.5px;">— ' + s.message + '</span>' : '');
        });
    }

    document.getElementById('progress-fill').style.width = '100%';

    if (res.success) {
        setTimeout(() => {
            const appUrl = document.getElementById('app_url').value.replace(/\/$/, '');
            document.getElementById('login-link').href = appUrl + '/login';
            goTo(5);
        }, 1000);
    } else {
        const lastFailed = res.steps ? res.steps.find(s => !s.ok) : null;
        document.getElementById('install-error').style.display = 'block';
        document.getElementById('install-error').innerHTML =
            `<div class="alert alert-error"><strong>Installation failed:</strong> ${lastFailed?.message || res.message || 'Unknown error'}<br><br>
            <button class="btn btn-outline" style="margin-top:8px" onclick="goTo(2)">← Fix & Retry</button></div>`;
    }
}

let animTimer = null;
function animateInstallSteps(idx) {
    if (idx >= 6) return;
    clearTimeout(animTimer);
    for (let i = 0; i < idx; i++) {
        const el = document.getElementById('ist-' + i);
        if (el && el.className.includes('pending') || el?.className.includes('running')) {
            el.className = 'install-step success';
            el.innerHTML = '✓ ' + installStepLabels[i];
        }
    }
    const cur = document.getElementById('ist-' + idx);
    if (cur) {
        cur.className = 'install-step running';
        cur.innerHTML = '<svg class="spin" style="width:12px;height:12px;display:inline;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>' + installStepLabels[idx] + '…';
    }
    document.getElementById('progress-fill').style.width = ((idx / 6) * 90) + '%';
    animTimer = setTimeout(() => animateInstallSteps(idx + 1), 4000);
}

// ── Fetch helper ───────────────────────────────────────────────────────────
async function post(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch(window.location.href, { method: 'POST', body: fd });
    return res.json();
}

// ── Boot ───────────────────────────────────────────────────────────────────
updateStepIndicator(0);
checkRequirements();
</script>
</body>
</html>
