# build-release.ps1 - RyaanCMS cPanel Deployment Builder + Auto GitHub Release
# Usage:
#   .\build-release.ps1                      -> auto patch bump (1.0.0 -> 1.0.1)
#   .\build-release.ps1 -Version 1.1.0       -> specific version
#   .\build-release.ps1 -Version 1.1.0 -Changelog "Bug fixes and improvements"

param(
    [string]$Version      = "",
    [string]$Changelog    = "",
    [string]$AppFolderName = "ryaancms"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = $PSScriptRoot
Set-Location $ScriptRoot

Write-Host ""
Write-Host "  RyaanCMS - Build & Release" -ForegroundColor Cyan
Write-Host "  ===========================" -ForegroundColor Cyan
Write-Host ""

# ── Helpers ───────────────────────────────────────────────────
function Get-EnvValue($key) {
    if (Test-Path ".env") {
        $line = Get-Content ".env" | Where-Object { $_ -match "^$key=" } | Select-Object -First 1
        if ($line) { return ($line -replace "^$key=","").Trim() }
    }
    return $null
}

function Set-EnvValue($key, $value) {
    $envPath = ".env"
    $content = Get-Content $envPath -Raw
    if ($content -match "(?m)^$key=") {
        $content = [regex]::Replace($content, "(?m)^$key=.*", "$key=$value")
    } else {
        $content = $content.TrimEnd() + "`n$key=$value`n"
    }
    [System.IO.File]::WriteAllText((Resolve-Path $envPath).Path, $content, [System.Text.Encoding]::UTF8)
}

function Bump-Patch($ver) {
    $parts = $ver -split "\."
    if ($parts.Count -eq 3) {
        $parts[2] = [int]$parts[2] + 1
        return $parts -join "."
    }
    return $ver
}

function Get-GitChangelog($fromTag) {
    $prev = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    if ($fromTag) {
        $logs = git log "v$fromTag..HEAD" --oneline 2>&1 | Where-Object { $_ -notmatch "^fatal" }
    } else {
        $logs = git log --oneline -20 2>&1 | Where-Object { $_ -notmatch "^fatal" }
    }
    $ErrorActionPreference = $prev
    if (-not $logs) { return "Bug fixes and improvements." }
    $items = $logs | ForEach-Object { "- " + ($_ -replace "^[a-f0-9]+ ","") }
    return $items -join "`n"
}

# ── Read current version ──────────────────────────────────────
$CurrentVersion = Get-EnvValue "APP_VERSION"
if (-not $CurrentVersion) { $CurrentVersion = "1.0.0" }

$GithubToken = $env:GITHUB_TOKEN
if (-not $GithubToken) { $GithubToken = Get-EnvValue "GITHUB_TOKEN" }

# ── Detect repo ───────────────────────────────────────────────
$remoteUrl = git remote get-url origin 2>$null
$RepoPath  = $null
if ($remoteUrl -match "github\.com[:/](.+?)(?:\.git)?$") {
    $RepoPath = $Matches[1].Trim()
}
if (-not $RepoPath) {
    Write-Host "  [ERROR] Could not detect GitHub repo from git remote." -ForegroundColor Red
    exit 1
}

# ── Determine new version ─────────────────────────────────────
if (-not $Version) {
    $suggested = Bump-Patch $CurrentVersion
    Write-Host "  Current version : v$CurrentVersion" -ForegroundColor DarkGray
    Write-Host "  Suggested next  : v$suggested" -ForegroundColor DarkGray
    Write-Host ""
    $input = Read-Host "  Enter new version (press Enter for v$suggested)"
    $Version = if ($input.Trim()) { $input.Trim() } else { $suggested }
}

# Strip leading 'v' if user typed it
$Version = $Version -replace "^v", ""

if ($Version -eq $CurrentVersion) {
    Write-Host "  [ERROR] New version ($Version) is same as current ($CurrentVersion)." -ForegroundColor Red
    Write-Host "  Use: .\build-release.ps1 -Version 1.1.0" -ForegroundColor Yellow
    exit 1
}

# ── Determine changelog ───────────────────────────────────────
if (-not $Changelog) {
    $autoLog = Get-GitChangelog $CurrentVersion
    Write-Host ""
    Write-Host "  Auto-generated changelog from git log:" -ForegroundColor DarkGray
    Write-Host $autoLog -ForegroundColor DarkGray
    Write-Host ""
    $input = Read-Host "  Press Enter to use this changelog, or type a custom one"
    $Changelog = if ($input.Trim()) { $input.Trim() } else { $autoLog }
}

Write-Host ""
Write-Host "  Version  : v$CurrentVersion  ->  v$Version" -ForegroundColor White
Write-Host "  Repo     : $RepoPath" -ForegroundColor White
Write-Host "  App Dir  : ~/$AppFolderName/" -ForegroundColor White
Write-Host ""

# ── Require GitHub Token ──────────────────────────────────────
if (-not $GithubToken) {
    Write-Host "  [ERROR] GITHUB_TOKEN not found in .env!" -ForegroundColor Red
    Write-Host "  Add:  GITHUB_TOKEN=ghp_xxxx  to your .env file" -ForegroundColor Yellow
    exit 1
}

# ── Find PHP ──────────────────────────────────────────────────
$phpExe = $null
foreach ($p in @(
    "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe",
    "C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe",
    "C:\php\php.exe","C:\xampp\php\php.exe")) {
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
foreach ($p in @(
    "C:\laragon\bin\composer\composer.phar",
    "C:\ProgramData\ComposerSetup\bin\composer.phar",
    "$env:APPDATA\Composer\composer.phar")) {
    if (Test-Path $p) { $composerPhar = $p; break }
}

Write-Host "  PHP      : $phpExe" -ForegroundColor DarkGray
Write-Host "  Composer : $composerPhar" -ForegroundColor DarkGray
Write-Host ""

# ─────────────────────────────────────────────────────────────
# STEP 1: Update .env APP_VERSION
# ─────────────────────────────────────────────────────────────
Write-Host "  [1/8] Updating .env APP_VERSION to $Version..." -ForegroundColor Yellow
Set-EnvValue "APP_VERSION" $Version

# ─────────────────────────────────────────────────────────────
# STEP 2: Update versions.json
# ─────────────────────────────────────────────────────────────
Write-Host "  [2/8] Updating versions.json..." -ForegroundColor Yellow
$versionsPath = "$ScriptRoot\versions.json"
$versionsData = Get-Content $versionsPath -Raw | ConvertFrom-Json

# Check if this version already exists
$exists = $versionsData.versions | Where-Object { $_.version -eq $Version }
if (-not $exists) {
    $newEntry = [PSCustomObject]@{
        version           = $Version
        type              = "feature"
        released_at       = (Get-Date -Format "yyyy-MM-dd")
        changelog         = $Changelog
        download_url      = "https://github.com/$RepoPath/releases/download/v$Version/ryaancms-v$Version-cpanel.zip"
        requires_migration = $true
        min_php           = "8.2"
        min_from_version  = $CurrentVersion
        critical          = $false
        file_count        = $null
    }
    # Add to versions array
    $versionsData.versions = @($versionsData.versions) + $newEntry
}

# Update latest
$versionsData.latest = $Version

# Save back (pretty print)
$versionsJson = $versionsData | ConvertTo-Json -Depth 10
[System.IO.File]::WriteAllText($versionsPath, $versionsJson, [System.Text.Encoding]::UTF8)

# ─────────────────────────────────────────────────────────────
# STEP 3: Git commit version bump
# ─────────────────────────────────────────────────────────────
Write-Host "  [3/8] Committing version bump to git..." -ForegroundColor Yellow
$prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"
git add versions.json 2>&1 | Out-Null
git commit -m "chore: bump version to v$Version" 2>&1 | Out-Null
git push origin main 2>&1 | Out-Null
$ErrorActionPreference = $prev

# ─────────────────────────────────────────────────────────────
# STEP 4: Clean dist
# ─────────────────────────────────────────────────────────────
$distDir = "$ScriptRoot\dist"
$appDir  = "$distDir\$AppFolderName"
$pubDir  = "$distDir\public_html"
$zipName = "ryaancms-v$Version-cpanel.zip"
$zipPath = "$ScriptRoot\$zipName"

Write-Host "  [4/8] Cleaning dist folder..." -ForegroundColor Yellow
if (Test-Path $distDir) { Remove-Item $distDir -Recurse -Force }
New-Item -ItemType Directory -Path $appDir -Force | Out-Null
New-Item -ItemType Directory -Path $pubDir -Force | Out-Null

# ─────────────────────────────────────────────────────────────
# STEP 5: Composer install
# ─────────────────────────────────────────────────────────────
Write-Host "  [5/8] Running composer install (production)..." -ForegroundColor Yellow
if ($composerPhar) {
    & $phpExe $composerPhar install --no-dev --optimize-autoloader --no-interaction --no-progress
} else {
    $ce = Get-Command composer -ErrorAction SilentlyContinue
    if ($ce) { & $ce.Source install --no-dev --optimize-autoloader --no-interaction --no-progress }
    else { Write-Host "  [ERROR] Composer not found!" -ForegroundColor Red; exit 1 }
}
if ($LASTEXITCODE -ne 0) { Write-Host "  [ERROR] Composer install failed!" -ForegroundColor Red; exit 1 }

# ─────────────────────────────────────────────────────────────
# STEP 6: Copy files + fix public/index.php
# ─────────────────────────────────────────────────────────────
Write-Host "  [6/8] Copying application files..." -ForegroundColor Yellow
$skipNames = @(".git","node_modules","dist","build",".github","public",".env",
               "push.ps1","setup.ps1","build-release.ps1",".ryaan-config")

Get-ChildItem -Path $ScriptRoot | Where-Object {
    $skipNames -notcontains $_.Name -and $_.Name -notlike "ryaancms-v*-cpanel.zip"
} | ForEach-Object {
    Copy-Item $_.FullName -Destination "$appDir\$($_.Name)" -Recurse -Force
}

# Clean storage runtime
@("$appDir\storage\logs","$appDir\storage\framework\cache\data",
  "$appDir\storage\framework\sessions","$appDir\storage\framework\views",
  "$appDir\bootstrap\cache") | ForEach-Object {
    if (-not (Test-Path $_)) { New-Item -ItemType Directory -Path $_ -Force | Out-Null }
    Get-ChildItem $_ -File -ErrorAction SilentlyContinue | Remove-Item -Force
    New-Item -Path "$_\.gitkeep" -ItemType File -Force | Out-Null
}

# Copy public + fix paths
Copy-Item "$ScriptRoot\public\*" -Destination $pubDir -Recurse -Force
$indexPath = "$pubDir\index.php"
$content = Get-Content $indexPath -Raw
$content = $content `
    -replace [regex]::Escape("__DIR__ . '/../storage/app/.installed'"),           "__DIR__ . '/../$AppFolderName/storage/app/.installed'" `
    -replace [regex]::Escape("__DIR__ . '/../storage/framework/maintenance.php'"), "__DIR__ . '/../$AppFolderName/storage/framework/maintenance.php'" `
    -replace [regex]::Escape("__DIR__.'/../storage/framework/maintenance.php'"),    "__DIR__.'/../$AppFolderName/storage/framework/maintenance.php'" `
    -replace [regex]::Escape("__DIR__.'/../vendor/autoload.php'"),                 "__DIR__.'/../$AppFolderName/vendor/autoload.php'" `
    -replace [regex]::Escape("__DIR__.'/../bootstrap/app.php'"),                   "__DIR__.'/../$AppFolderName/bootstrap/app.php'"
[System.IO.File]::WriteAllText($indexPath, $content, [System.Text.Encoding]::UTF8)

if (Test-Path "$pubDir\install.php") {
    $ic = Get-Content "$pubDir\install.php" -Raw
    $ic = $ic -replace [regex]::Escape("__DIR__ . '/../"), "__DIR__ . '/../$AppFolderName/"
    $ic = $ic -replace [regex]::Escape("__DIR__.'/../"),   "__DIR__.'/../$AppFolderName/"
    [System.IO.File]::WriteAllText("$pubDir\install.php", $ic, [System.Text.Encoding]::UTF8)
}

# ─────────────────────────────────────────────────────────────
# STEP 7: Create ZIP
# ─────────────────────────────────────────────────────────────
Write-Host "  [7/8] Creating deployment ZIP..." -ForegroundColor Yellow
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($distDir, $zipPath, [System.IO.Compression.CompressionLevel]::Optimal, $false)
Remove-Item $distDir -Recurse -Force
$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host "     ZIP: $zipName ($sizeMB MB)" -ForegroundColor DarkGray

# ─────────────────────────────────────────────────────────────
# STEP 8: GitHub Release + Upload
# ─────────────────────────────────────────────────────────────
Write-Host "  [8/8] Creating GitHub Release v$Version..." -ForegroundColor Yellow

$apiBase = "https://api.github.com"
$headers = @{
    "Authorization"        = "Bearer $GithubToken"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

# Check if release already exists
$release = $null
try {
    $release = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/tags/v$Version" `
               -Headers $headers -Method Get -ErrorAction Stop
    Write-Host "     Release v$Version already exists (id: $($release.id))" -ForegroundColor DarkGray
} catch { }

if (-not $release) {
    # Push git tag
    $prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"
    git tag "v$Version" 2>&1 | Out-Null
    git push origin "v$Version" 2>&1 | Out-Null
    $ErrorActionPreference = $prev

    $releaseBody = @{
        tag_name         = "v$Version"
        target_commitish = "main"
        name             = "RyaanCMS v$Version"
        draft            = $false
        prerelease       = $false
        body             = "## RyaanCMS v$Version`n`n$Changelog`n`n### cPanel Installation`n1. Download **$zipName** below`n2. Upload to ``/home/username/`` via File Manager`n3. Extract, move ``public_html/`` contents + ``ryaancms/`` folder`n4. ``chmod -R 775 ryaancms/storage ryaancms/bootstrap/cache```n5. Visit yourdomain.com - install wizard starts!"
    } | ConvertTo-Json -Depth 3

    $release = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases" `
               -Method Post -Headers $headers -Body $releaseBody -ContentType "application/json"
    Write-Host "     Release created (id: $($release.id))" -ForegroundColor DarkGray
}

# Remove existing asset with same name
try {
    $assets = Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/$($release.id)/assets" -Headers $headers
    $old = $assets | Where-Object { $_.name -eq $zipName }
    if ($old) {
        Invoke-RestMethod -Uri "$apiBase/repos/$RepoPath/releases/assets/$($old.id)" -Headers $headers -Method Delete | Out-Null
    }
} catch { }

# Upload ZIP
Write-Host "     Uploading $zipName..." -ForegroundColor Yellow
$uploadUrl  = "https://uploads.github.com/repos/$RepoPath/releases/$($release.id)/assets?name=$zipName"
$zipBytes   = [System.IO.File]::ReadAllBytes($zipPath)
$upHeaders  = @{
    "Authorization"        = "Bearer $GithubToken"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
    "Content-Type"         = "application/octet-stream"
}
$asset = Invoke-RestMethod -Uri $uploadUrl -Method Post -Headers $upHeaders -Body $zipBytes
Write-Host "     Uploaded!" -ForegroundColor DarkGray

# Update versions.json download_url with actual asset URL and push
$vJson = Get-Content $versionsPath -Raw
$vJson = [regex]::Replace($vJson,
    "(?<=\""download_url\""\s*:\s*\"")[^""]*(?=\"")",
    $asset.browser_download_url)
[System.IO.File]::WriteAllText($versionsPath, $vJson, [System.Text.Encoding]::UTF8)

$prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"
git add versions.json 2>&1 | Out-Null
git commit -m "chore: versions.json final download_url for v$Version" 2>&1 | Out-Null
git push origin main 2>&1 | Out-Null
$ErrorActionPreference = $prev

# ── Done ──────────────────────────────────────────────────────
Write-Host ""
Write-Host "  ==========================================" -ForegroundColor Green
Write-Host "  Released!  v$Version  ($sizeMB MB)" -ForegroundColor Green
Write-Host "  ==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  GitHub : https://github.com/$RepoPath/releases/tag/v$Version" -ForegroundColor Cyan
Write-Host "  ZIP    : $($asset.browser_download_url)" -ForegroundColor Cyan
Write-Host ""
