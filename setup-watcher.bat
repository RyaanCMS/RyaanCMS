@echo off
cd /d "%~dp0"

echo.
echo   RyaanCMS Auto-Push Setup
echo   ========================
echo.

set SCRIPT=%~dp0auto-push.ps1
set STARTUP=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup
set VBS=%STARTUP%\RyaanCMS-AutoPush.vbs

:: Create a silent VBScript launcher in Windows Startup folder
(
echo Set oShell = CreateObject^("WScript.Shell"^)
echo oShell.Run "powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File ""%SCRIPT%""", 0, False
) > "%VBS%"

if not exist "%VBS%" (
    echo   ERROR: Could not write to Startup folder.
    pause
    exit /b 1
)

echo   Startup entry created: %VBS%
echo.
echo   Starting watcher now...

:: Start immediately (hidden window via VBScript)
cscript //nologo "%VBS%"

echo   Watcher is running in the background.
echo   Starts automatically on every login - no admin needed.
echo.
echo   Flow: save files ^> 60 sec ^> auto commit+push ^> GitHub releases
echo.
pause
