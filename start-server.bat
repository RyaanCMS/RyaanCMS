@echo off
title RyaanCMS — http://127.0.0.1:8001

set PHP_BIN=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
set PROJECT=c:\Users\hp\Desktop\AI claude\RyaanCMS

echo.
echo  ==========================================
echo   RyaanCMS — Starting development server
echo   http://127.0.0.1:8001
echo  ==========================================
echo.

cd /d "%PROJECT%"
"%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8001

pause
