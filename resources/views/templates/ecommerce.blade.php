<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>StyleHub — Fashion & Lifestyle Store</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--black:#0a0a0a;--white:#fff;--coral:#ff4d4d;--gray:#f5f5f5;--mid:#888;--border:#e8e8e8}
body{font-family:'Inter',sans-serif;color:var(--black);background:var(--white)}
a{text-decoration:none;color:inherit}

/* NAV */
nav{position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid var(--border);padding:0 48px;display:flex;align-items:center;justify-content:space-between;height:64px}
.nav-logo{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;letter-spacing:-0.5px}
.nav-logo span{color:var(--coral)}
.nav-links{display:flex;gap:32px}
.nav-links a{font-size:13px;font-weight:500;color:var(--mid);letter-spacing:.3px;transition:color .15s}
.nav-links a:hover{color:var(--black)}
.nav-right{display:flex;align-items:center;gap:20px}
.nav-cart{background:var(--black);color:#fff;padding:9px 22px;border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.5px;display:flex;align-items:center;gap:8px}
.cart-count{background:var(--coral);color:#fff;width:18px;height:18px;border-radius:50%;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center}

/* HERO */
.hero{display:grid;grid-template-columns:1fr 1fr;min-height:88vh;background:var(--gray)}
.hero-content{display:flex;flex-direction:column;justify-content:center;padding:80px 60px}
.hero-tag{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--border);border-radius:100px;padding:6px 16px;font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:28px;width:fit-content}
.hero-tag::before{content:'';width:6px;height:6px;background:var(--coral);border-radius:50%}
.hero h1{font-family:'Syne',sans-serif;font-size:68px;font-weight:800;line-height:1;letter-spacing:-2px;margin-bottom:22px}
.hero h1 em{color:var(--coral);font-style:italic}
.hero p{font-size:16px;color:var(--mid);line-height:1.7;max-width:400px;margin-bottom:36px}
.hero-btns{display:flex;gap:12px;align-items:center}
.btn-black{background:var(--black);color:#fff;padding:15px 36px;border-radius:100px;font-size:13px;font-weight:600;letter-spacing:.3px}
.btn-ghost{color:var(--black);font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px}
.btn-ghost::after{content:'→';transition:transform .2s}
.btn-ghost:hover::after{transform:translateX(4px)}
.hero-visual{background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);display:flex;align-items:center;justify-content:center;font-size:120px;position:relative;overflow:hidden}
.hero-visual::after{content:'NEW SEASON';position:absolute;top:32px;right:-28px;background:var(--coral);color:#fff;font-family:'Syne',sans-serif;font-weight:800;font-size:12px;letter-spacing:3px;padding:8px 48px;transform:rotate(45deg)}

/* BRANDS */
.brands{border-bottom:1px solid var(--border);padding:20px 48px;display:flex;justify-content:center;gap:60px;align-items:center}
.brand-name{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;color:#ccc;letter-spacing:-0.5px}

/* CATEGORIES */
.categories{padding:80px 48px}
.section-label{font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--mid);margin-bottom:8px}
.section-h2{font-family:'Syne',sans-serif;font-size:42px;font-weight:800;letter-spacing:-1px;margin-bottom:48px}
.cat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.cat-card{aspect-ratio:3/4;border-radius:16px;display:flex;flex-direction:column;justify-content:flex-end;padding:24px;position:relative;overflow:hidden;cursor:pointer;transition:transform .3s}
.cat-card:hover{transform:translateY(-4px)}
.cat-card::before{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.7) 0%,transparent 60%)}
.cat-card .cat-emoji{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:72px;transition:transform .3s}
.cat-card:hover .cat-emoji{transform:translate(-50%,-50%) scale(1.15)}
.cat-title{position:relative;color:#fff;font-family:'Syne',sans-serif;font-weight:700;font-size:20px}
.cat-count{position:relative;color:rgba(255,255,255,.65);font-size:13px;margin-top:4px}

/* PRODUCTS */
.products{padding:0 48px 80px}
.section-top{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px}
.view-all{font-size:13px;font-weight:600;border-bottom:1px solid var(--black);padding-bottom:2px}
.prod-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px}
.prod-card{cursor:pointer}
.prod-img{aspect-ratio:3/4;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:56px;margin-bottom:14px;position:relative;overflow:hidden;transition:transform .3s}
.prod-card:hover .prod-img{transform:scale(1.02)}
.prod-tag{position:absolute;top:12px;left:12px;background:var(--coral);color:#fff;font-size:10px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:100px}
.prod-wish{position:absolute;top:12px;right:12px;width:32px;height:32px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px}
.prod-name{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;margin-bottom:4px}
.prod-brand{font-size:12px;color:var(--mid);margin-bottom:8px}
.prod-price{display:flex;gap:10px;align-items:center}
.prod-price .new{font-weight:700;font-size:16px}
.prod-price .old{font-size:13px;color:var(--mid);text-decoration:line-through}
.prod-add{margin-top:12px;width:100%;background:var(--black);color:#fff;border:none;border-radius:100px;padding:11px;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s}
.prod-add:hover{background:#333}

/* BANNER */
.promo-banner{margin:0 48px 80px;background:linear-gradient(135deg,#0a0a0a 0%,#1a1a2e 100%);border-radius:24px;padding:72px 80px;display:flex;justify-content:space-between;align-items:center}
.promo-tag{background:var(--coral);color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:6px 16px;border-radius:100px;width:fit-content;margin-bottom:20px}
.promo-h2{font-family:'Syne',sans-serif;font-size:52px;font-weight:800;color:#fff;letter-spacing:-1.5px;line-height:1.05;margin-bottom:12px}
.promo-sub{color:rgba(255,255,255,.55);font-size:15px;line-height:1.6;max-width:400px;margin-bottom:32px}
.promo-btn{background:var(--coral);color:#fff;padding:16px 40px;border-radius:100px;font-size:14px;font-weight:700;display:inline-block}
.promo-visual{font-size:100px}

/* NEWSLETTER */
.newsletter{background:var(--gray);padding:80px 48px;text-align:center}
.newsletter .section-h2{margin-bottom:12px}
.newsletter p{color:var(--mid);font-size:15px;max-width:440px;margin:0 auto 32px}
.nl-form{display:flex;gap:10px;max-width:480px;margin:0 auto}
.nl-input{flex:1;border:1px solid var(--border);border-radius:100px;padding:14px 22px;font-size:14px;font-family:'Inter',sans-serif;outline:none}
.nl-input:focus{border-color:var(--black)}
.nl-btn{background:var(--black);color:#fff;border:none;border-radius:100px;padding:14px 28px;font-size:13px;font-weight:600;cursor:pointer}

/* FOOTER */
footer{background:var(--black);color:rgba(255,255,255,.5);padding:60px 48px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px}
.f-logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:#fff;margin-bottom:12px;display:block}
.f-logo span{color:var(--coral)}
.f-about{font-size:13px;line-height:1.7;max-width:240px}
.f-head{color:#fff;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:20px}
.f-link{display:block;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:10px;transition:color .15s}
.f-link:hover{color:#fff}
.f-bottom{border-top:1px solid rgba(255,255,255,.08);margin-top:48px;padding-top:20px;display:flex;justify-content:space-between;font-size:12px}
.f-badge{color:var(--coral);font-weight:600}

@media(max-width:900px){
nav{padding:0 20px}
.nav-links{display:none}
.hero{grid-template-columns:1fr;min-height:auto}
.hero-content{padding:60px 24px}
.hero h1{font-size:44px}
.hero-visual{height:360px}
.brands{padding:20px;gap:24px;flex-wrap:wrap}
.categories,.products{padding:60px 20px}
.cat-grid{grid-template-columns:1fr 1fr}
.prod-grid{grid-template-columns:1fr 1fr}
.promo-banner{margin:0 20px 60px;padding:48px 32px;flex-direction:column;gap:32px}
.promo-h2{font-size:34px}
.newsletter{padding:60px 20px}
.nl-form{flex-direction:column}
footer{grid-template-columns:1fr;padding:48px 20px;gap:32px}
}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">Style<span>Hub</span></div>
  <div class="nav-links">
    <a href="#">Women</a><a href="#">Men</a><a href="#">Kids</a><a href="#">Sale</a><a href="#">New In</a>
  </div>
  <div class="nav-right">
    <span style="font-size:13px;color:var(--mid)">🔍</span>
    <span style="font-size:16px">♡</span>
    <div class="nav-cart">🛒 Cart <div class="cart-count">3</div></div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-tag">Summer 2024 Collection</div>
    <h1>Wear Your <em>Story</em></h1>
    <p>Discover fashion that speaks before you do. Curated styles for every chapter of your life — bold, minimal, and always you.</p>
    <div class="hero-btns">
      <a href="#products" class="btn-black">Shop Now</a>
      <a href="#categories" class="btn-ghost">Browse Categories</a>
    </div>
  </div>
  <div class="hero-visual">👗</div>
</section>

<!-- BRANDS -->
<div class="brands">
  @foreach(['Versace','Gucci','Prada','Zara','H&M','Levi\'s'] as $b)
  <span class="brand-name">{{ $b }}</span>
  @endforeach
</div>

<!-- CATEGORIES -->
<section id="categories" class="categories">
  <div class="section-label">Shop By</div>
  <h2 class="section-h2">Top Categories</h2>
  <div class="cat-grid">
    @foreach([
      ['👗','#1a1a2e','Women\'s Fashion','1,240 items'],
      ['👔','#16213e','Men\'s Collection','980 items'],
      ['👟','#0f3460','Footwear','640 items'],
      ['👜','#1a0a2e','Accessories','420 items'],
    ] as [$emoji,$bg,$title,$count])
    <div class="cat-card" style="background:{{ $bg }}">
      <div class="cat-emoji">{{ $emoji }}</div>
      <div class="cat-title">{{ $title }}</div>
      <div class="cat-count">{{ $count }}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="products">
  <div class="section-top">
    <div>
      <div class="section-label">Trending Now</div>
      <h2 class="section-h2" style="margin-bottom:0">Featured Products</h2>
    </div>
    <a href="#" class="view-all">View All →</a>
  </div>
  <div class="prod-grid">
    @foreach([
      ['👗','#f5f0ff','Summer Maxi Dress','Zara Studio','$89','$129','NEW'],
      ['🧥','#fff0f0','Oversized Blazer','H&M Premium','$145','$195','SALE'],
      ['👟','#f0f5ff','Air Classic Sneakers','Nike','$120','','HOT'],
      ['👜','#fff5f0','Mini Leather Tote','Coach','$280','$350','SALE'],
    ] as [$e,$bg,$n,$brand,$new,$old,$tag])
    <div class="prod-card">
      <div class="prod-img" style="background:{{ $bg }}">
        {{ $e }}
        <div class="prod-tag">{{ $tag }}</div>
        <div class="prod-wish">♡</div>
      </div>
      <div class="prod-name">{{ $n }}</div>
      <div class="prod-brand">{{ $brand }}</div>
      <div class="prod-price">
        <span class="new">{{ $new }}</span>
        @if($old)<span class="old">{{ $old }}</span>@endif
      </div>
      <button class="prod-add">Add to Cart</button>
    </div>
    @endforeach
  </div>
</section>

<!-- PROMO BANNER -->
<div class="promo-banner">
  <div>
    <div class="promo-tag">LIMITED TIME</div>
    <div class="promo-h2">Summer Sale<br>Up to 50% Off</div>
    <p class="promo-sub">Don't miss out on our biggest sale of the year. Thousands of styles reduced across all categories.</p>
    <a href="#" class="promo-btn">Shop the Sale →</a>
  </div>
  <div class="promo-visual">🎉</div>
</div>

<!-- NEWSLETTER -->
<section class="newsletter">
  <div class="section-label">Stay in the loop</div>
  <h2 class="section-h2">Get 10% Off Your First Order</h2>
  <p>Subscribe to our newsletter for exclusive deals, new arrivals, and style inspiration delivered straight to your inbox.</p>
  <div class="nl-form">
    <input class="nl-input" type="email" placeholder="your@email.com">
    <button class="nl-btn">Subscribe</button>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div>
    <span class="f-logo">Style<span>Hub</span></span>
    <p class="f-about">Curating fashion-forward styles for the modern individual since 2018. Sustainable, stylish, affordable.</p>
  </div>
  <div>
    <div class="f-head">Shop</div>
    <a class="f-link" href="#">Women</a><a class="f-link" href="#">Men</a>
    <a class="f-link" href="#">Kids</a><a class="f-link" href="#">Sale</a><a class="f-link" href="#">New Arrivals</a>
  </div>
  <div>
    <div class="f-head">Support</div>
    <a class="f-link" href="#">Size Guide</a><a class="f-link" href="#">Shipping Info</a>
    <a class="f-link" href="#">Returns</a><a class="f-link" href="#">Track Order</a><a class="f-link" href="#">Contact Us</a>
  </div>
  <div>
    <div class="f-head">Company</div>
    <a class="f-link" href="#">About Us</a><a class="f-link" href="#">Careers</a>
    <a class="f-link" href="#">Press</a><a class="f-link" href="#">Sustainability</a><a class="f-link" href="#">Affiliates</a>
  </div>
  <div class="f-bottom" style="grid-column:1/-1">
    <span>© 2024 StyleHub. All rights reserved.</span>
    <span class="f-badge">Built with RyaanCMS</span>
  </div>
</footer>

</body>
</html>
