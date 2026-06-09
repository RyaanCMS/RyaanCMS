# build-release.ps1 - RyaanCMS cPanel Deployment Builder + Auto GitHub Release
# Usage: .\build-release.ps1
# First time: add GITHUB_TOKEN=your_token to .env

param(
    [string]$AppFolderName = "ryaancms"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = $PSScriptRoot
Set-Location $ScriptRoot

Write-Host ""
Write-Host "  RyaanCMS - Build & Release" -ForegroundColor Cyan
Write-Host "  ===========================" -ForegroundColor Cyan
Write-Host ""

# ── Read .env ────────────────────────────────────────────────
function Get-EnvValue($key) {
    if (Test-Path ".env") {
        $line = Get-Content ".env" | Where-Object { $_ -match "^$key=" } | Select-Object -First 1
        if ($line) { return ($line -replace "^$key=","").Trim() }
    }
    return $null
}

$Version      = Get-EnvValue "APP_VERSION"
if (-not $Version) { $Version = "1.0.0" }

$GithubToken  = $env:GITHUB_TOKEN
if (-not $GithubToken) { $GithubToken = Get-EnvValue "GITHUB_TOKEN" }

# ── Detect GitHub repo from git remote ───────────────────────
$remoteUrl = git remote get-url origin 2>$null
$RepoPath  = $null
if ($remoteUrl -match "github\.com[:/](.+?)(?:\.git)?$") {
    $RepoPath = $Matches[1].Trim()   # e.g. RyaanCMS/RyaanCMS
}
if (-not $RepoPath) {
    Write-Host "  [ERROR] Could not detect GitHub repo from git remote." -ForegroundColor Red
    exit 1
}

Write-Host "  Version  : v$Version"
Write-Host "  Repo     : $RepoPath"
Write-Host "  App Dir  : ~/$AppFolderName/"
Write-Host ""

# ── Require GitHub Token ─────────────────────────────────────
if (-not $GithubToken) {
    Write-Host "  [ERROR] GitHub token not found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "  Add this line to your .env file:" -ForegroundColor Yellow
    Write-Host "  GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx" -ForegroundColor White
    Write-Host ""
    Write-Host "  Get token: github.com/settings/tokens/new" -ForegroundColor DarkGray
    Write-Host "  Required scopes: repo (full control)" -ForegroundColor DarkGray
    Write-Host ""
    exit 1
}

# ── Find PHP ─────────────────────────────────────────────────
$phpExe = $null
$phpPaths = @(
    "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe",
    "C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe",
    "C:\php\php.exe",
    "C:\xampp\php\php.exe"
)
foreach ($p in $phpPaths) {
    if (Test-Path $p) { $phpExe = $p; break }
}
if (-not $phpExe) {
    $laragonPhp = Get-ChildItem "C:\laragon\bin\php" -ErrorAction SilentlyContinue |
                  Sort-Object LastWriteTime -Descending | Select-Object -First 1
    if ($laragonPhp) { $phpExe = "$($laragonPhp.FullName)\php.exe" }
}
if (-not $phpExe) {
    $found = Get-Command php -ErrorAction SilentlyContinue
    if ($found) { $phpExe = $found.Source }
}
if (-not $phpExe) { Write-Host "  [ERROR] PHP not found!" -ForegroundColor Red; exit 1 }

# ── Find Composer ─────────────────────────────────────────────
$composerPhar = $null
$composerPaths = @(
    "C:\laragon\bin\composer\composer.phar",
    "C:\ProgramData\ComposerSetup\bin\composer.phar",
    "$env:APPDATA\Composer\composer.phar"
)
foreach ($p in $composerPaths) {
    if (Test-Path $p) { $composerPhar = $p; break }
}

Write-Host "  PHP      : $phpExe" -ForegroundColor DarkGray
Write-Host "  Composer : $composerPhar" -ForegroundColor DarkGray
Write-Host ""

# ── Paths ─────────────────────────────────────────────────────
$distDir = "$ScriptRoot\dist"
$appDir  = "$distDir\$AppFolderName"
$pubDir  = "$distDir\public_html"
$zipName = "ryaancms-v$Version-cpanel.zip"
$zipPath = "$ScriptRoot\$zipName"

# ── STEP 1: Clean ─────────────────────────────────────────────
Write-Host "  [1/7] Cleaning dist folder..." -ForegroundColor Yellow
if (Test-Path $distDir) { Remove-Item $distDir -Recurse -Force }
New-Item -ItemType Directory -Path $appDir -Force | Out-Null
New-Item -ItemType Directory -Path $pubDir -Force | Out-Null

# ── STEP 2: Composer install ──────────────────────────────────
Write-Host "  [2/7] Running composer install (production)..." -ForegroundColor Yellow
if ($composerPhar) {
    & $phpExe $composerPhar install --no-dev --optimize-autoloader --no-interaction --no-progress
} else {
    $ce = Get-Command composer -ErrorAction SilentlyContinue
    if ($ce) { & $ce.Source install --no-dev --optimize-autoloader --no-interaction --no-progress }
    else { Write-Host "  [ERROR] Composer not found!" -ForegroundColor Red; exit 1 }
}
if ($LASTEXITCODE -ne 0) { Write-Host "  [ERROR] Composer install failed!" -ForegroundColor Red; exit 1 }

# ── STEP 3: Copy app files ────────────────────────────────────
Write-Host "  [3/7] Copying application files..." -ForegroundColor Yellow
$skipNames = @(".git","node_modules","dist","build",".github","public",".env",
               "push.ps1","setup.ps1","build-release.ps1",".ryaan-config")

Get-ChildItem -Path $ScriptRoot | Where-Object {
    $skipNames -notcontains $_.Name -and $_.Name -notlike "ryaancms-v*-cpanel.zip"
} | ForEach-Object {
    $dest = if ($_.PSIsContainer) { "$appDir\$($_.Name)" } else { "$appDir\$($_.Name)" }
    Copy-Item $_.FullName -Destination $dest -Recurse -Force
}

# ── STEP 4: Clean storage runtime files ──────────────────────
Write-Host "  [4/7] Cleaning storage runtime files..." -ForegroundColor Yellow
@("$appDir\storage\logs",
  "$appDir\storage\framework\cache\data",
  "$appDir\storage\framework\sessions",
  "$appDir\storage\framework\views",
  "$appDir\bootstrap\cache") | ForEach-Object {
    if (-not (Test-Path $_)) { New-Item -ItemType Directory -Path $_ -Force | Out-Null }
    Get-ChildItem $_ -File -ErrorAction SilentlyContinue | Remove-Item -Force
    New-Item -Path "$_\.gitkeep" -ItemType File -Force | Out-Null
}

# ── STEP 5: Copy & fix public/ ────────────────────────────────
Write-Host "  [5/7] Copying public files and fixing index.php paths..." -ForegroundColor Yellow
Copy-Item "$ScriptRoot\public\*" -Destination $pubDir -Recurse -Force

$indexPath = "$pubDir\index.php"
$content = Get-Content $indexPath -Raw
$content = $content `
    -replace [regex]::Escape("__DIR__ . '/../storage/app/.installed'"),       "__DIR__ . '/../$AppFolderName/storage/app/.installed'" `
    -replace [regex]::Escape("__DIR__ . '/../storage/framework/maintenance.php'"), "__DIR__ . '/../$AppFolderName/storage/framework/maintenance.php'" `
    -replace [regex]::Escape("__DIR__.'/../storage/framework/maintenance.php'"),    "__DIR__.'/../$AppFolderName/storage/framework/maintenance.php'" `
    -replace [regex]::Escape("__DIR__.'/../vendor/autoload.php'"),             "__DIR__.'/../$AppFolderName/vendor/autoload.php'" `
    -replace [regex]::Escape("__DIR__.'/../bootstrap/app.php'"),               "__DIR__.'/../$AppFolderName/bootstrap/app.php'"
[System.IO.File]::WriteAllText($indexPath, $content, [System.Text.Encoding]::UTF8)

if (Test-Path "$pubDir\install.php") {
    $ic = Get-Content "$pubDir\install.php" -Raw
    $ic = $ic -replace [regex]::Escape("__DIR__ . '/../"), "__DIR__ . '/../$AppFolderName/"
    $ic = $ic -replace [regex]::Escape("__DIR__.'/../"),   "__DIR__.'/../$AppFolderName/"
    [System.IO.File]::WriteAllText("$pubDir\install.php", $ic, [System.Text.Encoding]::UTF8)
}

# ── STEP 6: Create ZIP ────────────────────────────────────────
Write-Host "  [6/7] Creating deployment ZIP..." -ForegroundColor Yellow
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($distDir, $zipPath, [System.IO.Compression.CompressionLevel]::Optimal, $false)
Remove-Item $distDir -Recurse -Force
$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host "     ZIP: $zipName ($sizeMB MB)" -ForegroundColor DarkGray

# ── STEP 7: GitHub Release + Upload ──────────────────────────
Write-Host "  [7/7] Creating GitHub Release v$Version..." -ForegroundColor Yellow

$apiBase = "https://api.github.com"
$headers = @{
    "Authorization"        = "Bearer $GithubToken"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

# Check if release already exists
$existingRelease = $null
try {
    $existingRelease = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/tags/v$Version" `
                       -Headers $headers -Method Get -ErrorAction Stop
    Write-Host "     Release v$Version already exists (id: $($existingRelease.id))" -ForegroundColor DarkGray
} catch { }

