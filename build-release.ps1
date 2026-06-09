# build-release.ps1 - RyaanCMS cPanel Deployment Builder
# Run: .\build-release.ps1
# Output: ryaancms-v{version}-cpanel.zip

param(
    [string]$AppFolderName = "ryaancms"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = $PSScriptRoot
Set-Location $ScriptRoot

Write-Host ""
Write-Host "  RyaanCMS - cPanel Deploy Builder" -ForegroundColor Cyan
Write-Host "  ==================================" -ForegroundColor Cyan
Write-Host ""

# Read version from .env
$Version = "1.0.0"
if (Test-Path ".env") {
    $envLine = Get-Content ".env" | Where-Object { $_ -match "^APP_VERSION=" } | Select-Object -First 1
    if ($envLine) {
        $Version = ($envLine -replace "APP_VERSION=","").Trim()
    }
}
Write-Host "  Version  : v$Version"
Write-Host "  App Dir  : ~/$AppFolderName/"
Write-Host "  Public   : ~/public_html/"
Write-Host ""

# Find PHP
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
    $found = Get-Command php -ErrorAction SilentlyContinue
    if ($found) { $phpExe = $found.Source }
}
if (-not $phpExe) {
    # Search laragon php folder dynamically
    $laragonPhp = Get-ChildItem "C:\laragon\bin\php" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1
    if ($laragonPhp) { $phpExe = "$($laragonPhp.FullName)\php.exe" }
}
if (-not $phpExe) {
    Write-Host "  [ERROR] PHP not found!" -ForegroundColor Red
    exit 1
}
Write-Host "  PHP      : $phpExe" -ForegroundColor DarkGray

# Find Composer
$composerPhar = $null
$composerPaths = @(
    "C:\laragon\bin\composer\composer.phar",
    "C:\ProgramData\ComposerSetup\bin\composer.phar",
    "$env:APPDATA\Composer\composer.phar"
)
foreach ($p in $composerPaths) {
    if (Test-Path $p) { $composerPhar = $p; break }
}
Write-Host "  Composer : $composerPhar" -ForegroundColor DarkGray
Write-Host ""

# Paths
$distDir = "$ScriptRoot\dist"
$appDir  = "$distDir\$AppFolderName"
$pubDir  = "$distDir\public_html"
$zipPath = "$ScriptRoot\ryaancms-v$Version-cpanel.zip"

# Step 1: Clean
Write-Host "  [1/6] Cleaning dist folder..." -ForegroundColor Yellow
if (Test-Path $distDir) { Remove-Item $distDir -Recurse -Force }
New-Item -ItemType Directory -Path $appDir -Force | Out-Null
New-Item -ItemType Directory -Path $pubDir -Force | Out-Null

# Step 2: Composer install
Write-Host "  [2/6] Running composer install (production)..." -ForegroundColor Yellow
if ($composerPhar) {
    & $phpExe $composerPhar install --no-dev --optimize-autoloader --no-interaction --no-progress
} else {
    $composerExe = Get-Command composer -ErrorAction SilentlyContinue
    if ($composerExe) {
        & $composerExe.Source install --no-dev --optimize-autoloader --no-interaction --no-progress
    } else {
        Write-Host "  [ERROR] Composer not found!" -ForegroundColor Red
        exit 1
    }
}
if ($LASTEXITCODE -ne 0) {
    Write-Host "  [ERROR] Composer install failed!" -ForegroundColor Red
    exit 1
}

# Step 3: Copy app files (exclude public/, .env, dev files)
Write-Host "  [3/6] Copying application files..." -ForegroundColor Yellow

$skipNames = @(
    ".git", "node_modules", "dist", "build", ".github",
    "public", ".env", "push.ps1", "setup.ps1",
    "build-release.ps1", ".ryaan-config"
)

Get-ChildItem -Path $ScriptRoot | Where-Object {
    $skipNames -notcontains $_.Name -and $_.Name -notlike "ryaancms-v*-cpanel.zip"
} | ForEach-Object {
    if ($_.PSIsContainer) {
        Copy-Item $_.FullName -Destination "$appDir\$($_.Name)" -Recurse -Force
    } else {
        Copy-Item $_.FullName -Destination "$appDir\$($_.Name)" -Force
    }
}

