<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>DataFlow — Automate Your Business Intelligence</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--blue:#2563eb;--indigo:#4f46e5;--light-blue:#eff6ff;--dark:#0f172a;--mid:#334155;--muted:#64748b;--border:#e2e8f0;--white:#fff}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--white);color:var(--dark);overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* NAV */
nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.9);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:0 60px;display:flex;align-items:center;justify-content:space-between;height:68px}
.nav-logo{font-size:20px;font-weight:800;letter-spacing:-0.5px;color:var(--dark)}
.nav-logo span{color:var(--blue)}
.nav-links{display:flex;gap:32px;align-items:center}
.nav-links a{font-size:14px;font-weight:500;color:var(--muted);transition:color .15s}
.nav-links a:hover{color:var(--dark)}
.nav-right{display:flex;gap:12px;align-items:center}
.btn-login{font-size:14px;font-weight:600;color:var(--mid);padding:9px 20px;border-radius:8px;transition:background .15s}
.btn-login:hover{background:var(--light-blue)}
.btn-cta{background:var(--blue);color:#fff;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:700}

/* HERO */
.hero{padding:90px 60px 0;text-align:center;background:linear-gradient(180deg,#f0f7ff 0%,#fff 80%);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-300px;left:50%;transform:translateX(-50%);width:900px;height:900px;background:radial-gradient(circle,rgba(37,99,235,.08) 0%,transparent 70%);pointer-events:none}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--border);border-radius:100px;padding:6px 16px 6px 6px;font-size:12px;font-weight:600;color:var(--mid);margin-bottom:28px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.hero-badge .badge-new{background:var(--blue);color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;letter-spacing:.5px}
.hero h1{font-size:72px;font-weight:800;letter-spacing:-2.5px;line-height:1.05;max-width:820px;margin:0 auto 22px;color:var(--dark)}
.hero h1 .gradient{background:linear-gradient(135deg,var(--blue),var(--indigo));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-sub{font-size:18px;color:var(--muted);max-width:540px;margin:0 auto 40px;line-height:1.7;font-weight:400}
.hero-btns{display:flex;gap:12px;justify-content:center;margin-bottom:20px}
.btn-primary{background:var(--blue);color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700}
.btn-secondary{background:#fff;color:var(--dark);border:1px solid var(--border);padding:14px 36px;border-radius:10px;font-size:15px;font-weight:600}
.hero-note{font-size:13px;color:var(--muted);margin-bottom:56px}
.hero-note strong{color:var(--dark)}
.hero-dash{background:#0f172a;border-radius:20px 20px 0 0;padding:28px 28px 0;max-width:1000px;margin:0 auto;box-shadow:0 -20px 60px rgba(37,99,235,.15)}
.dash-bar{display:flex;gap:6px;margin-bottom:20px;align-items:center}
.dash-dot{width:10px;height:10px;border-radius:50%}
.dash-inner{background:#1e293b;border-radius:12px;height:340px;display:flex;align-items:center;justify-content:center;font-size:64px;overflow:hidden;position:relative}
.dash-grid{position:absolute;inset:0;display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:repeat(2,1fr);gap:12px;padding:20px}
.dash-widget{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:8px}
.dw-label{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:1px;text-transform:uppercase}
.dw-value{font-size:24px;font-weight:800;color:#fff}
.dw-trend{font-size:11px;color:#4ade80}

/* LOGOS */
.logos{border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:24px 60px;display:flex;justify-content:center;gap:56px;align-items:center}
.logo-item{font-size:14px;font-weight:700;color:#cbd5e1;letter-spacing:-.3px}

/* FEATURES */
.features{padding:100px 60px}
.section-center{text-align:center;max-width:600px;margin:0 auto 64px}
.eyebrow{font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--blue);margin-bottom:12px}
.h2{font-size:46px;font-weight:800;letter-spacing:-1.5px;line-height:1.1;color:var(--dark);margin-bottom:14px}
.sub{font-size:16px;color:var(--muted);line-height:1.7}
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1200px;margin:0 auto}
.feat-card{background:#fafafa;border:1px solid var(--border);border-radius:20px;padding:36px;transition:all .2s}
.feat-card:hover{background:#fff;border-color:#bfdbfe;box-shadow:0 8px 32px rgba(37,99,235,.08);transform:translateY(-2px)}
.feat-icon{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--blue),var(--indigo));display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:20px}
.feat-title{font-size:18px;font-weight:700;color:var(--dark);margin-bottom:10px}
.feat-desc{font-size:14px;color:var(--muted);line-height:1.7}

/* PRICING */
.pricing{background:var(--light-blue);padding:100px 60px}
.price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1100px;margin:0 auto}
.price-card{background:#fff;border:1px solid var(--border);border-radius:24px;padding:40px;transition:all .2s}
.price-card.popular{border-color:var(--blue);box-shadow:0 0 0 2px var(--blue);position:relative}
.popular-badge{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--blue);color:#fff;font-size:11px;font-weight:700;letter-spacing:1px;padding:5px 20px;border-radius:100px;white-space:nowrap}
.price-tier{font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue);margin-bottom:14px}
.price-amount{font-size:52px;font-weight:800;letter-spacing:-2px;color:var(--dark);line-height:1}
.price-amount sup{font-size:24px;vertical-align:top;margin-top:12px;letter-spacing:0}
.price-per{font-size:14px;color:var(--muted);margin:6px 0 28px}
.price-desc{font-size:14px;color:var(--muted);margin-bottom:28px;line-height:1.6}
.price-features{list-style:none;space-y:10px;margin-bottom:32px}
.price-features li{font-size:14px;color:var(--mid);padding:7px 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.price-features li::before{content:'✓';color:var(--blue);font-weight:700;font-size:13px}
.btn-plan{display:block;text-align:center;padding:13px;border-radius:10px;font-size:14px;font-weight:700;transition:all .2s}
.btn-plan-outline{border:1px solid var(--border);color:var(--dark)}
.btn-plan-outline:hover{border-color:var(--blue);color:var(--blue)}
.btn-plan-filled{background:var(--blue);color:#fff}

/* TESTIMONIALS */
.testimonials{padding:100px 60px}
.test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:1200px;margin:64px auto 0}
.test-card{border:1px solid var(--border);border-radius:20px;padding:32px;transition:all .2s}
.test-card:hover{border-color:#bfdbfe;box-shadow:0 4px 20px rgba(37,99,235,.06)}
.test-quote{font-size:15px;color:var(--mid);line-height:1.75;margin-bottom:24px}
.test-author{display:flex;align-items:center;gap:12px}
.test-ava{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px}
.test-name{font-weight:700;font-size:14px}
.test-co{font-size:12px;color:var(--muted)}

/* CTA */
.cta{margin:0 60px 100px;background:linear-gradient(135deg,var(--dark) 0%,#1e293b 100%);border-radius:28px;padding:80px;text-align:center;position:relative;overflow:hidden}
.cta::before{content:'';position:absolute;top:-200px;left:50%;transform:translateX(-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(37,99,235,.3) 0%,transparent 70%);pointer-events:none}
.cta h2{font-size:52px;font-weight:800;letter-spacing:-2px;color:#fff;margin-bottom:14px;position:relative}
.cta p{color:rgba(255,255,255,.55);font-size:16px;max-width:440px;margin:0 auto 36px;line-height:1.7;position:relative}
.cta-btns{display:flex;gap:12px;justify-content:center;position:relative}
.btn-cta-white{background:#fff;color:var(--dark);padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700}
.btn-cta-ghost{border:1px solid rgba(255,255,255,.2);color:#fff;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:500}

/* FOOTER */
footer{background:var(--dark);color:rgba(255,255,255,.45);padding:60px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px}
.f-logo{font-size:20px;font-weight:800;color:#fff;margin-bottom:12px;letter-spacing:-.5px;display:block}
.f-logo span{color:var(--blue)}
.f-desc{font-size:13px;line-height:1.8;max-width:240px}
.f-head{color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:20px}
.f-link{display:block;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:10px;transition:color .15s}
.f-link:hover{color:#fff}
.f-bottom{border-top:1px solid rgba(255,255,255,.07);margin-top:48px;padding-top:20px;display:flex;justify-content:space-between;grid-column:1/-1;font-size:12px}
.f-ryaan{color:var(--blue);font-weight:600}

@media(max-width:900px){
nav{padding:0 20px}.nav-links{display:none}
.hero{padding:80px 20px 0}
.hero h1{font-size:42px}
.logos{padding:20px;gap:20px;flex-wrap:wrap}
.features,.pricing,.testimonials{padding:60px 20px}
.feat-grid,.price-grid,.test-grid{grid-template-columns:1fr}
.cta{margin:0 20px 60px;padding:48px 28px}
.cta h2{font-size:36px}
footer{grid-template-columns:1fr;padding:48px 20px;gap:32px}
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">Data<span>Flow</span></div>
  <div class="nav-links">
    <a href="#features">Features</a><a href="#pricing">Pricing</a><a href="#testimonials">Customers</a><a href="#">Docs</a>
  </div>
  <div class="nav-right">
    <a href="#" class="btn-login">Sign In</a>
    <a href="#" class="btn-cta">Start Free Trial</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><span class="badge-new">NEW</span>v3.0 — Real-time AI Insights are here</div>
  <h1>Turn Data Into <span class="gradient">Decisions</span>, Automatically</h1>
  <p class="hero-sub">DataFlow connects all your business tools and uses AI to surface insights, predict trends, and automate reporting — so your team can focus on what matters.</p>
  <div class="hero-btns">
    <a href="#" class="btn-primary">Start Free — No Credit Card</a>
    <a href="#" class="btn-secondary">▶ Watch Demo</a>
  </div>
  <p class="hero-note"><strong>Trusted by 12,000+ companies.</strong> Setup in under 5 minutes.</p>
  <div class="hero-dash">
    <div class="dash-bar">
      <div class="dash-dot" style="background:#ef4444"></div>
      <div class="dash-dot" style="background:#fbbf24"></div>
      <div class="dash-dot" style="background:#4ade80"></div>
    </div>
    <div class="dash-inner">
      <div class="dash-grid">
        @foreach([['Revenue','$248K','↑ 18%'],['Users','12,480','↑ 32%'],['Conversion','4.8%','↑ 0.6%'],['Churn','1.2%','↓ 0.3%'],['MRR','$42,100','↑ 22%'],['ARR','$505K','↑ 22%'],['NPS Score','72','↑ 8'],['Tickets','14','↓ 23%']] as [$l,$v,$t])
        <div class="dash-widget"><div class="dw-label">{{ $l }}</div><div class="dw-value">{{ $v }}</div><div class="dw-trend">{{ $t }}</div></div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<!-- LOGOS -->
<div class="logos">
  @foreach(['Stripe','Shopify','HubSpot','Notion','Intercom','Linear','Vercel'] as $l)
  <span class="logo-item">{{ $l }}</span>
  @endforeach
</div>

<!-- FEATURES -->
<section id="features" class="features">
  <div class="section-center">
    <div class="eyebrow">Features</div>
    <h2 class="h2">Everything You Need to Scale</h2>
    <p class="sub">One platform to collect, analyze, and act on your business data — powered by AI that learns your business.</p>
  </div>
  <div class="feat-grid">
    @foreach([
      ['📊','Real-time Dashboards','Beautiful, customizable dashboards that update instantly as your data changes. Share with your team in one click.'],
      ['🤖','AI-Powered Insights','Our AI surfaces anomalies, predicts trends, and recommends actions before you even think to ask.'],
      ['🔗','200+ Integrations','Connect Stripe, Shopify, HubSpot, Salesforce, and 200+ more tools in minutes with no code required.'],
      ['📈','Predictive Analytics','Forecast revenue, churn, and growth with ML models trained on your specific business patterns.'],
      ['🔔','Smart Alerts','Get notified the moment something important changes — by email, Slack, or SMS. Never miss a critical metric.'],
      ['🔒','Enterprise Security','SOC 2 Type II certified, GDPR compliant, with SSO, RBAC, and audit logs built in from day one.'],
    ] as [$icon,$title,$desc])
    <div class="feat-card">
      <div class="feat-icon">{{ $icon }}</div>
      <div class="feat-title">{{ $title }}</div>
      <div class="feat-desc">{{ $desc }}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- PRICING -->
<section id="pricing" class="pricing">
  <div class="section-center">
    <div class="eyebrow">Pricing</div>
    <h2 class="h2">Simple, Transparent Pricing</h2>
    <p class="sub">Start for free. Scale as you grow. No hidden fees, no surprises.</p>
  </div>
  <div class="price-grid">
    <div class="price-card">
      <div class="price-tier">Starter</div>
      <div class="price-amount"><sup>$</sup>0</div>
      <div class="price-per">Free forever</div>
      <div class="price-desc">Perfect for solo founders and small teams just getting started with data.</div>
      <ul class="price-features">
        <li>Up to 5 data sources</li><li>3 dashboards</li><li>7-day data history</li><li>Email alerts</li><li>Community support</li>
      </ul>
      <a href="#" class="btn-plan btn-plan-outline">Get Started Free</a>
    </div>
    <div class="price-card popular">
      <div class="popular-badge">MOST POPULAR</div>
      <div class="price-tier">Pro</div>
      <div class="price-amount"><sup>$</sup>49</div>
      <div class="price-per">per month, billed annually</div>
      <div class="price-desc">For growing teams that need powerful analytics and collaboration tools.</div>
      <ul class="price-features">
        <li>Unlimited data sources</li><li>Unlimited dashboards</li><li>1-year data history</li><li>AI insights</li><li>Slack & email alerts</li><li>Priority support</li>
      </ul>
      <a href="#" class="btn-plan btn-plan-filled">Start Pro Trial</a>
    </div>
    <div class="price-card">
      <div class="price-tier">Enterprise</div>
      <div class="price-amount" style="font-size:42px;letter-spacing:-1px">Custom</div>
      <div class="price-per">Contact us for pricing</div>
      <div class="price-desc">For large organizations with advanced security, compliance, and scale requirements.</div>
      <ul class="price-features">
        <li>Everything in Pro</li><li>SSO / SAML</li><li>HIPAA / SOC 2</li><li>Custom SLA</li><li>Dedicated CSM</li><li>On-premise option</li>
      </ul>
      <a href="#" class="btn-plan btn-plan-outline">Contact Sales</a>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" class="testimonials">
  <div class="section-center">
    <div class="eyebrow">Customers</div>
    <h2 class="h2">Loved by 12,000+ Teams</h2>
  </div>
  <div class="test-grid">
    @foreach([
      ['"DataFlow replaced 4 separate analytics tools for us. The AI insights alone save our team 10+ hours per week."','🏢','Jordan Liu','VP Analytics, Stripe'],
      ['"We went from gut-feeling decisions to data-driven strategy in 2 weeks. Our revenue is up 34% since switching."','🚀','Priya Sharma','CEO, LaunchPad'],
      ['"The best analytics tool we\'ve ever used. Setup took 20 minutes and we had insights the same day. Incredible."','⚡','Marcus Reid','CTO, Flowbase'],
    ] as [$q,$ava,$n,$role])
    <div class="test-card">
      <div class="test-stars" style="color:#fbbf24;margin-bottom:14px">★★★★★</div>
      <p class="test-quote">{{ $q }}</p>
      <div class="test-author">
        <div class="test-ava" style="background:var(--light-blue)">{{ $ava }}</div>
        <div><div class="test-name">{{ $n }}</div><div class="test-co">{{ $role }}</div></div>
      </div>
    </div>
    @endforeach
  </div>
</section>

<!-- CTA -->
<div class="cta">
  <h2>Ready to See Your Data Come Alive?</h2>
  <p>Join 12,000+ companies using DataFlow to make smarter decisions, faster.</p>
  <div class="cta-btns">
    <a href="#" class="btn-cta-white">Start Free Trial →</a>
    <a href="#" class="btn-cta-ghost">Schedule Demo</a>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div>
    <span class="f-logo">Data<span>Flow</span></span>
    <p class="f-desc">Turning business data into decisions with the power of AI and beautiful analytics.</p>
  </div>
  <div>
    <div class="f-head">Product</div>
    <a class="f-link" href="#">Features</a><a class="f-link" href="#">Pricing</a>
    <a class="f-link" href="#">Integrations</a><a class="f-link" href="#">Changelog</a><a class="f-link" href="#">Roadmap</a>
  </div>
  <div>
    <div class="f-head">Company</div>
    <a class="f-link" href="#">About</a><a class="f-link" href="#">Blog</a>
    <a class="f-link" href="#">Careers</a><a class="f-link" href="#">Press</a><a class="f-link" href="#">Contact</a>
  </div>
  <div>
    <div class="f-head">Legal</div>
    <a class="f-link" href="#">Privacy</a><a class="f-link" href="#">Terms</a>
    <a class="f-link" href="#">Security</a><a class="f-link" href="#">GDPR</a>
  </div>
  <div class="f-bottom">
    <span>© 2024 DataFlow Inc. All rights reserved.</span>
    <span>Built with <span class="f-ryaan">RyaanCMS</span></span>
  </div>
</footer>

</body>
</html>
