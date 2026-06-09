# ═══════════════════════════════════════════════════════
#  RyaanCMS — Dual Repo Push Script
#  Usage: .\push.ps1 "your commit message"
#
#  Pushes to:
#    Private repo — full codebase (everything)
#    Public repo  — filtered (no central marketplace code)
#
#  Git history is NEVER shared between repos.
#  Private code can NEVER leak to public repo.
# ═══════════════════════════════════════════════════════

param([string]$msg = "")

$PRIVATE_DIR = $PSScriptRoot
$CONFIG_FILE = Join-Path $PRIVATE_DIR ".ryaan-config"

# ── Require a commit message ──────────────────────────
if (-not $msg) {
    Write-Host ""
    Write-Host "  Usage: .\push.ps1 `"your commit message`"" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

# ── Read config ───────────────────────────────────────
if (-not (Test-Path $CONFIG_FILE)) {
    Write-Host ""
    Write-Host "  ERROR: .ryaan-config not found." -ForegroundColor Red
    Write-Host "  Run .\setup.ps1 first to configure this machine." -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

$config = @{}
Get-Content $CONFIG_FILE | Where-Object { $_ -match '=' } | ForEach-Object {
    $parts = $_ -split '=', 2
    $config[$parts[0].Trim()] = $parts[1].Trim()
}

$PUBLIC_DIR = $config['PUBLIC_DIR']

# ── Safety checks ─────────────────────────────────────
if (-not $PUBLIC_DIR) {
    Write-Host ""
    Write-Host "  ERROR: PUBLIC_DIR not set in .ryaan-config" -ForegroundColor Red
    Write-Host "  Run .\setup.ps1 to fix this." -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

if (-not (Test-Path $PUBLIC_DIR)) {
    Write-Host ""
    Write-Host "  ERROR: Public folder not found: $PUBLIC_DIR" -ForegroundColor Red
    Write-Host "  Run .\setup.ps1 to create it." -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

$publicGitDir = Join-Path $PUBLIC_DIR ".git"
if (-not (Test-Path $publicGitDir)) {
    Write-Host ""
    Write-Host "  ERROR: Public folder is not a git repo: $PUBLIC_DIR" -ForegroundColor Red
    Write-Host "  Run .\setup.ps1 to initialise it." -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

# ── Files/folders that NEVER go to public repo ────────
# Add new private paths here as your central marketplace grows
$PRIVATE_ONLY = @(
    "app\Http\Controllers\Central",
    "app\Services\Commission",
    "app\Services\Payout",
    "app\Services\License\LicenseAuthority.php",
    "routes\marketplace-host.php",
    "database\migrations\central",
    "resources\views\central",
    "config\commission.php",
    "push.ps1",
    "setup.ps1",
    ".ryaan-config"
)

# Folders robocopy should never copy (vendor, build artefacts, etc.)
$SKIP_DIRS = @('.git', 'vendor', 'node_modules', 'storage\app', 'storage\logs')

# ══════════════════════════════════════════════════════
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "  RyaanCMS Dual Push" -ForegroundColor Cyan
Write-Host "  $msg" -ForegroundColor White
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host ""

# ── STEP 1: Private repo — commit + push everything ───
Write-Host "[1/3] Private repo..." -ForegroundColor Yellow
Set-Location $PRIVATE_DIR

git add -A
$commitResult = git commit -m $msg 2>&1
if ($LASTEXITCODE -ne 0 -and $commitResult -notmatch 'nothing to commit') {
    Write-Host "      Commit failed: $commitResult" -ForegroundColor Red
    exit 1
}

git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "      Push to private repo failed." -ForegroundColor Red
    exit 1
}
Write-Host "      ✅ Private repo updated (full code)" -ForegroundColor Green

# ── STEP 2: Sync files to public folder ───────────────
Write-Host ""
Write-Host "[2/3] Syncing to public folder..." -ForegroundColor Yellow

# Build robocopy exclude list
$xcludePaths = $SKIP_DIRS | ForEach-Object { Join-Path $PRIVATE_DIR $_ }

# Mirror files (no git history transferred — just files)
robocopy $PRIVATE_DIR $PUBLIC_DIR /MIR /XD @xcludePaths /XF "*.log" /NFL /NDL /NJH /NJS | Out-Null

# Remove private-only paths from public folder
foreach ($path in $PRIVATE_ONLY) {
    $full = Join-Path $PUBLIC_DIR $path
    if (Test-Path $full) {
        Remove-Item $full -Recurse -Force
        Write-Host "      ✗ $path" -ForegroundColor DarkRed
    }
}
Write-Host "      ✅ Files synced (private paths stripped)" -ForegroundColor Green

# ── STEP 3: Public repo — commit + push filtered code ─
Write-Host ""
Write-Host "[3/3] Public repo..." -ForegroundColor Yellow
Set-Location $PUBLIC_DIR

git add -A
$pubCommit = git commit -m $msg 2>&1 --allow-empty
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "      Push to public repo failed." -ForegroundColor Red
    Set-Location $PRIVATE_DIR
    exit 1
}
Write-Host "      ✅ Public repo updated (filtered)" -ForegroundColor Green

# ── Done ──────────────────────────────────────────────
Set-Location $PRIVATE_DIR
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "  🎉 Both repos updated" -ForegroundColor Green
Write-Host "  Private: full code + history" -ForegroundColor DarkGray
Write-Host "  Public:  filtered — zero private code" -ForegroundColor DarkGray
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host ""