# Create release if it doesn't exist
$release = $existingRelease
if (-not $release) {
    # Push git tag first
    git tag "v$Version" 2>$null
    git push origin "v$Version" 2>$null

    $releaseBody = @{
        tag_name         = "v$Version"
        target_commitish = "main"
        name             = "RyaanCMS v$Version"
        draft            = $false
        prerelease       = $false
        body             = @"
## RyaanCMS v$Version

### cPanel Installation
1. Download **$zipName** below
2. Upload to ``/home/username/`` via cPanel File Manager
3. Extract it there
4. Move ``public_html/`` contents INTO your public_html/
5. Move ``ryaancms/`` folder next to public_html/
6. Set permissions: ``chmod -R 775 ryaancms/storage ryaancms/bootstrap/cache``
7. Visit **yourdomain.com** - install wizard starts automatically!

### Requirements
- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- cPanel shared hosting
"@
    } | ConvertTo-Json -Depth 3

    $release = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases" `
               -Method Post -Headers $headers -Body $releaseBody -ContentType "application/json"
    Write-Host "     Release created (id: $($release.id))" -ForegroundColor DarkGray
}

# Delete existing asset with same name if present
try {
    $existingAssets = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/$($release.id)/assets" `
                      -Headers $headers -Method Get
    $old = $existingAssets | Where-Object { $_.name -eq $zipName }
    if ($old) {
        Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/assets/$($old.id)" `
                          -Headers $headers -Method Delete | Out-Null
        Write-Host "     Replaced existing asset" -ForegroundColor DarkGray
    }
} catch { }

