@echo off
setlocal
cd /d "%~dp0"
echo ============================================================
echo NACS-Phil - Create or Update Administrator
echo ============================================================
php artisan nacs:create-admin
set "EXITCODE=%ERRORLEVEL%"
echo.
pause
exit /b %EXITCODE%
