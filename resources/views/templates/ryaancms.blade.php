<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>RyaanCMS — AI-Powered Website Builder</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#050508;--surface:#0d0d14;--border:rgba(99,102,241,.12);--indigo:#6366f1;--violet:#8b5cf6;--cyan:#06b6d4;--text:#e2e8f0;--muted:rgba(226,232,240,.45);--card:#0f0f1a}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;line-height:1.6}
a{text-decoration:none;color:inherit}

/* GLOW BG */
.glow-orb{position:fixed;border-radius:50%;filter:blur(120px);pointer-events:none;z-index:0}
.glow-1{width:600px;height:600px;background:radial-gradient(circle,rgba(99,102,241,.18) 0%,transparent 70%);top:-200px;left:-200px}
.glow-2{width:500px;height:500px;background:radial-gradient(circle,rgba(139,92,246,.12) 0%,transparent 70%);bottom:200px;right:-150px}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:0 60px;height:68px;display:flex;align-items:center;justify-content:space-between;background:rgba(5,5,8,.8);backdrop-filter:blur(20px);border-bottom:1px solid var(--border)}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;letter-spacing:-.5px}
.logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--indigo),var(--violet));border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px}
.nav-links{display:flex;gap:32px}
.nav-links a{font-size:14px;color:var(--muted);font-weight:500;transition:color .15s}
.nav-links a:hover{color:var(--text)}
.nav-cta{background:var(--indigo);color:#fff;padding:9px 24px;border-radius:8px;font-size:14px;font-weight:600;transition:background .15s}
.nav-cta:hover{background:var(--violet)}

/* HERO */
.hero{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:120px 60px 80px}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);border-radius:100px;padding:6px 16px;font-size:12px;font-weight:600;color:var(--indigo);letter-spacing:.5px;margin-bottom:28px}
.hero-badge-dot{width:6px;height:6px;background:var(--indigo);border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
.hero h1{font-size:clamp(44px,7vw,88px);font-weight:900;line-height:1.05;letter-spacing:-2px;margin-bottom:24px;max-width:900px}
.hero h1 .grad{background:linear-gradient(135deg,var(--indigo),var(--violet),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:18px;color:var(--muted);max-width:560px;margin:0 auto 40px;line-height:1.75}
.hero-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-primary{background:linear-gradient(135deg,var(--indigo),var(--violet));color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;transition:opacity .15s;box-shadow:0 0 40px rgba(99,102,241,.3)}
.btn-primary:hover{opacity:.85}
.btn-ghost{border:1px solid var(--border);color:var(--text);padding:14px 36px;border-radius:10px;font-size:15px;font-weight:600;transition:border-color .15s,background .15s}
.btn-ghost:hover{border-color:var(--indigo);background:rgba(99,102,241,.06)}
.hero-sub{margin-top:64px;display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap}
.hero-stat{text-align:center}
.hero-stat-num{font-size:36px;font-weight:900;background:linear-gradient(135deg,var(--indigo),var(--violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-1px;line-height:1}
.hero-stat-label{font-size:12px;color:var(--muted);letter-spacing:.5px;margin-top:4px}
.stat-divider{width:1px;height:40px;background:var(--border)}

/* MARQUEE */
.marquee-wrap{border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:16px 0;overflow:hidden;position:relative;z-index:1}
.marquee-track{display:inline-flex;animation:marquee 30s linear infinite;white-space:nowrap}
.marquee-item{display:flex;align-items:center;gap:12px;padding:0 28px;font-size:13px;font-weight:600;letter-spacing:.5px;color:rgba(226,232,240,.25)}
.marquee-dot{width:5px;height:5px;background:var(--indigo);border-radius:50%;flex-shrink:0}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* SECTION COMMON */
section{position:relative;z-index:1}
.section-inner{max-width:1100px;margin:0 auto;padding:0 40px}
.section-badge{display:inline-block;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--indigo);margin-bottom:12px}
.section-h2{font-size:clamp(32px,4vw,52px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:16px}
.section-sub{font-size:16px;color:var(--muted);max-width:520px;line-height:1.7}

/* HOW IT WORKS */
.how{padding:100px 0}
.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;margin-top:60px;border:1px solid var(--border);border-radius:16px;overflow:hidden}
.how-step{background:var(--card);padding:40px 32px;position:relative;transition:background .2s}
.how-step:hover{background:#131320}
.step-num{font-size:48px;font-weight:900;color:rgba(99,102,241,.12);letter-spacing:-2px;line-height:1;margin-bottom:20px}
.step-icon{font-size:32px;margin-bottom:16px}
.step-title{font-size:18px;font-weight:700;margin-bottom:10px}
.step-desc{font-size:14px;color:var(--muted);line-height:1.7}

/* FEATURES */
.features{padding:80px 0}
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:56px}
.feat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:32px;transition:border-color .2s,transform .2s}
.feat-card:hover{border-color:rgba(99,102,241,.4);transform:translateY(-2px)}
.feat-icon{width:48px;height:48px;border-radius:12px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:20px}
.feat-title{font-size:16px;font-weight:700;margin-bottom:8px}
.feat-desc{font-size:13px;color:var(--muted);line-height:1.65}

/* DOMAINS */
.domains{padding:80px 0}
.domain-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:48px}
.domain-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;align-items:center;gap:14px;transition:border-color .2s}
.domain-card:hover{border-color:rgba(99,102,241,.35)}
.domain-icon{font-size:24px;flex-shrink:0}
.domain-name{font-size:13px;font-weight:600}

