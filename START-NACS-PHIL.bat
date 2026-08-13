@echo off
setlocal EnableExtensions
title NACS-Phil Local Server

cd /d "%~dp0"
if errorlevel 1 (
    echo [FAIL] Could not enter the NACS-Phil project folder.
    echo Expected: %~dp0
    pause
    exit /b 1
)

if not exist "artisan" (
    echo [FAIL] artisan was not found in:
    echo %CD%
    pause
    exit /b 1
)

where php.exe >nul 2>&1
if errorlevel 1 (
    echo [FAIL] php.exe was not found on PATH.
    pause
    exit /b 1
)

echo ============================================================
echo NACS-Phil Local Website
echo ============================================================
echo Project: %CD%
echo Website: http://127.0.0.1:8000
echo Admin:   http://127.0.0.1:8000/admin
echo.
echo Keep this window open while using the website.
echo Press Ctrl+C to stop the server.
echo.

start "" "http://127.0.0.1:8000"

php artisan serve --host=127.0.0.1 --port=8000
set "NACS_EXIT=%ERRORLEVEL%"

echo.
if not "%NACS_EXIT%"=="0" (
    echo [FAIL] Laravel server stopped with exit code %NACS_EXIT%.
    pause
)

exit /b %NACS_EXIT%