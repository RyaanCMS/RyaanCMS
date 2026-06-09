@echo off
title RyaanCMS Dev Server

set PHP_BIN=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
set PROJECT=C:\Users\hp\Desktop\AI claude\RyaanCMS

cd /d "%PROJECT%"

echo.
echo  ==========================================
echo   RyaanCMS — Starting servers
echo   http://127.0.0.1:8000
echo   http://127.0.0.1:8001
echo  ==========================================
echo.

:: Start port 8000 in a hidden background window
start "RyaanCMS-8000" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000

:: Start port 8001 in a hidden background window
start "RyaanCMS-8001" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8001

echo  Both servers started.
echo  http://127.0.0.1:8000  (port 8000)
echo  http://127.0.0.1:8001  (port 8001)
echo.
echo  Press any key to stop all RyaanCMS servers...
pause >nul

:: Stop both servers when this window is closed
taskkill /FI "WINDOWTITLE eq RyaanCMS-8000*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq RyaanCMS-8001*" /F >nul 2>&1
echo  Servers stopped.
