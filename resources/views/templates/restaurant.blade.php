<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>La Bella Cucina — Fine Italian Dining</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--cream:#fdf6ec;--gold:#c9a84c;--dark:#1a0a00;--red:#8b2500;--text:#3d2b1f}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--text)}
a{text-decoration:none}

/* NAV */
nav{position:fixed;top:0;width:100%;z-index:100;background:rgba(26,10,0,.94);backdrop-filter:blur(12px);padding:18px 60px;display:flex;justify-content:space-between;align-items:center}
.logo{font-family:'Playfair Display',serif;font-size:22px;color:var(--gold);letter-spacing:1px}
.nav-links{display:flex;gap:36px}
.nav-links a{color:rgba(255,255,255,.75);font-size:13px;letter-spacing:1.5px;text-transform:uppercase;transition:color .2s}
.nav-links a:hover{color:var(--gold)}
.nav-reserve{background:var(--gold);color:var(--dark);padding:10px 26px;border-radius:2px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase}

/* HERO */
.hero{height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden;
  background:radial-gradient(ellipse at 30% 50%,#3d1800 0%,#1a0a00 60%)}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c9a84c' fill-opacity='0.04'%3E%3Cpath d='M40 40c0-11.046-8.954-20-20-20S0 28.954 0 40s8.954 20 20 20 20-8.954 20-20zm20-20c0-11.046-8.954-20-20-20S20 8.954 20 20 28.954 40 40 40 60 31.046 60 20z'/%3E%3C/g%3E%3C/svg%3E")}
.hero-badge{display:inline-block;border:1px solid rgba(201,168,76,.5);color:var(--gold);font-size:11px;letter-spacing:5px;text-transform:uppercase;padding:7px 22px;margin-bottom:28px}
.hero h1{font-family:'Playfair Display',serif;font-size:76px;color:#fff;line-height:1.05;margin-bottom:22px}
.hero h1 em{color:var(--gold);font-style:italic;display:block}
.hero p{color:rgba(255,255,255,.6);font-size:16px;max-width:460px;margin:0 auto 40px;line-height:1.8;font-weight:300}
.hero-btns{display:flex;gap:14px;justify-content:center}
.btn-gold{background:var(--gold);color:var(--dark);padding:15px 38px;font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;border-radius:2px}
.btn-outline{border:1px solid rgba(255,255,255,.35);color:#fff;padding:15px 38px;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;border-radius:2px;transition:all .2s}
.btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.scroll-hint{position:absolute;bottom:36px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.3);font-size:11px;letter-spacing:3px;text-transform:uppercase;display:flex;flex-direction:column;align-items:center;gap:8px}
.scroll-hint::after{content:'';width:1px;height:40px;background:linear-gradient(var(--gold),transparent)}

/* STRIP */
.gold-strip{background:var(--gold);padding:16px;text-align:center;display:flex;justify-content:center;gap:60px}
.gold-strip span{color:var(--dark);font-size:12px;letter-spacing:3px;text-transform:uppercase;font-weight:600}

/* ABOUT */
.about{padding:100px 60px;display:grid;grid-template-columns:1fr 1fr;gap:90px;align-items:center;max-width:1240px;margin:0 auto}
.about-visual{height:520px;background:linear-gradient(145deg,#2d1200,#5c2a00,#3d1800);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:100px;position:relative}
.about-visual::after{content:'Since 1987';position:absolute;bottom:24px;right:24px;background:var(--gold);color:var(--dark);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:8px 16px}
.section-eyebrow{font-size:11px;letter-spacing:5px;text-transform:uppercase;color:var(--gold);margin-bottom:16px;font-weight:600}
.section-h2{font-family:'Playfair Display',serif;font-size:46px;color:var(--dark);line-height:1.15;margin-bottom:22px}
.body-text{color:#5d4037;line-height:1.85;margin-bottom:18px;font-size:15px}
.chef-sig{font-family:'Playfair Display',serif;font-style:italic;color:var(--gold);font-size:26px;margin-top:8px}
.chef-title{font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#9e7060;margin-top:4px}

/* MENU */
.menu-sec{background:var(--dark);padding:100px 60px}
.menu-sec .section-h2{color:#fff}
.menu-header{text-align:center;max-width:540px;margin:0 auto 64px}
.menu-header .body-text{color:rgba(255,255,255,.5)}
.menu-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;max-width:1200px;margin:0 auto}
.menu-card{background:rgba(255,255,255,.03);border:1px solid rgba(201,168,76,.12);padding:40px 32px;transition:all .2s}
.menu-card:hover{background:rgba(201,168,76,.07);border-color:rgba(201,168,76,.3)}
.m-emoji{font-size:40px;margin-bottom:18px}
.m-name{font-family:'Playfair Display',serif;color:#fff;font-size:21px;margin-bottom:10px}
.m-desc{color:rgba(255,255,255,.45);font-size:13px;line-height:1.7;margin-bottom:18px}
.m-price{color:var(--gold);font-size:20px;font-weight:600}
.m-line{width:32px;height:1px;background:var(--gold);margin-bottom:14px}

/* GALLERY */
.gallery{padding:80px 60px;max-width:1200px;margin:0 auto}
.gallery-grid{display:grid;grid-template-columns:2fr 1fr 1fr;grid-template-rows:260px 260px;gap:12px;margin-top:48px}
.g-cell{background:linear-gradient(135deg,#2d1200,#5c2a00);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:60px}
.g-cell:first-child{grid-row:1/3}

/* RESERVATION */
.reservation{background:linear-gradient(135deg,#8b2500 0%,#5c1800 100%);padding:100px 60px;text-align:center}
.reservation .section-h2{color:#fff}
.reservation .section-eyebrow{color:rgba(255,255,255,.6)}
.res-sub{color:rgba(255,255,255,.65);max-width:480px;margin:0 auto 44px;font-size:15px;line-height:1.7}
.res-form{display:flex;gap:10px;max-width:760px;margin:0 auto;flex-wrap:wrap;justify-content:center}
.res-input{flex:1;min-width:180px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);color:#fff;padding:15px 18px;font-size:14px;border-radius:2px;font-family:'Inter',sans-serif}
.res-input::placeholder{color:rgba(255,255,255,.45)}
.res-btn{background:var(--gold);color:var(--dark);padding:15px 36px;font-weight:800;font-size:12px;letter-spacing:2px;border:none;cursor:pointer;border-radius:2px;text-transform:uppercase}

/* FOOTER */
footer{background:#0d0500;padding:72px 60px 0;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px}
.f-logo{font-family:'Playfair Display',serif;font-size:24px;color:var(--gold);margin-bottom:16px;display:block}
.f-about{color:rgba(255,255,255,.4);font-size:13px;line-height:1.8;max-width:260px}
.f-head{color:var(--gold);font-size:10px;letter-spacing:4px;text-transform:uppercase;margin-bottom:22px;font-weight:700}
.f-link{display:block;color:rgba(255,255,255,.45);font-size:13px;margin-bottom:10px;transition:color .2s}
.f-link:hover{color:var(--gold)}
.f-bottom{border-top:1px solid rgba(255,255,255,.07);margin:60px -60px 0;padding:22px 60px;display:flex;justify-content:space-between;align-items:center}
.f-copy{color:rgba(255,255,255,.25);font-size:12px}
.f-badge{background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.2);color:var(--gold);font-size:10px;letter-spacing:1px;padding:4px 12px;border-radius:2px}

@media(max-width:900px){
nav{padding:16px 20px}.nav-links{display:none}
.hero h1{font-size:44px}
.about{grid-template-columns:1fr;padding:60px 24px;gap:40px}
.menu-grid{grid-template-columns:1fr}
.menu-sec{padding:60px 24px}
.gallery-grid{grid-template-columns:1fr 1fr;grid-template-rows:auto}
.g-cell:first-child{grid-row:auto}
.gallery{padding:60px 24px}
.reservation{padding:60px 24px}
.res-form{flex-direction:column}
footer{grid-template-columns:1fr;padding:48px 24px 0;gap:36px}
.f-bottom{margin:40px -24px 0;padding:20px 24px;flex-direction:column;gap:10px}
}
</style>
</head>
<body>

<nav>
  <div class="logo">La Bella Cucina</div>
  <div class="nav-links">
    <a href="#about">Our Story</a>
    <a href="#menu">Menu</a>
    <a href="#gallery">Gallery</a>
    <a href="#reservation">Reservations</a>
    <a href="#contact">Contact</a>
  </div>
  <a href="#reservation" class="nav-reserve">Book a Table</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div style="position:relative;z-index:1">
    <div class="hero-badge">Est. 1987 — Milano, Italy</div>
    <h1>The Art of<em>Italian Cuisine</em></h1>
    <p>Experience authentic flavours passed through generations. Every dish a story of passion, tradition, and the finest Italian ingredients.</p>
    <div class="hero-btns">
      <a href="#menu" class="btn-gold">Explore Menu</a>
      <a href="#reservation" class="btn-outline">Reserve Tonight</a>
    </div>
  </div>
  <div class="scroll-hint">Scroll</div>
</section>

<!-- GOLD STRIP -->
<div class="gold-strip">
  <span>🍷 Award-Winning Wine List</span>
  <span>🍝 Handmade Pasta Daily</span>
  <span>🌿 Farm-to-Table Ingredients</span>
  <span>⭐ Michelin Recognized</span>
</div>

<!-- ABOUT -->
<section id="about" class="about">
  <div class="about-visual">🍝</div>
  <div>
    <div class="section-eyebrow">Our Story</div>
    <h2 class="section-h2">Born in the Heart of Milan</h2>
    <p class="body-text">La Bella Cucina has been serving authentic Italian cuisine for over three decades. Our recipes are sourced from generations of family tradition — unchanged, uncompromised, unmatched.</p>
    <p class="body-text">We import our pasta, olive oil, and prosciutto directly from Italy. Every dish is a journey back to the sunlit terraces of Tuscany and the bustling trattorias of Rome.</p>
    <div class="chef-sig">Chef Marco Rossi</div>
    <div class="chef-title">Executive Chef & Co-Founder</div>
  </div>
</section>

<!-- MENU -->
<section id="menu" class="menu-sec">
  <div class="menu-header">
    <div class="section-eyebrow">Our Specialties</div>
    <h2 class="section-h2">Signature Dishes</h2>
    <p class="body-text">Handcrafted with love, served with tradition</p>
  </div>
  <div class="menu-grid">
    @foreach([
      ['🍝','Tagliatelle al Ragù','Slow-cooked Bolognese on hand-rolled egg pasta, finished with aged Parmigiano-Reggiano','$28'],
      ['🍕','Pizza Margherita DOP','San Marzano tomatoes, buffalo mozzarella, fresh basil — wood-fired at 900°F','$22'],
      ['🥩','Bistecca Fiorentina','28-day aged T-bone, grilled to perfection with rosemary roasted potatoes','$68'],
      ['🦞','Risotto ai Frutti di Mare','Arborio rice with fresh lobster, shrimp, mussels, and saffron','$45'],
      ['🍮','Tiramisù della Casa','Espresso-soaked ladyfingers, mascarpone cream, Valrhona cocoa','$14'],
      ['🍷','Wine Pairing Menu','Sommelier-curated Barolo, Brunello, and Chianti Classico pairings','$55'],
    ] as [$e,$n,$d,$p])
    <div class="menu-card">
      <div class="m-emoji">{{$e}}</div>
      <div class="m-line"></div>
      <div class="m-name">{{$n}}</div>
      <div class="m-desc">{{$d}}</div>
      <div class="m-price">{{$p}}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- GALLERY -->
<section id="gallery" class="gallery">
  <div class="section-eyebrow">Ambience</div>
  <h2 class="section-h2">Inside La Bella Cucina</h2>
  <div class="gallery-grid">
    <div class="g-cell" style="font-size:90px">🏛️</div>
    <div class="g-cell">🕯️</div>
    <div class="g-cell">🍾</div>
    <div class="g-cell">🌹</div>
    <div class="g-cell">🎻</div>
  </div>
</section>

<!-- RESERVATION -->
<section id="reservation" class="reservation">
  <div class="section-eyebrow">Reserve Your Evening</div>
  <h2 class="section-h2">Make a Reservation</h2>
  <p class="res-sub">Join us for an unforgettable dining experience. Open Tuesday through Sunday, 5pm – 11pm.</p>
  <div class="res-form">
    <input class="res-input" type="text" placeholder="Your Name">
    <input class="res-input" type="date">
    <input class="res-input" type="number" placeholder="Guests" min="1" max="20">
    <input class="res-input" type="time" value="19:00">
    <button class="res-btn">Reserve Now</button>
  </div>
</section>

<!-- FOOTER -->
<footer id="contact">
  <div>
    <span class="f-logo">La Bella Cucina</span>
    <p class="f-about">Authentic Italian cuisine crafted with passion since 1987. Where every meal becomes a cherished memory.</p>
  </div>
  <div>
    <div class="f-head">Hours</div>
    <a class="f-link">Tue–Thu: 5pm – 10pm</a>
    <a class="f-link">Fri–Sat: 5pm – 11pm</a>
    <a class="f-link">Sunday: 4pm – 9pm</a>
    <a class="f-link" style="color:rgba(255,255,255,.2)">Monday: Closed</a>
  </div>
  <div>
    <div class="f-head">Location</div>
    <a class="f-link">📍 123 Via Roma, New York</a>
    <a class="f-link">📞 +1 (212) 555-0192</a>
    <a class="f-link">✉️ info@labellarestaurant.com</a>
  </div>
  <div>
    <div class="f-head">Follow Us</div>
    <a class="f-link">Instagram</a>
    <a class="f-link">Facebook</a>
    <a class="f-link">TripAdvisor</a>
    <a class="f-link">OpenTable</a>
  </div>
  <div class="f-bottom">
    <span class="f-copy">© 2024 La Bella Cucina. All rights reserved.</span>
    <span class="f-badge">Built with RyaanCMS</span>
  </div>
</footer>

</body>
</html>
