# auto-push.ps1
# Watches project files. When files stop changing for 60 seconds -> auto commit + push.
# GitHub Actions then builds the release automatically.

param(
    [int]$DebounceSeconds = 60,
    [string]$ProjectDir   = $PSScriptRoot
)

Set-Location $ProjectDir

Write-Host ""
Write-Host "  RyaanCMS Auto-Push Watcher" -ForegroundColor Cyan
Write-Host "  Watching: $ProjectDir"      -ForegroundColor DarkGray
Write-Host "  Debounce: ${DebounceSeconds}s after last change" -ForegroundColor DarkGray
Write-Host "  Press Ctrl+C to stop."      -ForegroundColor DarkGray
Write-Host ""

# ── Setup FileSystemWatcher ───────────────────────────────────
$watcher                       = New-Object System.IO.FileSystemWatcher
$watcher.Path                  = $ProjectDir
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter          = [System.IO.NotifyFilters]::LastWrite -bor [System.IO.NotifyFilters]::FileName
$watcher.EnableRaisingEvents   = $true

$global:LastChange = $null

$onChange = {
    $path = $Event.SourceEventArgs.FullPath
    foreach ($skip in @('\.git\\','\\vendor\\','\\node_modules\\','\\dist\\',
                        '\\storage\\logs\\','auto-push\.ps1','\.zip$','\.env$')) {
        if ($path -match $skip) { return }
    }
    $global:LastChange = [DateTime]::UtcNow
}

$null = Register-ObjectEvent $watcher Changed -Action $onChange
$null = Register-ObjectEvent $watcher Created -Action $onChange
$null = Register-ObjectEvent $watcher Deleted -Action $onChange
$null = Register-ObjectEvent $watcher Renamed -Action $onChange

# ── Helper functions ──────────────────────────────────────────
function Has-UncommittedChanges {
    $prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"
    $s = git status --porcelain 2>&1 | Where-Object { $_ -notmatch '\.env$' -and $_ -notmatch '\.zip$' }
    $ErrorActionPreference = $prev
    return ($s | Where-Object { $_.Trim() }).Count -gt 0
}

function Auto-Commit {
    $prev = $ErrorActionPreference; $ErrorActionPreference = "Continue"

    git add -A 2>&1 | Out-Null

    $changed = git diff --cached --name-only 2>&1 | Where-Object { $_.Trim() }
    if (-not $changed) { $ErrorActionPreference = $prev; return }

    $fileList = ($changed | Select-Object -First 5) -join ", "
    if ($changed.Count -gt 5) { $fileList += " (+" + ($changed.Count - 5) + " more)" }

    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"
    $msg = "feat: auto-update $timestamp - $fileList"

    git commit -m $msg 2>&1 | Out-Null

    $time = Get-Date -Format "HH:mm:ss"
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [$time] Committed: $fileList" -ForegroundColor Green
        git push origin main 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [$time] Pushed -> GitHub Actions building release..." -ForegroundColor Cyan
        } else {
            Write-Host "  [$time] Push failed. Will retry on next change." -ForegroundColor Red
        }
    }

    $ErrorActionPreference = $prev
}

# ── Main poll loop ────────────────────────────────────────────
while ($true) {
    Start-Sleep -Seconds 5

    if ($null -eq $global:LastChange) { continue }

    $elapsed = ([DateTime]::UtcNow - $global:LastChange).TotalSeconds
    if ($elapsed -lt $DebounceSeconds) { continue }

    $global:LastChange = $null
    if (Has-UncommittedChanges) { Auto-Commit }
}