# Step 4: Clean storage runtime files (keep folder structure)
Write-Host "  [4/6] Cleaning storage runtime files..." -ForegroundColor Yellow

$runtimeDirs = @(
    "$appDir\storage\logs",
    "$appDir\storage\framework\cache\data",
    "$appDir\storage\framework\sessions",
    "$appDir\storage\framework\views",
    "$appDir\bootstrap\cache"
)
foreach ($d in $runtimeDirs) {
    if (-not (Test-Path $d)) {
        New-Item -ItemType Directory -Path $d -Force | Out-Null
    }
    Get-ChildItem $d -File -ErrorAction SilentlyContinue | Remove-Item -Force
    New-Item -Path "$d\.gitkeep" -ItemType File -Force | Out-Null
}

# Step 5: Copy and fix public/ files
Write-Host "  [5/6] Copying public files and fixing index.php paths..." -ForegroundColor Yellow
Copy-Item "$ScriptRoot\public\*" -Destination $pubDir -Recurse -Force

# Fix index.php: change ../ to ../$AppFolderName/
$indexPath = "$pubDir\index.php"
$content = Get-Content $indexPath -Raw

$content = $content -replace [regex]::Escape("__DIR__ . '/../storage/app/.installed'"), "__DIR__ . '/../$AppFolderName/storage/app/.installed'"
$content = $content -replace [regex]::Escape("__DIR__ . '/../storage/framework/maintenance.php'"), "__DIR__ . '/../$AppFolderName/storage/framework/maintenance.php'"
$content = $content -replace [regex]::Escape("__DIR__.'/../storage/framework/maintenance.php'"), "__DIR__.'/../$AppFolderName/storage/framework/maintenance.php'"
$content = $content -replace [regex]::Escape("__DIR__.'/../vendor/autoload.php'"), "__DIR__.'/../$AppFolderName/vendor/autoload.php'"
$content = $content -replace [regex]::Escape("__DIR__.'/../bootstrap/app.php'"), "__DIR__.'/../$AppFolderName/bootstrap/app.php'"

[System.IO.File]::WriteAllText($indexPath, $content, [System.Text.Encoding]::UTF8)

# Fix install.php if present
$installPath = "$pubDir\install.php"
if (Test-Path $installPath) {
    $ic = Get-Content $installPath -Raw
    $ic = $ic -replace "(__DIR__ \. '\/\.\.\/)(?!$AppFolderName)", "`${1}$AppFolderName/"
    $ic = $ic -replace "(__DIR__\.'\/\.\.\/)(?!$AppFolderName)", "`${1}$AppFolderName/"
    [System.IO.File]::WriteAllText($installPath, $ic, [System.Text.Encoding]::UTF8)
}

# Step 6: Create ZIP
Write-Host "  [6/6] Creating deployment ZIP..." -ForegroundColor Yellow
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($distDir, $zipPath, [System.IO.Compression.CompressionLevel]::Optimal, $false)

$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)

# Cleanup
Remove-Item $distDir -Recurse -Force

# Done
Write-Host ""
Write-Host "  =========================================" -ForegroundColor Green
Write-Host "  Build complete!  v$Version  ($sizeMB MB)" -ForegroundColor Green
Write-Host "  =========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  File: ryaancms-v$Version-cpanel.zip" -ForegroundColor White
Write-Host ""
Write-Host "  cPanel Upload Steps:" -ForegroundColor Cyan
Write-Host "  1. Upload the ZIP to /home/username/ via File Manager"
Write-Host "  2. Extract it there"
Write-Host "  3. You will see 2 folders:"
Write-Host "     - public_html/  ->  move contents INTO your public_html/"
Write-Host "     - $AppFolderName/      ->  keep it here as /home/username/$AppFolderName/"
Write-Host "  4. In Terminal: chmod -R 775 $AppFolderName/storage $AppFolderName/bootstrap/cache"
Write-Host "  5. Visit yourdomain.com -> install wizard starts!"
Write-Host ""
