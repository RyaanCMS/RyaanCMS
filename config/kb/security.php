<?php
/**
 * Security Knowledge Base
 * Most AI builders are weak here. These rules MUST be applied automatically.
 * Level 2: Architecture Knowledge — Security layer
 */
return [

    // ── ALWAYS-APPLY rules (zero exceptions) ─────────────────────────────────
    'always_apply' => [
        'mass_assignment' => 'NEVER use $fillable = ["*"]. Always explicit fillable or guarded.',
        'authorization'   => 'Every controller method MUST call $this->authorize() or use Policy. No exceptions.',
        'input_validation'=> 'ALL user input validated via Form Request class. Never validate in controller directly.',
        'sql_injection'   => 'Use Eloquent or query builder with bindings. NEVER string-concatenate SQL.',
        'xss'             => 'Always use {{ $var }} in Blade (auto-escaped). {!! !!} only for explicitly trusted HTML.',
        'csrf'            => '@csrf in ALL forms. API routes use Sanctum token or stateless auth.',
        'file_upload'     => 'Validate MIME type + extension + max size. Store outside public/. Never trust client filename.',
        'env_secrets'     => 'NEVER hardcode API keys, passwords, tokens in code. Always .env + config().',
        'debug_mode'      => 'APP_DEBUG must be false in production. Log errors to storage/logs, never to browser.',
        'https'           => 'Force HTTPS in production. config/session.php: secure = true, same_site = lax.',
        'password_hash'   => 'Always Hash::make() for passwords. NEVER md5/sha1. Use bcrypt (cost ≥ 12) or argon2id.',
    ],

    // ── SQL Injection Prevention ───────────────────────────────────────────────
    'sql_injection' => [
        'good'  => "User::where('email', \$request->email)->first()",
        'bad'   => "DB::select(\"SELECT * FROM users WHERE email = '{\$request->email}'\")",
        'rules' => ['Use Eloquent ORM', 'Use query builder bindings', 'Sanitize raw query inputs'],
    ],

    // ── XSS Prevention ────────────────────────────────────────────────────────
    'xss' => [
        'blade_safe'   => '{{ $var }} — auto HTML-escaped',
        'blade_unsafe' => '{!! $var !!} — only for trusted admin content',
        'js_safe'      => '@json($data) or JSON.parse() with server-side sanitization',
        'rules'        => ['Escape all output', 'Use CSP headers', 'Sanitize HTML if rich text stored'],
    ],

    // ── Authentication Security ───────────────────────────────────────────────
    'authentication' => [
        'rate_limit'     => 'Throttle login: 5 attempts per minute per IP+email',
        'remember_token' => 'Regenerate session after login (session()->regenerate())',
        'logout'         => 'Invalidate session and regenerate token on logout',
        'two_factor'     => 'Recommend 2FA for admin roles (Laravel Fortify built-in)',
        'session'        => 'session.lifetime = 120 min, cookie secure + httponly + samesite',
        'api_tokens'     => 'Sanctum: hash tokens in DB, set expiry, scope tokens',
    ],

    // ── Authorization Security ─────────────────────────────────────────────────
    'authorization' => [
        'pattern'        => 'Gate + Policy per model. Never role-check in controllers directly.',
        'row_ownership'  => 'Always check: $this->authorize("update", $model) — prevents IDOR attacks',
        'admin_routes'   => "Protect with middleware('role:admin') or ability check in Gate",
        'api_endpoints'  => 'Every API route must have auth:sanctum + appropriate policy',
        'idor_prevention'=> 'Never expose sequential IDs in URLs for sensitive resources — use UUID or encrypted ID',
    ],

    // ── File Upload Security ───────────────────────────────────────────────────
    'file_upload' => [
        'validation'  => "validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'])",
        'storage'     => 'Store in storage/app/private/ — never in public/ directly',
        'serve'       => 'Use signed URL or controller to serve private files',
        'rename'      => 'Always rename to random filename (Str::uuid()) — never keep original name',
        'antivirus'   => 'For user-generated content: consider ClamAV scan or VirusTotal API',
    ],

    // ── API Security ──────────────────────────────────────────────────────────
    'api' => [
        'rate_limiting'  => "throttle:60,1 for public, throttle:600,1 for authenticated",
        'cors'           => 'Configure config/cors.php — never allow_origins = ["*"] in production',
        'sensitive_data' => 'Never return password, remember_token, or full card details in API response',
        'versioning'     => 'Prefix all APIs: /api/v1/ — allows breaking changes without disruption',
        'idempotency'    => 'POST endpoints for payments: implement idempotency key',
    ],

    // ── Data Protection ───────────────────────────────────────────────────────
    'data_protection' => [
        'pii_encryption' => 'Encrypt PII (NID, passport, full address) — use Laravel encrypted cast',
        'soft_delete'    => 'Use SoftDeletes for user data (GDPR right to erasure — make anonymization easy)',
        'audit_log'      => 'Log: who did what, when, from which IP — for every sensitive operation',
        'backup'         => 'Automated daily DB backup. Test restore quarterly.',
    ],

    // ── Dependency Security ────────────────────────────────────────────────────
    'dependencies' => [
        'rule'     => 'Run composer audit and npm audit regularly',
        'packages' => 'Only use packages with recent updates and community maintenance',
        'env'      => 'Different APP_KEY, DB credentials, API keys per environment',
    ],

    // ── Security Headers ─────────────────────────────────────────────────────
    'headers' => [
        'X-Frame-Options'         => 'DENY',
        'X-Content-Type-Options'  => 'nosniff',
        'X-XSS-Protection'        => '1; mode=block',
        'Strict-Transport-Security'=> 'max-age=31536000; includeSubDomains',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'apply_via'               => 'Middleware\\SecurityHeaders — apply globally',
    ],

    // ── OWASP Top 10 Checklist ─────────────────────────────────────────────────
    'owasp_checklist' => [
        'A01_broken_access_control'   => 'Use Policies + Gates. Check ownership on every request.',
        'A02_cryptographic_failures'  => 'HTTPS always. Encrypted PII. bcrypt passwords.',
        'A03_injection'               => 'Eloquent ORM. Parameterized queries. Validate all input.',
        'A04_insecure_design'         => 'Threat model at design phase. Rate limits. Account lockout.',
        'A05_security_misconfiguration'=> 'APP_DEBUG=false. Remove debug routes. Proper CORS.',
        'A06_vulnerable_components'   => 'composer audit. Keep dependencies updated.',
        'A07_auth_failures'           => 'Rate limit login. Secure sessions. 2FA for admins.',
        'A08_software_integrity'      => 'Verify package checksums. Use lock files.',
        'A09_logging_failures'        => 'Log all auth events, admin actions, errors.',
        'A10_ssrf'                    => 'Validate URLs before curl/HTTP requests. Block private IP ranges.',
    ],

];
