<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — {{ config('app.name', 'RyaanCMS') }}</title>
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
                <a href="{{ route('privacy') }}" style="font-size:.875rem; color:#64748b; text-decoration:none; font-weight:500;">Privacy Policy</a>
                <a href="{{ route('home') }}" style="font-size:.875rem; color:#6366f1; text-decoration:none; font-weight:600;">← Back to Home</a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main style="max-width:900px; margin:0 auto; padding:3rem 1.5rem 5rem;">

        <!-- Header -->
        <div style="margin-bottom:2.5rem;">
            <div style="display:inline-block; background:#ede9fe; color:#6d28d9; font-size:.75rem; font-weight:700; letter-spacing:.05em; padding:.3rem .9rem; border-radius:999px; margin-bottom:1rem; text-transform:uppercase;">Legal</div>
            <h1 style="font-size:2.25rem; font-weight:800; color:#0f172a; margin:0 0 .75rem;">Terms of Service</h1>
            <p style="font-size:.9375rem; color:#64748b; margin:0;">Last updated: {{ date('F j, Y') }} &nbsp;·&nbsp; Effective immediately</p>
        </div>

        <div style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:2.5rem 2.5rem;" class="prose">

            <p>Welcome to <strong>{{ config('app.name', 'RyaanCMS') }}</strong> ("RyaanCMS", "we", "our", or "us"). By accessing or using our self-hosted AI application builder platform, you agree to be bound by these Terms of Service ("Terms"). Please read them carefully before using the software.</p>

            <hr class="divider">

            <h2>1. Acceptance of Terms</h2>
            <p>By installing, accessing, or using RyaanCMS, you confirm that you are at least 18 years old, have the legal authority to enter into this agreement, and agree to comply with these Terms. If you are using RyaanCMS on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p>

            <h2>2. Description of Service</h2>
            <p>RyaanCMS is a self-hosted AI-powered application builder that enables you to generate, manage, and deploy web applications using artificial intelligence. The platform may include:</p>
            <ul>
                <li>AI-powered code generation using third-party AI providers (OpenAI, Anthropic, Google Gemini, etc.)</li>
                <li>A plugin/extension marketplace for distributing and installing modules</li>
                <li>Project management and file editing tools</li>
                <li>A builder interface for creating web applications</li>
            </ul>
            <p>Because RyaanCMS is self-hosted software, you are responsible for the infrastructure, hosting, security, and maintenance of your own installation.</p>

            <h2>3. License Grant</h2>
            <p>Subject to these Terms, we grant you a limited, non-exclusive, non-transferable license to:</p>
            <ul>
                <li>Install and use RyaanCMS on servers you own or control</li>
                <li>Modify the source code for your own internal use</li>
                <li>Create and distribute applications built using RyaanCMS</li>
            </ul>
            <p>You may <strong>not</strong>:</p>
            <ul>
                <li>Redistribute RyaanCMS itself as a competing product or SaaS</li>
                <li>Remove or obscure any copyright or attribution notices (including "Powered by RyaanCMS" branding)</li>
                <li>Use RyaanCMS to generate malware, phishing content, or any application intended to harm others</li>
                <li>Reverse engineer, decompile, or attempt to extract proprietary algorithms beyond what open-source licensing permits</li>
            </ul>

            <h2>4. Plugin Marketplace & License Keys</h2>
            <p>Plugins and applications distributed through the RyaanCMS marketplace are subject to individual license terms set by their respective developers. When you install a marketplace package:</p>
            <ul>
                <li>A unique license key is issued per marketplace item purchase and is <strong>domain-locked</strong> to a single domain</li>
                <li>You may install the purchased marketplace item into multiple projects on that same domain</li>
                <li>Attempting to activate a license key on a different domain than originally registered is a violation of these Terms</li>
                <li>License keys may not be transferred, resold, or shared</li>
                <li>Developer packages uploaded to the marketplace must comply with our content guidelines and must not contain malicious code</li>
            </ul>

            <h2>5. Third-Party AI Providers</h2>
            <p>RyaanCMS integrates with third-party AI services including but not limited to OpenAI, Anthropic, Google, and Mistral. By using AI features:</p>
            <ul>
                <li>You acknowledge that your prompts and project data may be transmitted to these third-party providers</li>
                <li>You are responsible for obtaining and managing your own API keys</li>
                <li>You must comply with each provider's usage policies (e.g., OpenAI's Usage Policy, Anthropic's Acceptable Use Policy)</li>
                <li>We are not responsible for the accuracy, quality, or legality of AI-generated content</li>
            </ul>

            <h2>6. User Responsibilities</h2>
            <p>You are solely responsible for:</p>
            <ul>
                <li>All applications, code, and content generated through your RyaanCMS installation</li>
                <li>Securing your installation against unauthorized access</li>
                <li>Maintaining backups of your data</li>
                <li>Ensuring your use of RyaanCMS complies with applicable local, national, and international laws</li>
                <li>Any charges incurred from third-party AI API usage</li>
            </ul>

            <h2>7. Prohibited Uses</h2>
            <p>You may not use RyaanCMS to:</p>
            <ul>
                <li>Generate content that is illegal, fraudulent, defamatory, or infringes on third-party intellectual property rights</li>
                <li>Build applications designed for spamming, phishing, or unauthorized data collection</li>
                <li>Circumvent security measures of any system or network</li>
                <li>Violate any applicable laws or regulations</li>
                <li>Harass, abuse, or harm any individual or group</li>
            </ul>

            <h2>8. Intellectual Property</h2>
            <p>You retain ownership of all applications and code you generate using RyaanCMS. RyaanCMS retains ownership of the platform itself, its proprietary algorithms, and its branding. Attribution ("Powered by RyaanCMS") in generated applications is required under the default license and constitutes part of the license grant conditions.</p>

            <h2>9. Disclaimer of Warranties</h2>
            <p>RyaanCMS is provided <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> without warranties of any kind, express or implied. We do not warrant that:</p>
            <ul>
                <li>The software will be error-free or uninterrupted</li>
                <li>AI-generated code will be production-ready, secure, or legally compliant</li>
                <li>The software will meet your specific requirements</li>
            </ul>
            <p>You assume full responsibility for the use of any AI-generated output in production systems.</p>

            <h2>10. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, RyaanCMS and its contributors shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the software, including but not limited to: data loss, security breaches, revenue loss, or third-party claims resulting from AI-generated content.</p>

            <h2>11. Indemnification</h2>
            <p>You agree to indemnify and hold harmless RyaanCMS and its contributors from any claims, damages, losses, or expenses (including legal fees) arising from: your use of the platform, applications you build and deploy, violation of these Terms, or infringement of any third-party rights.</p>

            <h2>12. Updates to These Terms</h2>
            <p>We reserve the right to modify these Terms at any time. Continued use of RyaanCMS after modifications constitutes acceptance of the updated Terms. We will update the "Last updated" date at the top of this page when changes are made.</p>

            <h2>13. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with applicable law. Any disputes shall be resolved through good-faith negotiation, and if necessary, through binding arbitration or a court of competent jurisdiction.</p>

            <h2>14. Contact</h2>
            <p>If you have questions about these Terms of Service, please contact us through the official RyaanCMS project repository or the contact information provided on our website.</p>

        </div>

        <!-- Navigation between legal pages -->
        <div style="display:flex; gap:1rem; margin-top:2rem; flex-wrap:wrap;">
            <a href="{{ route('privacy') }}"
               style="display:inline-flex; align-items:center; gap:8px; padding:.75rem 1.25rem; background:#fff; border:1px solid #e2e8f0; border-radius:10px; font-size:.875rem; font-weight:600; color:#475569; text-decoration:none;">
                Privacy Policy →
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
