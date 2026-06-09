# auto-push.ps1
# Watches project files. When files stop changing for 60 seconds -> auto commit + push.
# GitHub Actions then builds the release automatically.
# Run once at startup via setup-watcher.bat

param(
    [int]$DebounceSeconds = 60,
    [string]$ProjectDir   = $PSScriptRoot
)

Set-Location $ProjectDir

$SKIP_DIRS  = @('vendor','node_modules','.git','dist','storage\logs','storage\framework\cache')
$WATCH_EXTS = @('*.php','*.blade.php','*.js','*.css','*.json','*.env.example','*.sql','*.yml','*.yaml','*.md')

Write-Host ""
Write-Host "  RyaanCMS Auto-Push Watcher" -ForegroundColor Cyan
Write-Host "  Watching: $ProjectDir"      -ForegroundColor DarkGray
Write-Host "  Debounce: ${DebounceSeconds}s after last change" -ForegroundColor DarkGray
Write-Host "  Press Ctrl+C to stop."      -ForegroundColor DarkGray
Write-Host ""

# ── Setup FileSystemWatcher ───────────────────────────────────
$watcher                     = New-Object System.IO.FileSystemWatcher
$watcher.Path                = $ProjectDir
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter        = [System.IO.NotifyFilters]::LastWrite -bor [System.IO.NotifyFilters]::FileName
$watcher.EnableRaisingEvents = $true

# Shared flag: last-change timestamp
$global:LastChange = $null

$onChange = {
    $path = $Event.SourceEventArgs.FullPath

    # Ignore noisy/private paths
    foreach ($skip in @('\.git\\','\\vendor\\','\\node_modules\\','\\dist\\',
                        '\\storage\\logs\\','auto-push\.ps1','\.zip$')) {
        if ($path -match $skip) { return }
    }
    # Ignore .env (never commit it)
    if ($path -match '\.env$') { return }

    $global:LastChange = [DateTime]::UtcNow
}

$null = Register-ObjectEvent $watcher Changed -Action $onChange
$null = Register-ObjectEvent $watcher Created -Action $onChange
$null = Register-ObjectEvent $watcher Deleted -Action $onChange
$null = Register-ObjectEvent $watcher Renamed -Action $onChange

# ── Poll loop ─────────────────────────────────────────────────
function Has-UncommittedChanges {
    $prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"
    $status = git status --porcelain 2>&1 | Where-Object { $_ -notmatch '\.env$' -and $_ -notmatch '\.zip$' }
    $ErrorActionPreference = $prev
    return ($status | Where-Object { $_.Trim() }).Count -gt 0
}

function Auto-Commit {
    $prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"

    # Stage everything except .env and ZIPs (gitignore handles these)
    git add -A 2>&1 | Out-Null

    # Build commit message from changed files
    $changed = git diff --cached --name-only 2>&1 | Where-Object { $_.Trim() }
    if (-not $changed) { $ErrorActionPreference = $prev; return $false }

    $fileList = ($changed | Select-Object -First 5) -join ', '
    if ($changed.Count -gt 5) { $fileList += " (+$($changed.Count - 5) more)" }
    $timestamp = (Get-Date -Format "yyyy-MM-dd HH:mm")
    $msg = "feat: auto-update $timestamp — $fileList"

    git commit -m $msg 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) { $ErrorActionPreference = $prev; return $false }

    Write-Host "  [$(Get-Date -Format 'HH:mm:ss')] Committed: $fileList" -ForegroundColor Green

    git push origin main 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [$(Get-Date -Format 'HH:mm:ss')] Pushed -> GitHub Actions building release..." -ForegroundColor Cyan
    } else {
        Write-Host "  [$(Get-Date -Format 'HH:mm:ss')] Push failed. Will retry next cycle." -ForegroundColor Red
    }

    $ErrorActionPreference = $prev
    return $true
}

# Main loop
while ($true) {
    Start-Sleep -Seconds 5

    if ($global:LastChange -eq $null) { continue }

    $elapsed = ([DateTime]::UtcNow - $global:LastChange).TotalSeconds
    if ($elapsed -lt $DebounceSeconds)  { continue }

    # Debounce period passed — check if anything to commit
    if (Has-UncommittedChanges) {
        $global:LastChange = $null
        Auto-Commit
    } else {
        $global:LastChange = $null
    }
}
