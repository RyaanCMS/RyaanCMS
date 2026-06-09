<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>PixelCraft — Creative Digital Agency</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--black:#080808;--white:#f5f5f0;--yellow:#d4f000;--gray:#111;--mid:#888;--border:rgba(255,255,255,.08)}
body{font-family:'Inter',sans-serif;background:var(--black);color:var(--white);overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* NAV */
nav{position:fixed;top:0;width:100%;z-index:100;padding:20px 60px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
.nav-logo{font-family:'Bebas Neue',sans-serif;font-size:26px;letter-spacing:2px;color:var(--yellow)}
.nav-links{display:flex;gap:36px}
.nav-links a{font-size:13px;color:rgba(255,255,255,.55);letter-spacing:.5px;font-weight:500;transition:color .15s}
.nav-links a:hover{color:var(--white)}
.nav-cta{background:var(--yellow);color:var(--black);padding:10px 26px;font-size:13px;font-weight:700;letter-spacing:.5px}

/* HERO */
.hero{min-height:100vh;padding:140px 60px 80px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden;background:var(--black)}
.hero-ticker{position:absolute;top:80px;left:0;right:0;white-space:nowrap;overflow:hidden;border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:12px 0}
.ticker-track{display:inline-flex;animation:ticker 20s linear infinite}
.ticker-item{font-family:'Bebas Neue',sans-serif;font-size:14px;letter-spacing:4px;color:rgba(255,255,255,.2);padding:0 40px}
.ticker-item.yellow{color:var(--yellow)}
@keyframes ticker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.hero-content{max-width:1200px;margin:0 auto;width:100%}
.hero-eyebrow{display:flex;align-items:center;gap:14px;margin-bottom:32px}
.hero-eyebrow span{font-size:12px;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,.4)}
.hero-line{width:40px;height:1px;background:rgba(255,255,255,.2)}
.hero h1{font-family:'Bebas Neue',sans-serif;font-size:clamp(80px,12vw,160px);line-height:.95;letter-spacing:2px;margin-bottom:40px}
.hero h1 em{color:var(--yellow);font-style:normal;display:block}
.hero-bottom{display:flex;justify-content:space-between;align-items:flex-end}
.hero p{max-width:400px;font-size:16px;color:rgba(255,255,255,.5);line-height:1.75}
.hero-btns{display:flex;gap:12px}
.btn-yellow{background:var(--yellow);color:var(--black);padding:16px 40px;font-size:14px;font-weight:700;letter-spacing:.3px}
.btn-ghost{border:1px solid rgba(255,255,255,.15);color:var(--white);padding:16px 40px;font-size:14px;font-weight:500;transition:border-color .2s}
.btn-ghost:hover{border-color:var(--yellow)}
.hero-stats{display:flex;gap:48px}
.h-stat-num{font-family:'Bebas Neue',sans-serif;font-size:56px;color:var(--yellow);letter-spacing:1px;line-height:1}
.h-stat-label{font-size:12px;color:rgba(255,255,255,.35);letter-spacing:1.5px;text-transform:uppercase;margin-top:4px}

/* MARQUEE BAND */
.marquee-band{border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:20px 0;overflow:hidden}
.marquee-track{display:inline-flex;animation:ticker 30s linear infinite}
.marquee-item{display:flex;align-items:center;gap:20px;padding:0 30px;font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:3px;color:rgba(255,255,255,.15)}
.marquee-dot{width:6px;height:6px;background:var(--yellow);border-radius:50%;flex-shrink:0}

/* SERVICES */
.services{padding:100px 60px}
.section-label{font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:5px;color:var(--yellow);margin-bottom:12px}
.section-h2{font-family:'Bebas Neue',sans-serif;font-size:64px;letter-spacing:2px;line-height:1;margin-bottom:60px}
.serv-list{display:grid;grid-template-columns:1fr 1fr;gap:0;max-width:1200px;margin:0 auto;border-top:1px solid var(--border)}
.serv-item{padding:40px 32px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:28px;transition:background .2s;cursor:default}
.serv-item:nth-child(odd){border-right:1px solid var(--border)}
.serv-item:hover{background:rgba(212,240,0,.04)}
.serv-num{font-family:'Bebas Neue',sans-serif;font-size:40px;color:rgba(255,255,255,.08);letter-spacing:1px;flex-shrink:0;line-height:1}
.serv-icon{font-size:36px;flex-shrink:0;margin-top:4px}
.serv-title{font-size:20px;font-weight:700;margin-bottom:8px}
.serv-desc{font-size:14px;color:rgba(255,255,255,.45);line-height:1.7}
.serv-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:14px}
.serv-tag{border:1px solid var(--border);color:rgba(255,255,255,.35);font-size:10px;letter-spacing:1px;padding:4px 12px}