/* TECH STACK */
.tech{padding:80px 0}
.tech-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:40px}
.tech-pill{background:rgba(99,102,241,.07);border:1px solid var(--border);border-radius:100px;padding:8px 18px;font-size:13px;font-weight:500;color:var(--muted);transition:all .15s}
.tech-pill:hover{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4);color:var(--text)}

/* PRICING */
.pricing{padding:100px 0}
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:56px}
.pricing-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:36px;position:relative}
.pricing-card.featured{border-color:var(--indigo);background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(139,92,246,.06))}
.featured-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--indigo),var(--violet));color:#fff;font-size:11px;font-weight:700;padding:4px 16px;border-radius:100px;letter-spacing:.5px;white-space:nowrap}
.plan-name{font-size:14px;font-weight:700;color:var(--muted);letter-spacing:.5px;text-transform:uppercase;margin-bottom:16px}
.plan-price{font-size:48px;font-weight:900;letter-spacing:-2px;line-height:1;margin-bottom:4px}
.plan-price span{font-size:16px;font-weight:500;color:var(--muted)}
.plan-desc{font-size:13px;color:var(--muted);margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--border)}
.plan-features{list-style:none;display:flex;flex-direction:column;gap:12px;margin-bottom:32px}
.plan-features li{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:10px}
.plan-features li::before{content:'✓';width:18px;height:18px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;color:var(--indigo);flex-shrink:0}
.plan-btn{display:block;text-align:center;padding:13px;border-radius:10px;font-size:14px;font-weight:700;border:1px solid var(--border);color:var(--text);transition:all .15s}
.plan-btn:hover{border-color:var(--indigo);color:var(--indigo)}
.plan-btn.primary{background:linear-gradient(135deg,var(--indigo),var(--violet));border:none;color:#fff}
.plan-btn.primary:hover{opacity:.85}

/* CTA */
.cta{padding:100px 0}
.cta-box{background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(139,92,246,.1));border:1px solid rgba(99,102,241,.25);border-radius:24px;padding:80px 60px;text-align:center}
.cta h2{font-size:clamp(32px,4vw,52px);font-weight:900;letter-spacing:-1.5px;margin-bottom:16px}
.cta p{font-size:17px;color:var(--muted);max-width:500px;margin:0 auto 36px}

/* FOOTER */
footer{border-top:1px solid var(--border);padding:48px 60px;display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1}
.footer-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px}
.footer-links{display:flex;gap:28px}
.footer-links a{font-size:13px;color:var(--muted);transition:color .15s}
.footer-links a:hover{color:var(--text)}
.footer-copy{font-size:13px;color:rgba(226,232,240,.25)}

@media(max-width:900px){
  nav{padding:0 24px}
  .nav-links{display:none}
  .hero{padding:100px 24px 60px}
  .how-grid,.feat-grid,.domain-grid,.pricing-grid{grid-template-columns:1fr}
  .how-grid{border-radius:12px}
  footer{flex-direction:column;gap:20px;text-align:center;padding:40px 24px}
  .cta-box{padding:48px 24px}
  .section-inner{padding:0 24px}
}
</style>
</head>
<body>

<div class="glow-orb glow-1"></div>
<div class="glow-orb glow-2"></div>

