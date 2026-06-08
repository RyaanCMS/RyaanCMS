<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — {{ config('app.name', 'RyaanCMS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .prose h2 { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: .75rem; }
        .prose h3 { font-size: 1rem; font-weight: 600; color: #1e293b; margin-top: 1.5rem; margin-bottom: .5rem; }
        .prose p, .prose li { font-size: .9375rem; line-height: 1.75; color: #475569; }
        .prose ul { padding-left: 1.5rem; list-style: disc; margin-top: .5rem; margin-bottom: .5rem; }
        .prose li { margin-bottom: .25rem; }
        .prose a { color: #6366f1; text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header style="background:#fff; border-bottom:1px solid #e2e8f0;">
        <div style="max-width:900px; margin:0 auto; padding:0 1.5rem; height:60px; display:flex; align-items:center; justify-content:space-between;">
            <a href="{{ route('home') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
                <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span style="font-size:1.1rem; font-weight:800; color:#1e293b;">{{ config('app.name', 'RyaanCMS') }}</span>
            </a>
            <div style="display:flex; gap:1.5rem; align-items:center;">
                <a href="{{ route('terms') }}" style="font-size:.875rem; color:#64748b; text-decoration:none; font-weight:500;">Terms of Service</a>
                <a href="{{ route('home') }}" style="font-size:.875rem; color:#6366f1; text-decoration:none; font-weight:600;">← Back to Home</a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main style="max-width:900px; margin:0 auto; padding:3rem 1.5rem 5rem;">

        <!-- Header -->
        <div style="margin-bottom:2.5rem;">
            <div style="display:inline-block; background:#ede9fe; color:#6d28d9; font-size:.75rem; font-weight:700; letter-spacing:.05em; padding:.3rem .9rem; border-radius:999px; margin-bottom:1rem; text-transform:uppercase;">Legal</div>
            <h1 style="font-size:2.25rem; font-weight:800; color:#0f172a; margin:0 0 .75rem;">Privacy Policy</h1>
            <p style="font-size:.9375rem; color:#64748b; margin:0;">Last updated: {{ date('F j, Y') }} &nbsp;·&nbsp; Effective immediately</p>
        </div>

        <div style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:2.5rem 2.5rem;" class="prose">

            <p><strong>{{ config('app.name', 'RyaanCMS') }}</strong> ("RyaanCMS", "we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains what information we collect, how we use it, and your rights regarding your data when you use our self-hosted AI application builder platform.</p>
            <p>Because RyaanCMS is <strong>self-hosted software</strong>, the primary data controller for your installation is <strong>you</strong> — the person or organization running the instance. This policy also covers the RyaanCMS project's own data practices.</p>

            <hr class="divider">

            <h2>1. Information We Collect</h2>

            <h3>1.1 Account Information</h3>
            <p>When you create an account during installation or registration, we collect:</p>
            <ul>
                <li>Name and email address</li>
                <li>Encrypted password (never stored in plain text)</li>
                <li>Account creation timestamp</li>
            </ul>

            <h3>1.2 Project & Usage Data</h3>
            <p>As you use the platform, the following data is stored in your own database:</p>
            <ul>
                <li>Projects you create, including names, descriptions, and settings</li>
                <li>Files and source code generated for your projects</li>
                <li>AI prompts and conversation history within the builder</li>
                <li>Marketplace plugin installation records and license keys</li>
            </ul>
            <p>This data is stored <strong>entirely on your own server</strong> and is not transmitted to us.</p>

            <h3>1.3 Technical & Log Data</h3>
            <p>Your web server may automatically log:</p>
            <ul>
                <li>IP addresses and browser/device information</li>
                <li>Pages visited and actions taken within the application</li>
                <li>Error logs and performance metrics</li>
            </ul>
            <p>This data is stored in your server logs and is under your control.</p>

            <h3>1.4 Third-Party AI Provider Data</h3>
            <p>When you use AI-powered features, your prompts and context data are transmitted to the AI provider you have configured (e.g., OpenAI, Anthropic, Google Gemini). We do not intercept this data, but you should be aware that:</p>
            <ul>
                <li>Your prompts are subject to the privacy policy of the respective AI provider</li>
                <li>You should review OpenAI's, Anthropic's, or your chosen provider's privacy policy</li>
                <li>Avoid sending personally identifiable information (PII) in AI prompts</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>Within your self-hosted installation, your data is used to:</p>
            <ul>
                <li>Authenticate your identity and maintain your session</li>
                <li>Store and retrieve your projects and generated code</li>
                <li>Process AI generation requests by forwarding prompts to your configured AI provider</li>
                <li>Validate marketplace license keys and enforce domain-locking</li>
                <li>Send system notifications (e.g., build complete, error alerts) if configured</li>
            </ul>
            <p>We do not sell, rent, or share your personal information with third parties for marketing purposes.</p>

            <h2>3. Data Storage & Security</h2>
            <p>Since RyaanCMS is self-hosted, your data resides on servers you control. We strongly recommend:</p>
            <ul>
                <li>Using HTTPS (TLS/SSL) for your installation</li>
                <li>Keeping your server and Laravel dependencies up to date</li>
                <li>Using strong, unique passwords and enabling any available two-factor authentication</li>
                <li>Regularly backing up your database and uploaded files</li>
                <li>Restricting database and server access with proper firewall rules</li>
            </ul>
            <p>We are not responsible for data breaches or security incidents on your own hosted infrastructure.</p>

            <h2>4. Cookies & Session Data</h2>
            <p>RyaanCMS uses cookies to maintain your authenticated session. These cookies are:</p>
            <ul>
                <li><strong>Session cookies</strong> — temporarily store your login session (expire when you close the browser or after the configured timeout)</li>
                <li><strong>CSRF tokens</strong> — protect against cross-site request forgery attacks</li>
                <li><strong>Remember-me cookies</strong> — if you opt in, extend your session for up to 30 days</li>
            </ul>
            <p>No tracking or advertising cookies are used by RyaanCMS.</p>

            <h2>5. Third-Party Integrations</h2>
            <p>RyaanCMS integrates with third-party services that have their own privacy policies:</p>
            <ul>
                <li><strong>OpenAI</strong> — openai.com/privacy</li>
                <li><strong>Anthropic (Claude)</strong> — anthropic.com/privacy</li>
                <li><strong>Google (Gemini)</strong> — policies.google.com/privacy</li>
                <li><strong>Bunny Fonts</strong> — fonts.bunny.net (used for typography, no tracking)</li>
            </ul>
            <p>You choose which integrations to enable. We recommend reviewing each provider's privacy policy before enabling their services.</p>

            <h2>6. Data Retention</h2>
            <p>Since you control your own server:</p>
            <ul>
                <li>Your data is retained for as long as your installation is active</li>
                <li>You can delete any project, user account, or data at any time through the admin interface or directly in the database</li>
                <li>Deleting your RyaanCMS installation removes all associated data from that server</li>
            </ul>

            <h2>7. Your Rights</h2>
            <p>As a user of a self-hosted RyaanCMS installation, you have the right to:</p>
            <ul>
                <li><strong>Access</strong> — View all data stored about your account and projects</li>
                <li><strong>Correction</strong> — Update your profile information at any time</li>
                <li><strong>Deletion</strong> — Delete your account and all associated data</li>
                <li><strong>Portability</strong> — Export your projects and generated code</li>
                <li><strong>Restriction</strong> — Limit how AI providers process your data by using local/offline AI models</li>
            </ul>
            <p>To exercise these rights, use the account settings within the application or contact your system administrator.</p>

            <h2>8. Children's Privacy</h2>
            <p>RyaanCMS is not intended for use by individuals under the age of 13. We do not knowingly collect personal information from children. If you believe a child has provided personal information through your RyaanCMS installation, please delete that information immediately.</p>

            <h2>9. International Data Transfers</h2>
            <p>If you configure AI providers such as OpenAI or Anthropic, your data (prompts) may be transferred to and processed in countries outside your own, including the United States. These providers maintain their own data processing agreements and comply with international data transfer regulations (e.g., GDPR Standard Contractual Clauses).</p>

            <h2>10. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. The "Last updated" date at the top of this page will reflect any changes. We encourage you to review this policy periodically. Continued use of RyaanCMS after changes are posted constitutes your acceptance of the updated policy.</p>

            <h2>11. Contact Us</h2>
            <p>If you have questions or concerns about this Privacy Policy or how your data is handled within RyaanCMS, please reach out through:</p>
            <ul>
                <li>The official RyaanCMS project repository</li>
                <li>The contact information provided on the RyaanCMS website</li>
                <li>Your system administrator (for questions specific to your hosted instance)</li>
            </ul>

        </div>

        <!-- Navigation between legal pages -->
        <div style="display:flex; gap:1rem; margin-top:2rem; flex-wrap:wrap;">
            <a href="{{ route('terms') }}"
               style="display:inline-flex; align-items:center; gap:8px; padding:.75rem 1.25rem; background:#fff; border:1px solid #e2e8f0; border-radius:10px; font-size:.875rem; font-weight:600; color:#475569; text-decoration:none;">
                Terms of Service →
            </a>
            <a href="{{ route('home') }}"
               style="display:inline-flex; align-items:center; gap:8px; padding:.75rem 1.25rem; background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:10px; font-size:.875rem; font-weight:600; color:#fff; text-decoration:none;">
                ← Back to Home
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer style="background:#fff; border-top:1px solid #e2e8f0; padding:1.5rem; text-align:center;">
        <p style="font-size:.8125rem; color:#94a3b8; margin:0;">
            &copy; {{ date('Y') }} {{ config('app.name', 'RyaanCMS') }}. All rights reserved.
            &nbsp;·&nbsp;
            <a href="{{ route('terms') }}" style="color:#6366f1; text-decoration:none;">Terms of Service</a>
            &nbsp;·&nbsp;
            <a href="{{ route('privacy') }}" style="color:#6366f1; text-decoration:none;">Privacy Policy</a>
        </p>
        <!-- ryaancms-powered-attribution -->
        <div style="margin-top:.5rem;">
            <span style="font-size:.75rem; font-weight:600; color:#94a3b8;">⚡ Powered by <span style="color:#6366f1;">RyaanCMS</span> <span style="opacity:.55; font-size:.65rem;">v{{ config('ryaan.version', '1.0.0') }}</span></span>
        </div>
    </footer>

</body>
</html>
