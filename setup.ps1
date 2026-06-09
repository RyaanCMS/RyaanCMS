# ═══════════════════════════════════════════════════════
#  RyaanCMS — One-time Setup Script
#  Run this on any new PC after cloning the private repo:
#    git clone https://github.com/YOUR/RyaanCMS-private.git
#    .\setup.ps1
# ═══════════════════════════════════════════════════════

$PRIVATE_DIR = $PSScriptRoot
$CONFIG_FILE = Join-Path $PRIVATE_DIR ".ryaan-config"

Write-Host ""
Write-Host "  ██████╗ ██╗   ██╗ █████╗  █████╗ ███╗  ██╗" -ForegroundColor Cyan
Write-Host "  ██╔══██╗╚██╗ ██╔╝██╔══██╗██╔══██╗████╗ ██║" -ForegroundColor Cyan
Write-Host "  ██████╔╝ ╚████╔╝ ███████║███████║██╔██╗██║" -ForegroundColor Cyan
Write-Host "  ██╔══██╗  ╚██╔╝  ██╔══██║██╔══██║██║╚████║" -ForegroundColor Cyan
Write-Host "  ██║  ██║   ██║   ██║  ██║██║  ██║██║ ╚███║" -ForegroundColor Cyan
Write-Host "  ╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚══╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Two-Repo Setup Script" -ForegroundColor White
Write-Host "  Private repo: full codebase" -ForegroundColor DarkGray
Write-Host "  Public repo:  client code only (auto-filtered)" -ForegroundColor DarkGray
Write-Host ""

# ── Read config ───────────────────────────────────────────────
if (-not (Test-Path $CONFIG_FILE)) {
    Write-Host "ERROR: .ryaan-config not found in $PRIVATE_DIR" -ForegroundColor Red
    Write-Host "This file should be in the private repo." -ForegroundColor Red
    exit 1
}

$config = @{}
Get-Content $CONFIG_FILE | Where-Object { $_ -match '=' } | ForEach-Object {
    $parts = $_ -split '=', 2
    $config[$parts[0].Trim()] = $parts[1].Trim()
}

$PUBLIC_REPO_URL = $config['PUBLIC_REPO_URL']
$PUBLIC_DIR      = $config['PUBLIC_DIR']

if (-not $PUBLIC_REPO_URL -or -not $PUBLIC_DIR) {
    Write-Host "ERROR: .ryaan-config is missing PUBLIC_REPO_URL or PUBLIC_DIR" -ForegroundColor Red
    exit 1
}

Write-Host "  Private dir : $PRIVATE_DIR" -ForegroundColor DarkGray
Write-Host "  Public dir  : $PUBLIC_DIR" -ForegroundColor DarkGray
Write-Host "  Public repo : $PUBLIC_REPO_URL" -ForegroundColor DarkGray
Write-Host ""

# ── Step 1: Verify private remote ────────────────────────────
Write-Host "Step 1/4  Checking private remote..." -ForegroundColor Yellow
Set-Location $PRIVATE_DIR
$remotes = git remote -v 2>&1
if ($remotes -notmatch 'origin') {
    Write-Host "         No 'origin' remote found. Please set it manually:" -ForegroundColor Red
    Write-Host "         git remote add origin <private-repo-url>" -ForegroundColor Red
    exit 1
}
Write-Host "         OK — origin remote exists" -ForegroundColor Green

# ── Step 2: Create public folder ─────────────────────────────
Write-Host "Step 2/4  Setting up public folder..." -ForegroundColor Yellow
if (-not (Test-Path $PUBLIC_DIR)) {
    New-Item -ItemType Directory -Path $PUBLIC_DIR | Out-Null
    Write-Host "         Created: $PUBLIC_DIR" -ForegroundColor Green
} else {
    Write-Host "         Already exists: $PUBLIC_DIR" -ForegroundColor DarkGray
}

# ── Step 3: Init public repo + set remote ────────────────────
Write-Host "Step 3/4  Initialising public repo..." -ForegroundColor Yellow
Set-Location $PUBLIC_DIR

$isGitRepo = Test-Path (Join-Path $PUBLIC_DIR ".git")
if (-not $isGitRepo) {
    git init | Out-Null
    git remote add origin $PUBLIC_REPO_URL | Out-Null
    Write-Host "         Initialised + remote set" -ForegroundColor Green
} else {
    $existingRemote = git remote get-url origin 2>&1
    if ($existingRemote -ne $PUBLIC_REPO_URL) {
        git remote set-url origin $PUBLIC_REPO_URL | Out-Null
        Write-Host "         Remote updated to: $PUBLIC_REPO_URL" -ForegroundColor Green
    } else {
        Write-Host "         Already configured" -ForegroundColor DarkGray
    }
}

# ── Step 4: Done ──────────────────────────────────────────────
Set-Location $PRIVATE_DIR
Write-Host "Step 4/4  Verifying push.ps1 exists..." -ForegroundColor Yellow
if (Test-Path (Join-Path $PRIVATE_DIR "push.ps1")) {
    Write-Host "         Found push.ps1" -ForegroundColor Green
} else {
    Write-Host "         WARNING: push.ps1 not found!" -ForegroundColor Red
}

Write-Host ""
Write-Host "  ✅ Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "  Daily usage:" -ForegroundColor White
Write-Host "    .\push.ps1 `"your commit message`"" -ForegroundColor Cyan
Write-Host ""
Write-Host "  This will push to:" -ForegroundColor White
Write-Host "    Private repo — full code (safe)" -ForegroundColor DarkGray
Write-Host "    Public repo  — filtered (no central marketplace code)" -ForegroundColor DarkGray
Write-Host ""
