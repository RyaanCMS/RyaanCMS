<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Alex Morgan — UX Designer & Developer</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0b0c10;--surface:#13151a;--card:#1a1d24;--purple:#a855f7;--cyan:#22d3ee;--white:#f4f4f8;--muted:#8b8fa8;--border:rgba(255,255,255,.07)}
body{font-family:'Space Grotesk',sans-serif;background:var(--bg);color:var(--white);overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* NAV */
nav{position:fixed;top:0;width:100%;z-index:100;padding:20px 60px;display:flex;justify-content:space-between;align-items:center;backdrop-filter:blur(20px);border-bottom:1px solid var(--border)}
.nav-brand{font-family:'Space Mono',monospace;font-size:18px;font-weight:700;background:linear-gradient(135deg,var(--purple),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.nav-links{display:flex;gap:32px}
.nav-links a{font-size:14px;color:var(--muted);font-weight:500;transition:color .2s}
.nav-links a:hover{color:var(--white)}
.nav-cta{background:linear-gradient(135deg,var(--purple),#7c3aed);color:#fff;padding:10px 24px;border-radius:100px;font-size:13px;font-weight:600}

/* HERO */
.hero{min-height:100vh;display:flex;align-items:center;padding:120px 60px 80px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;background:radial-gradient(circle,rgba(168,85,247,.2) 0%,transparent 70%);pointer-events:none}
.hero::after{content:'';position:absolute;bottom:-100px;left:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(34,211,238,.1) 0%,transparent 70%);pointer-events:none}
.hero-layout{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;max-width:1200px;margin:0 auto;width:100%;position:relative;z-index:1}
.hero-available{display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:13px;color:var(--muted)}
.hero-dot{width:8px;height:8px;background:#4ade80;border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-size:66px;font-weight:700;line-height:1.05;letter-spacing:-2px;margin-bottom:20px}
.hero h1 .name{background:linear-gradient(135deg,var(--purple),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-role{font-family:'Space Mono',monospace;color:var(--cyan);font-size:14px;letter-spacing:2px;margin-bottom:20px}
.hero p{color:var(--muted);font-size:16px;line-height:1.75;max-width:440px;margin-bottom:36px}
.hero-btns{display:flex;gap:14px;align-items:center}
.btn-gradient{background:linear-gradient(135deg,var(--purple),var(--cyan));color:#fff;padding:14px 34px;border-radius:100px;font-size:14px;font-weight:600}
.btn-ghost{border:1px solid var(--border);color:var(--white);padding:14px 34px;border-radius:100px;font-size:14px;font-weight:500;transition:border-color .2s}
.btn-ghost:hover{border-color:var(--purple)}
.hero-stats{display:flex;gap:36px;margin-top:44px}
.stat-num{font-size:32px;font-weight:700;background:linear-gradient(135deg,var(--purple),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.stat-label{font-size:12px;color:var(--muted);margin-top:2px}
.hero-visual{position:relative}
.avatar-wrap{width:420px;height:420px;border-radius:32px;background:linear-gradient(135deg,#1a1d24,#13151a);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:120px;position:relative;overflow:hidden}
.avatar-wrap::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(168,85,247,.1),rgba(34,211,238,.1))}
.float-badge{position:absolute;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:12px 16px;display:flex;align-items:center;gap:10px}
.fb-1{bottom:-16px;left:-20px}
.fb-2{top:-16px;right:-20px}
.fb-icon{font-size:20px}
.fb-text{font-size:12px;font-weight:600;color:var(--white)}
.fb-sub{font-size:10px;color:var(--muted)}

/* SKILLS */
.skills{padding:80px 60px;background:var(--surface)}
.section-mono{font-family:'Space Mono',monospace;font-size:12px;color:var(--cyan);letter-spacing:3px;text-transform:uppercase;margin-bottom:10px}
.section-h2{font-size:44px;font-weight:700;letter-spacing:-1.5px;margin-bottom:48px}
.skills-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;max-width:1200px;margin:0 auto}
.skill-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px 24px;transition:all .2s}
.skill-card:hover{border-color:rgba(168,85,247,.4);transform:translateY(-2px)}
.skill-icon{font-size:32px;margin-bottom:14px}
.skill-name{font-weight:600;font-size:16px;margin-bottom:6px}
.skill-level{font-size:12px;color:var(--muted);margin-bottom:14px}
.skill-bar{height:3px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden}
.skill-fill{height:100%;border-radius:2px;background:linear-gradient(135deg,var(--purple),var(--cyan))}

/* WORK */
.work{padding:80px 60px}
.work-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:1200px;margin:48px auto 0}
.work-card{background:var(--card);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all .25s;cursor:pointer}
.work-card:hover{transform:translateY(-4px);border-color:rgba(168,85,247,.4)}
.work-img{height:220px;display:flex;align-items:center;justify-content:center;font-size:64px;position:relative}
.work-badge{position:absolute;top:16px;left:16px;background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:var(--purple);font-size:10px;font-weight:700;letter-spacing:1px;padding:4px 12px;border-radius:100px}
.work-body{padding:24px}
.work-title{font-weight:700;font-size:18px;margin-bottom:6px}
.work-desc{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px}
.work-tags{display:flex;gap:6px;flex-wrap:wrap}
.work-tag{background:rgba(255,255,255,.05);color:var(--muted);font-size:11px;padding:4px 10px;border-radius:100px}

/* TESTIMONIALS */
.testimonials{background:var(--surface);padding:80px 60px}
.test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:1200px;margin:48px auto 0}
.test-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:28px}
.test-stars{color:#fbbf24;font-size:14px;margin-bottom:14px}
.test-text{font-size:14px;color:var(--muted);line-height:1.75;margin-bottom:20px;font-style:italic}
.test-author{display:flex;align-items:center;gap:12px}
.test-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--purple),var(--cyan));display:flex;align-items:center;justify-content:center;font-size:18px}
.test-name{font-weight:700;font-size:14px}
.test-role{font-size:12px;color:var(--muted)}

/* CONTACT */
.contact{padding:80px 60px;text-align:center;position:relative;overflow:hidden}
.contact::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:500px;background:radial-gradient(circle,rgba(168,85,247,.15) 0%,transparent 70%);pointer-events:none}
.contact-card{max-width:640px;margin:0 auto;background:var(--card);border:1px solid var(--border);border-radius:28px;padding:60px;position:relative;z-index:1}
.contact h3{font-size:42px;font-weight:700;letter-spacing:-1.5px;margin:16px 0 12px}
.contact p{color:var(--muted);font-size:15px;line-height:1.7;margin-bottom:32px}
.contact-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.contact-btn{background:linear-gradient(135deg,var(--purple),var(--cyan));color:#fff;padding:14px 36px;border-radius:100px;font-weight:600;font-size:14px}
.contact-email{border:1px solid var(--border);color:var(--white);padding:14px 36px;border-radius:100px;font-size:14px;font-weight:500}

/* FOOTER */
footer{background:var(--surface);border-top:1px solid var(--border);padding:32px 60px;display:flex;justify-content:space-between;align-items:center}
.f-brand{font-family:'Space Mono',monospace;font-weight:700;font-size:16px;background:linear-gradient(135deg,var(--purple),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.f-links{display:flex;gap:24px}
.f-links a{font-size:13px;color:var(--muted)}
.f-badge{font-size:12px;color:var(--muted)}
.f-badge span{color:var(--purple)}

@media(max-width:900px){
nav{padding:16px 20px}.nav-links{display:none}
.hero{padding:100px 20px 60px}
.hero-layout{grid-template-columns:1fr}
.hero h1{font-size:44px}
.avatar-wrap{width:100%;height:280px}
.skills,.work,.testimonials,.contact{padding:60px 20px}
.skills-grid,.work-grid,.test-grid{grid-template-columns:1fr}
footer{padding:24px 20px;flex-direction:column;gap:16px;text-align:center}
}
</style>
</head>
<body>

<nav>
  <div class="nav-brand">alex.dev</div>
  <div class="nav-links">
    <a href="#work">Work</a><a href="#skills">Skills</a><a href="#testimonials">Reviews</a><a href="#contact">Contact</a>
  </div>
  <a href="#contact" class="nav-cta">Hire Me →</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-layout">
    <div>
      <div class="hero-available"><div class="hero-dot"></div> Available for freelance work</div>
      <div class="hero-role">// UX Designer & Full-Stack Developer</div>
      <h1>Hi, I'm <span class="name">Alex Morgan</span></h1>
      <p>I craft beautiful digital experiences that live at the intersection of design and engineering. Helping brands tell their story through pixels and code.</p>
      <div class="hero-btns">
        <a href="#work" class="btn-gradient">View My Work</a>
        <a href="#contact" class="btn-ghost">Let's Talk</a>
      </div>
      <div class="hero-stats">
        <div><div class="stat-num">6+</div><div class="stat-label">Years Experience</div></div>
        <div><div class="stat-num">80+</div><div class="stat-label">Projects Done</div></div>
        <div><div class="stat-num">98%</div><div class="stat-label">Client Satisfaction</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="avatar-wrap">👨‍💻
        <div class="float-badge fb-1"><div class="fb-icon">⭐</div><div><div class="fb-text">Top Rated</div><div class="fb-sub">Upwork Pro</div></div></div>
        <div class="float-badge fb-2"><div class="fb-icon">🚀</div><div><div class="fb-text">80+ Projects</div><div class="fb-sub">Delivered</div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- SKILLS -->
<section id="skills" class="skills">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-mono">// What I Do</div>
    <h2 class="section-h2">Skills & Expertise</h2>
    <div class="skills-grid">
      @foreach([
        ['🎨','UI/UX Design','Expert','95'],['⚛️','React / Next.js','Expert','92'],
        ['🐘','Laravel / PHP','Advanced','88'],['🗄️','Database Design','Advanced','85'],
        ['📱','Mobile Design','Proficient','80'],['☁️','Cloud / DevOps','Proficient','75'],
        ['🔐','Security & Auth','Advanced','82'],['📊','Data Visualization','Proficient','78'],
      ] as [$icon,$name,$level,$pct])
      <div class="skill-card">
        <div class="skill-icon">{{ $icon }}</div>
        <div class="skill-name">{{ $name }}</div>
        <div class="skill-level">{{ $level }}</div>
        <div class="skill-bar"><div class="skill-fill" style="width:{{ $pct }}%"></div></div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- WORK -->
<section id="work" class="work">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-mono">// Selected Projects</div>
    <h2 class="section-h2">My Work</h2>
    <div class="work-grid">
      @foreach([
        ['🏦','#1a1d24','FinVault Banking App','Mobile banking redesign with biometric auth and AI spending insights','UI/UX','React Native','Fintech'],
        ['🛒','#1d1a24','ShopSmart E-commerce','Full-stack e-commerce platform processing $2M+ in monthly transactions','Next.js','Laravel','E-commerce'],
        ['🏥','#1a241d','MedFlow Healthcare','HIPAA-compliant telehealth platform connecting 50K+ patients to doctors','Vue.js','Node.js','Healthcare'],
      ] as [$e,$bg,$title,$desc,...$tags])
      <div class="work-card">
        <div class="work-img" style="background:{{ $bg }}">{{ $e }}<div class="work-badge">Case Study</div></div>
        <div class="work-body">
          <div class="work-title">{{ $title }}</div>
          <div class="work-desc">{{ $desc }}</div>
          <div class="work-tags">@foreach($tags as $t)<span class="work-tag">{{ $t }}</span>@endforeach</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" class="testimonials">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-mono">// Client Voices</div>
    <h2 class="section-h2">What Clients Say</h2>
    <div class="test-grid">
      @foreach([
        ['"Alex transformed our product from clunky to world-class. The attention to detail and communication throughout the project was exceptional."','Sarah Chen','CTO, FinVault Inc.'],
        ['"Delivered a complex platform on time and under budget. The code quality was outstanding — maintainable, scalable, and beautifully architected."','Marcus Torres','Founder, ShopSmart'],
        ['"Working with Alex felt like having a senior product designer and developer on our team. Highly recommended for any serious project."','Dr. Priya Patel','CEO, MedFlow Health'],
      ] as [$text,$name,$role])
      <div class="test-card">
        <div class="test-stars">★★★★★</div>
        <p class="test-text">{{ $text }}</p>
        <div class="test-author">
          <div class="test-avatar">👤</div>
          <div><div class="test-name">{{ $name }}</div><div class="test-role">{{ $role }}</div></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact" class="contact">
  <div class="contact-card">
    <div class="section-mono">// Let's Build Together</div>
    <h3>Start a Project</h3>
    <p>Have an idea? I'm available for freelance projects and full-time opportunities. Let's create something extraordinary together.</p>
    <div class="contact-btns">
      <a href="mailto:alex@alexmorgan.dev" class="contact-btn">📧 Send Email</a>
      <a href="#" class="contact-email">Schedule a Call →</a>
    </div>
  </div>
</section>

<footer>
  <div class="f-brand">alex.dev</div>
  <div class="f-links"><a href="#">GitHub</a><a href="#">LinkedIn</a><a href="#">Dribbble</a><a href="#">Twitter</a></div>
  <div class="f-badge">© 2024 Alex Morgan · Built with <span>RyaanCMS</span></div>
</footer>

</body>
</html>
