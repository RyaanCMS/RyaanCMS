@echo off
cd /d "%~dp0"

echo.
echo   RyaanCMS - Push ^& Auto Release
echo   =================================
echo.

git add -A
git diff --cached --quiet && (
    echo   Nothing to commit.
    goto done
)

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set dt=%%I
set MSG=feat: update %dt:~0,8%-%dt:~8,6%

git commit -m "%MSG%"
git push origin main

echo.
echo   Pushed! GitHub Actions is building the release now.
echo   https://github.com/RyaanCMS/RyaanCMS/actions
echo.

:done
pause