/* WORK */
.work{padding:80px 60px;background:#0d0d0d}
.work-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:1200px;margin:0 auto}
.work-card{position:relative;overflow:hidden;cursor:pointer}
.work-img{aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:80px;transition:transform .4s}
.work-card:hover .work-img{transform:scale(1.05)}
.work-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.9) 0%,transparent 50%);padding:28px;display:flex;flex-direction:column;justify-content:flex-end;opacity:0;transition:opacity .3s}
.work-card:hover .work-overlay{opacity:1}
.work-cat{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--yellow);margin-bottom:8px}
.work-title{font-size:22px;font-weight:700}
.work-tag-work{display:inline-block;background:var(--yellow);color:var(--black);font-size:10px;font-weight:700;padding:4px 12px;position:absolute;top:16px;left:16px}

/* TEAM */
.team{padding:100px 60px}
.team-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;max-width:1200px;margin:60px auto 0}
.team-card{text-align:center}
.team-ava{width:100%;aspect-ratio:1;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:56px;margin-bottom:18px}
.team-name{font-size:17px;font-weight:700;margin-bottom:4px}
.team-role{font-size:13px;color:rgba(255,255,255,.4);margin-bottom:12px}
.team-social{display:flex;justify-content:center;gap:12px}
.team-social a{width:30px;height:30px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;color:rgba(255,255,255,.35);transition:all .2s}
.team-social a:hover{border-color:var(--yellow);color:var(--yellow)}

/* CTA */
.cta{margin:0 60px 100px;border:1px solid var(--border);padding:80px;display:flex;justify-content:space-between;align-items:center;gap:60px}
.cta h2{font-family:'Bebas Neue',sans-serif;font-size:72px;letter-spacing:2px;line-height:1}
.cta h2 em{color:var(--yellow);font-style:normal}
.cta p{color:rgba(255,255,255,.45);font-size:16px;line-height:1.7;max-width:360px;margin-bottom:32px}
.cta-btns{display:flex;gap:12px}

/* FOOTER */
footer{border-top:1px solid var(--border);padding:60px;display:grid;grid-template-columns:2fr 1fr 1fr;gap:80px}
.f-logo{font-family:'Bebas Neue',sans-serif;font-size:28px;letter-spacing:3px;color:var(--yellow);display:block;margin-bottom:14px}
.f-desc{font-size:13px;color:rgba(255,255,255,.35);line-height:1.8;max-width:260px}
.f-head{font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:20px}
.f-link{display:block;font-size:14px;color:rgba(255,255,255,.4);margin-bottom:12px;transition:color .15s}
.f-link:hover{color:var(--yellow)}
.f-bottom{border-top:1px solid var(--border);padding:20px 60px;display:flex;justify-content:space-between;font-size:12px;color:rgba(255,255,255,.2)}
.f-made{color:var(--yellow)}