# Upload ZIP asset
Write-Host "     Uploading $zipName..." -ForegroundColor Yellow
$uploadUrl  = "https://uploads.github.com/repos/$RepoPath/releases/$($release.id)/assets?name=$zipName"
$zipBytes   = [System.IO.File]::ReadAllBytes($zipPath)

$uploadHeaders = @{
    "Authorization"        = "Bearer $GithubToken"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
    "Content-Type"         = "application/octet-stream"
}

$asset = Invoke-RestMethod -Uri $uploadUrl -Method Post -Headers $uploadHeaders -Body $zipBytes
Write-Host "     Uploaded: $($asset.browser_download_url)" -ForegroundColor DarkGray

# Update versions.json with correct download URL
$versionsPath = "$ScriptRoot\versions.json"
$versionsJson = Get-Content $versionsPath -Raw
$versionsJson = $versionsJson -replace [regex]::Escape("`"download_url`": `"https://github.com/$RepoPath/releases/download/v$Version/ryaancms-v"), "`"download_url`": `"https://github.com/$RepoPath/releases/download/v$Version/ryaancms-v"
# Ensure download_url is correct
$newUrl = $asset.browser_download_url
$versionsJson = [regex]::Replace($versionsJson,
    '"download_url":\s*"[^"]*"',
    "`"download_url`": `"$newUrl`"")
[System.IO.File]::WriteAllText($versionsPath, $versionsJson, [System.Text.Encoding]::UTF8)

# Git commit versions.json
git add versions.json 2>$null
$commitResult = git commit -m "chore: update versions.json download_url for v$Version" 2>&1
if ($LASTEXITCODE -eq 0) {
    git push origin main 2>$null
}

# ── Done ──────────────────────────────────────────────────────
$releaseUrl = "https://github.com/$RepoPath/releases/tag/v$Version"

Write-Host ""
Write-Host "  ==========================================" -ForegroundColor Green
Write-Host "  Released!  v$Version  ($sizeMB MB)" -ForegroundColor Green
Write-Host "  ==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  GitHub Release : $releaseUrl" -ForegroundColor Cyan
Write-Host "  Download URL   : $($asset.browser_download_url)" -ForegroundColor Cyan
Write-Host ""
Write-Host "  cPanel Upload Steps:" -ForegroundColor White
Write-Host "  1. Download $zipName from GitHub Releases"
Write-Host "  2. Upload to /home/username/ in cPanel File Manager"
Write-Host "  3. Extract -> move public_html/ contents + ryaancms/ folder"
Write-Host "  4. chmod -R 775 ryaancms/storage ryaancms/bootstrap/cache"
Write-Host "  5. Visit yourdomain.com -> install!"
Write-Host ""
Write-Host "  Local ZIP kept at: $zipPath" -ForegroundColor DarkGray
Write-Host ""