<!-- NAV -->
<nav>
  <a href="#" class="logo">
    <div class="logo-icon">🤖</div>
    RyaanCMS
  </a>
  <div class="nav-links">
    <a href="#features">Features</a>
    <a href="#domains">Domains</a>
    <a href="#pricing">Pricing</a>
    <a href="#how">How It Works</a>
  </div>
  <a href="/login" class="nav-cta">Get Started</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge">
    <span class="hero-badge-dot"></span>
    AI-Powered Website Builder
  </div>
  <h1>Build Any Website<br>With <span class="grad">AI Assistance</span></h1>
  <p>Describe your vision. RyaanCMS generates the complete website — pages, content, structure — in seconds. No code required.</p>
  <div class="hero-btns">
    <a href="/login" class="btn-primary">Start Building Free</a>
    <a href="#how" class="btn-ghost">See How It Works</a>
  </div>
  <div class="hero-sub">
    <div class="hero-stat">
      <div class="hero-stat-num">10</div>
      <div class="hero-stat-label">AI Agents</div>
    </div>
    <div class="stat-divider"></div>
    <div class="hero-stat">
      <div class="hero-stat-num">12</div>
      <div class="hero-stat-label">Domain Packs</div>
    </div>
    <div class="stat-divider"></div>
    <div class="hero-stat">
      <div class="hero-stat-num">30+</div>
      <div class="hero-stat-label">Knowledge Bases</div>
    </div>
    <div class="stat-divider"></div>
    <div class="hero-stat">
      <div class="hero-stat-num">70+</div>
      <div class="hero-stat-label">Integrations</div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-wrap">
  <div class="marquee-track">
    @php $items = ['Restaurant','E-Commerce','Portfolio','SaaS','Agency','Healthcare','Legal','Real Estate','Education','Hotel','Event','NGO','Fitness','Photography','Blog']; @endphp
    @foreach(array_merge($items,$items) as $item)
      <div class="marquee-item"><span class="marquee-dot"></span>{{ $item }}</div>
    @endforeach
  </div>
</div>

<!-- HOW IT WORKS -->
<section class="how" id="how">
  <div class="section-inner">
    <div class="section-badge">How It Works</div>
    <h2 class="section-h2">Three Steps to Your Website</h2>
    <p class="section-sub">From idea to live website in minutes — powered by an autonomous AI pipeline.</p>
    <div class="how-grid">
      <div class="how-step">
        <div class="step-num">01</div>
        <div class="step-icon">💬</div>
        <div class="step-title">Describe Your Vision</div>
        <div class="step-desc">Tell the AI what kind of website you need. Use plain language — the AI understands context, industry, and requirements automatically.</div>
      </div>
      <div class="how-step">
        <div class="step-num">02</div>
        <div class="step-icon">🤖</div>
        <div class="step-title">AI Builds It</div>
        <div class="step-desc">10 specialized agents analyze your request, select the best domain pack, generate pages, content, and structure — all with zero manual coding.</div>
      </div>
      <div class="how-step">
        <div class="step-num">03</div>
        <div class="step-icon">🚀</div>
        <div class="step-title">Launch & Customize</div>
        <div class="step-desc">Your website is ready. Customize with the visual editor, add modules, connect integrations, and go live on your domain instantly.</div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-inner">
    <div class="section-badge">Features</div>
    <h2 class="section-h2">Everything You Need</h2>
    <p class="section-sub">A complete platform — from AI generation to deployment, analytics, and beyond.</p>
    <div class="feat-grid">
      <div class="feat-card">
        <div class="feat-icon">🧠</div>
        <div class="feat-title">Intelligent AI Builder</div>
        <div class="feat-desc">Context-aware AI that understands your industry and generates production-ready code and content automatically.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon">🎨</div>
        <div class="feat-title">Visual Page Editor</div>
        <div class="feat-desc">Drag-and-drop editor with live preview. Edit text, images, layouts, and colors without touching a line of code.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon">📦</div>
        <div class="feat-title">Module Marketplace</div>
        <div class="feat-desc">Install features instantly — booking systems, galleries, contact forms, e-commerce, and more with one click.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon">🔐</div>
        <div class="feat-title">Built-in Security</div>
        <div class="feat-desc">RBAC permissions, secure authentication, input validation, and XSS/CSRF protection built into every project.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon">📊</div>
        <div class="feat-title">Analytics & Reports</div>
        <div class="feat-desc">Track visitors, conversions, and performance with built-in analytics dashboards — no third-party tools needed.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon">🌐</div>
        <div class="feat-title">70+ Integrations</div>
        <div class="feat-desc">Connect payment gateways, CRMs, email platforms, social media, and APIs without writing integration code.</div>
      </div>
    </div>
  </div>
