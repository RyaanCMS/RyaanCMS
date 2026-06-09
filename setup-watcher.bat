@echo off
cd /d "%~dp0"

echo.
echo   RyaanCMS Auto-Push Setup
echo   ========================
echo   This registers a Windows Task that starts the file watcher
echo   automatically when you log in. After this, just code and save.
echo   GitHub releases happen automatically.
echo.

set TASK_NAME=RyaanCMS-AutoPush
set SCRIPT=%~dp0auto-push.ps1
set PS=C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe

schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1

schtasks /create ^
  /tn "%TASK_NAME%" ^
  /tr "%PS% -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%SCRIPT%\"" ^
  /sc ONLOGON ^
  /delay 0001:00 ^
  /rl HIGHEST ^
  /f >nul

if %errorlevel% neq 0 (
    echo   ERROR: Could not create task. Try running as Administrator.
    pause
    exit /b 1
)

echo   Task registered: %TASK_NAME%
echo   Watcher starts automatically on next login.
echo.
echo   Starting watcher now (in background)...
start "" /min "%PS%" -WindowStyle Hidden -ExecutionPolicy Bypass -File "%SCRIPT%"

echo   Done! The watcher is running in the background.
echo   Just code and save files - releases happen automatically.
echo.
pause
