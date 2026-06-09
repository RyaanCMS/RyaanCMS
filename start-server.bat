@echo off
title RyaanCMS Dev Server

set PHP_BIN=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
set MYSQLD=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe
set MYSQL_INI=C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini
set PROJECT=C:\Users\hp\Desktop\AI claude\RyaanCMS

cd /d "%PROJECT%"

echo.
echo  ==========================================
echo   RyaanCMS — Starting services
echo  ==========================================
echo.

:: ── Step 1: Start MySQL ───────────────────────────────────────────────────
echo  [1/3] Starting MySQL on port 3306...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if %errorlevel% == 0 (
    echo        MySQL already running — skipping.
) else (
    start "RyaanCMS-MySQL" /min "%MYSQLD%" --defaults-file="%MYSQL_INI%"
    echo        MySQL started. Waiting 5 seconds to initialize...
    timeout /t 5 /nobreak >nul
)

:: ── Step 2: Start PHP on port 8000 ───────────────────────────────────────
echo  [2/3] Starting PHP server on port 8000...
start "RyaanCMS-8000" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000

:: ── Step 3: Start PHP on port 8001 ───────────────────────────────────────
echo  [3/3] Starting PHP server on port 8001...
start "RyaanCMS-8001" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8001

echo.
echo  ==========================================
echo   All services running:
echo   MySQL  → port 3306
echo   App    → http://127.0.0.1:8000
echo   App    → http://127.0.0.1:8001
echo  ==========================================
echo.
echo  Press any key to STOP all services...
pause >nul

:: ── Stop everything ───────────────────────────────────────────────────────
echo  Stopping servers...
taskkill /FI "WINDOWTITLE eq RyaanCMS-8000*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq RyaanCMS-8001*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq RyaanCMS-MySQL*" /F >nul 2>&1
echo  Done.
