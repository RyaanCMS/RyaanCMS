<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallController extends Controller
{
    // Step 1 — Welcome & Requirements
    public function welcome()
    {
        $checks = $this->runChecks();
        return view('install.wizard', ['step' => 1, 'checks' => $checks]);
    }

    // Step 2 — Database
    public function database()
    {
        return view('install.wizard', ['step' => 2]);
    }

    public function saveDatabase(Request $request)
    {
        $request->validate([
            'db_host'     => ['required', 'string'],
            'db_port'     => ['required', 'numeric'],
            'db_name'     => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        // Test connection first
        try {
            $dsn  = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}";
            $pdo  = new \PDO($dsn, $request->db_username, $request->db_password ?? '');
        } catch (\PDOException $e) {
            return back()->withErrors(['db_name' => 'Connection failed: '.$e->getMessage()])->withInput();
        }

        // Write to .env
        $this->setEnvValues([
            'DB_HOST'     => $request->db_host,
            'DB_PORT'     => $request->db_port,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password ?? '',
        ]);

        session(['install_db_ok' => true]);

        return redirect('/install/migrate');
    }

    // Step 3 — Migrate
    public function migrate()
    {
        return view('install.wizard', ['step' => 3]);
    }

    public function runMigrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            session(['install_migrate_ok' => true]);
            return redirect('/install/admin');
        } catch (\Throwable $e) {
            return back()->withErrors(['migrate' => 'Migration failed: '.$e->getMessage()]);
        }
    }

    // Step 4 — Admin account
    public function admin()
    {
        return view('install.wizard', ['step' => 4]);
    }

    public function saveAdmin(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'app_name'              => ['required', 'string', 'max:100'],
        ]);

        // Create admin user
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Set APP_NAME
        $this->setEnvValues([
            'APP_NAME' => '"'.$request->app_name.'"',
            'APP_URL'  => $request->app_url ?? config('app.url'),
        ]);

        // Mark as installed
        file_put_contents(storage_path('app/.installed'), now()->toISOString());

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return redirect('/install/complete');
    }

    // Step 5 — Done
    public function complete()
    {
        return view('install.wizard', ['step' => 5]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function runChecks(): array
    {
        return [
            ['name' => 'PHP Version (≥ 8.2)',     'ok' => version_compare(PHP_VERSION, '8.2.0', '>='), 'value' => PHP_VERSION],
            ['name' => 'PDO Extension',            'ok' => extension_loaded('pdo'),                    'value' => extension_loaded('pdo') ? 'Enabled' : 'Missing'],
            ['name' => 'PDO MySQL Driver',         'ok' => extension_loaded('pdo_mysql'),              'value' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Missing'],
            ['name' => 'Mbstring Extension',       'ok' => extension_loaded('mbstring'),               'value' => extension_loaded('mbstring') ? 'Enabled' : 'Missing'],
            ['name' => 'OpenSSL Extension',        'ok' => extension_loaded('openssl'),                'value' => extension_loaded('openssl') ? 'Enabled' : 'Missing'],
            ['name' => 'Tokenizer Extension',      'ok' => extension_loaded('tokenizer'),              'value' => extension_loaded('tokenizer') ? 'Enabled' : 'Missing'],
            ['name' => 'Fileinfo Extension',       'ok' => extension_loaded('fileinfo'),               'value' => extension_loaded('fileinfo') ? 'Enabled' : 'Missing'],
            ['name' => 'ZipArchive Extension',     'ok' => class_exists('ZipArchive'),                 'value' => class_exists('ZipArchive') ? 'Enabled' : 'Missing'],
            ['name' => 'storage/ Writable',        'ok' => is_writable(storage_path()),                'value' => is_writable(storage_path()) ? 'Writable' : 'Not Writable'],
            ['name' => 'bootstrap/cache/ Writable','ok' => is_writable(base_path('bootstrap/cache')), 'value' => is_writable(base_path('bootstrap/cache')) ? 'Writable' : 'Not Writable'],
            ['name' => '.env File Exists',         'ok' => file_exists(base_path('.env')),             'value' => file_exists(base_path('.env')) ? 'Found' : 'Missing'],
        ];
    }

    private function setEnvValues(array $values): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $envPath);
            } else {
                file_put_contents($envPath, '');
            }
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $value = str_replace('"', '', $value); // strip existing quotes for safety
            $quoted = str_contains($value, ' ') ? '"'.$value.'"' : $value;

            if (preg_match('/^'.$key.'=/m', $content)) {
                $content = preg_replace('/^'.$key.'=.*/m', $key.'='.$quoted, $content);
            } else {
                $content .= "\n".$key.'='.$quoted;
            }
        }

        file_put_contents($envPath, $content);
    }
}