@media(max-width:900px){
nav{padding:16px 20px}.nav-links{display:none}
.hero{padding:120px 20px 60px}
.hero h1{font-size:72px}
.hero-bottom{flex-direction:column;gap:32px;align-items:flex-start}
.hero-stats{flex-wrap:wrap;gap:24px}
.services{padding:60px 20px}
.section-h2{font-size:48px}
.serv-list{grid-template-columns:1fr}
.serv-item:nth-child(odd){border-right:none}
.work{padding:60px 20px}
.work-grid{grid-template-columns:1fr}
.team{padding:60px 20px}
.team-grid{grid-template-columns:1fr 1fr}
.cta{margin:0 20px 60px;padding:48px 24px;flex-direction:column}
.cta h2{font-size:52px}
footer{grid-template-columns:1fr;padding:48px 20px;gap:36px}
.f-bottom{padding:20px;flex-direction:column;gap:8px}
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">PixelCraft</div>
  <div class="nav-links">
    <a href="#services">Services</a><a href="#work">Work</a><a href="#team">Team</a><a href="#contact">Contact</a>
  </div>
  <a href="#contact" class="nav-cta">Start a Project</a>
</nav>

<!-- TICKER -->
<div class="hero" style="overflow:hidden">
  <div class="hero-ticker">
    <div class="ticker-track">
      @foreach(array_fill(0,8,'') as $_)
      <span class="ticker-item">WE CREATE</span>
      <span class="ticker-item yellow">●</span>
      <span class="ticker-item">WE DESIGN</span>
      <span class="ticker-item yellow">●</span>
      <span class="ticker-item">WE BUILD</span>
      <span class="ticker-item yellow">●</span>
      @endforeach
    </div>
  </div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <div class="hero-line"></div>
      <span>Award-Winning Creative Agency · Est. 2016</span>
    </div>
    <h1>WE BUILD<em>DIGITAL</em>LEGENDS</h1>
    <div class="hero-bottom">
      <p>We are a team of designers, developers, and strategists obsessed with crafting digital experiences that leave a mark.</p>
      <div class="hero-btns">
        <a href="#work" class="btn-yellow">See Our Work</a>
        <a href="#contact" class="btn-ghost">Let's Talk</a>
      </div>
      <div class="hero-stats">
        <div><div class="h-stat-num">120+</div><div class="h-stat-label">Projects Done</div></div>
        <div><div class="h-stat-num">8</div><div class="h-stat-label">Years Active</div></div>
        <div><div class="h-stat-num">40+</div><div class="h-stat-label">Clients</div></div>
      </div>
    </div>
  </div>
</div>

<!-- MARQUEE BAND -->
<div class="marquee-band">
  <div class="marquee-track">
    @foreach(array_fill(0,6,'') as $_)
    <div class="marquee-item"><div class="marquee-dot"></div>BRANDING</div>
    <div class="marquee-item"><div class="marquee-dot"></div>WEB DESIGN</div>
    <div class="marquee-item"><div class="marquee-dot"></div>DEVELOPMENT</div>
    <div class="marquee-item"><div class="marquee-dot"></div>MOTION</div>
    <div class="marquee-item"><div class="marquee-dot"></div>STRATEGY</div>
    @endforeach
  </div>
</div>

<!-- SERVICES -->
<section id="services" class="services">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-label">What We Do</div>
    <h2 class="section-h2">Our Services</h2>
    <div class="serv-list">
      @foreach([
        ['01','🎨','Brand Identity','We craft visual identities that are bold, timeless, and impossible to ignore — from logo systems to full brand guidelines.',['Logo Design','Brand Strategy','Guidelines','Packaging']],
        ['02','🌐','Web Design & Dev','Award-winning websites built for speed, beauty, and conversion. We design and develop every pixel with intention.',['UI/UX','Webflow','React','Laravel']],
        ['03','📱','Mobile Apps','Native and cross-platform apps that users love — from concept and wireframes to App Store launch.',['iOS','Android','React Native','UX Design']],
        ['04','🎬','Motion & 3D','Animations, explainer videos, and 3D renders that bring your brand to life and stop thumbs mid-scroll.',['Animation','3D','Video','AR/VR']],
        ['05','📊','Digital Strategy','Data-driven strategy that aligns your brand with your business goals — from positioning to campaign execution.',['SEO','Analytics','Growth','Campaigns']],
        ['06','🛒','E-commerce','High-converting online stores built on Shopify, WooCommerce, or custom platforms that scale with your business.',['Shopify','WooCommerce','Custom','CRO']],
      ] as [$num,$icon,$title,$desc,$tags])
      <div class="serv-item">
        <div class="serv-num">{{ $num }}</div>
        <div>
          <div style="font-size:32px;margin-bottom:10px">{{ $icon }}</div>
          <div class="serv-title">{{ $title }}</div>
          <div class="serv-desc">{{ $desc }}</div>
          <div class="serv-tags">@foreach($tags as $t)<span class="serv-tag">{{ $t }}</span>@endforeach</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- WORK -->
<section id="work" class="work">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-label">Selected Work</div>
    <h2 class="section-h2">Our Portfolio</h2>
    <div class="work-grid">
      @foreach([
        ['🏦','linear-gradient(135deg,#1a0050,#0d0028)','Finance','NeoBank Rebrand','Brand identity + web design for a digital-first banking platform'],
        ['🍃','linear-gradient(135deg,#001a0a,#003318)','E-commerce','Botanica Store','Full Shopify redesign with 3D product visualizations'],
        ['🎵','linear-gradient(135deg,#1a0000,#330000)','Entertainment','Pulse Music App','Mobile app UI/UX + brand identity for streaming platform'],
        ['🏗️','linear-gradient(135deg,#0d0d00,#1a1a00)','Architecture','Vertex Studios','Portfolio website for award-winning architecture firm'],
      ] as [$e,$bg,$cat,$title,$desc])
      <div class="work-card">
        <div class="work-img" style="background:{{ $bg }}">{{ $e }}</div>
        <div class="work-tag-work">{{ $cat }}</div>
        <div class="work-overlay">
          <div class="work-cat">{{ $cat }}</div>
          <div class="work-title">{{ $title }}</div>
          <p style="font-size:13px;color:rgba(255,255,255,.6);margin-top:6px">{{ $desc }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- TEAM -->
<section id="team" class="team">
  <div style="max-width:1200px;margin:0 auto">
    <div class="section-label">The Crew</div>
    <h2 class="section-h2">Meet the Team</h2>
    <div class="team-grid">
      @foreach([
        ['👨‍🎨','#1a1400','Jordan Blake','Creative Director'],
        ['👩‍💻','#0d1a00','Sam Rivera','Lead Developer'],
        ['👨‍💼','#00101a','Alex Kim','Strategy Lead'],
        ['👩‍🎨','#1a000d','Mia Chen','Motion Designer'],
      ] as [$ava,$bg,$name,$role])
      <div class="team-card">
        <div class="team-ava" style="background:{{ $bg }}">{{ $ava }}</div>
        <div class="team-name">{{ $name }}</div>
        <div class="team-role">{{ $role }}</div>
        <div class="team-social">
          <a href="#">in</a><a href="#">tw</a><a href="#">dr</a>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- CTA -->
<div id="contact" class="cta">
  <h2>READY TO BUILD SOMETHING<em>LEGENDARY?</em></h2>
  <div>
    <p>Let's create something that makes people stop, look twice, and remember your brand forever. We'd love to hear about your project.</p>
    <div class="cta-btns">
      <a href="mailto:hello@pixelcraft.studio" class="btn-yellow">Start a Project →</a>
      <a href="#" class="btn-ghost">Schedule a Call</a>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div>
    <span class="f-logo">PixelCraft</span>
    <p class="f-desc">A creative digital agency obsessed with bold design, clean code, and unforgettable brands since 2016.</p>
  </div>
  <div>
    <div class="f-head">Navigate</div>
    <a class="f-link" href="#">Services</a><a class="f-link" href="#">Work</a>
    <a class="f-link" href="#">Team</a><a class="f-link" href="#">Blog</a><a class="f-link" href="#">Contact</a>
  </div>
  <div>
    <div class="f-head">Connect</div>
    <a class="f-link" href="#">Instagram</a><a class="f-link" href="#">Twitter</a>
    <a class="f-link" href="#">Dribbble</a><a class="f-link" href="#">Behance</a><a class="f-link" href="#">LinkedIn</a>
  </div>
</footer>
<div class="f-bottom">
  <span>© 2024 PixelCraft Studio. All rights reserved.</span>
  <span>Built with <span class="f-made">RyaanCMS</span></span>
</div>

</body>
</html>