</section>

<!-- DOMAINS -->
<section class="domains" id="domains">
  <div class="section-inner">
    <div class="section-badge">Domain Packs</div>
    <h2 class="section-h2">Built for Every Industry</h2>
    <p class="section-sub">Pre-trained on real-world industry requirements so the AI knows exactly what your website needs.</p>
    <div class="domain-grid">
      @php
        $domains = [
          ['🍽️','Restaurant & Food'],['🛒','E-Commerce'],['💼','Portfolio'],['🚀','SaaS & Software'],
          ['⚡','Creative Agency'],['🏥','Healthcare'],['⚖️','Legal Services'],['🏡','Real Estate'],
          ['🎓','Education'],['🏨','Hotel & Travel'],['🎪','Events'],['❤️','Non-Profit'],
        ];
      @endphp
      @foreach($domains as [$icon, $name])
        <div class="domain-card">
          <span class="domain-icon">{{ $icon }}</span>
          <span class="domain-name">{{ $name }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- TECH STACK -->
<section class="tech">
  <div class="section-inner">
    <div class="section-badge">Tech Stack</div>
    <h2 class="section-h2">Built on Rock-Solid Foundations</h2>
    <p class="section-sub">Enterprise-grade technologies, battle-tested and production-ready.</p>
    <div class="tech-pills">
      @foreach(['Laravel 11','PHP 8.3','MySQL','Redis','Tailwind CSS','Alpine.js','Claude AI','GPT-4','Gemini','Llama 3','Stripe','PayPal','Twilio','Mailgun','AWS S3','cPanel','Docker','GitHub Actions','REST API','WebSockets'] as $tech)
        <span class="tech-pill">{{ $tech }}</span>
      @endforeach
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="pricing" id="pricing">
  <div class="section-inner">
    <div class="section-badge">Pricing</div>
    <h2 class="section-h2">Simple, Transparent Pricing</h2>
    <p class="section-sub">Start free. Upgrade when you need more power.</p>
    <div class="pricing-grid">
      <div class="pricing-card">
        <div class="plan-name">Starter</div>
        <div class="plan-price">$0 <span>/ month</span></div>
        <div class="plan-desc">Perfect for testing and personal projects.</div>
        <ul class="plan-features">
          <li>1 Website Project</li>
          <li>5 AI Builder Sessions</li>
          <li>Basic Templates</li>
          <li>Community Support</li>
        </ul>
        <a href="/register" class="plan-btn">Get Started Free</a>
      </div>
      <div class="pricing-card featured">
        <div class="featured-badge">Most Popular</div>
        <div class="plan-name">Pro</div>
        <div class="plan-price">$29 <span>/ month</span></div>
        <div class="plan-desc">For professionals and growing businesses.</div>
        <ul class="plan-features">
          <li>10 Website Projects</li>
          <li>Unlimited AI Sessions</li>
          <li>All Domain Packs</li>
          <li>Priority Support</li>
          <li>Custom Domain</li>
          <li>Advanced Analytics</li>
        </ul>
        <a href="/register" class="plan-btn primary">Start Pro Trial</a>
      </div>
      <div class="pricing-card">
        <div class="plan-name">Agency</div>
        <div class="plan-price">$99 <span>/ month</span></div>
        <div class="plan-desc">For agencies managing multiple clients.</div>
        <ul class="plan-features">
          <li>Unlimited Projects</li>
          <li>White-label Option</li>
          <li>Multi-tenant Support</li>
          <li>Dedicated Support</li>
          <li>API Access</li>
          <li>Custom Integrations</li>
        </ul>
        <a href="/register" class="plan-btn">Contact Sales</a>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <div class="section-inner">
    <div class="cta-box">
      <h2>Ready to Build Something Amazing?</h2>
      <p>Join thousands of creators who build faster with AI. No credit card required.</p>
      <a href="/register" class="btn-primary" style="display:inline-block">Start Building Free →</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-logo">
    <div class="logo-icon" style="width:28px;height:28px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px">🤖</div>
    RyaanCMS
  </div>
  <div class="footer-links">
    <a href="#features">Features</a>
    <a href="#domains">Domains</a>
    <a href="#pricing">Pricing</a>
    <a href="/login">Login</a>
    <a href="/register">Register</a>
  </div>
  <div class="footer-copy">&copy; {{ date('Y') }} RyaanCMS. All rights reserved.</div>
</footer>

</body>
</html>
