<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $project->name ?? 'Site' }} — Coming Soon</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px}
.card{max-width:480px}
.icon{font-size:64px;margin-bottom:24px}
h1{font-size:32px;font-weight:800;letter-spacing:-0.5px;margin-bottom:10px}
p{color:rgba(255,255,255,.5);font-size:15px;line-height:1.7;margin-bottom:28px}
.badge{display:inline-block;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.35);font-size:12px;padding:8px 20px;border-radius:100px}
</style>
</head>
<body>
<div class="card">
  <div class="icon">🚧</div>
  <h1>{{ $project->name ?? 'This Site' }}</h1>
  <p>No active template is set for this project. Install and activate a template from the marketplace to go live.</p>
  <div class="badge">Built with RyaanCMS · Marketplace → Templates</div>
</div>
</body>
</html>
