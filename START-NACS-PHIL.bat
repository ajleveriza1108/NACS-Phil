@echo off
setlocal EnableExtensions DisableDelayedExpansion
title NACS-Phil Local Website - PC + Phone

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

set "NACS_PORT=8000"
set "NACS_LAN_IP="

for /f "delims=" %%I in ('powershell.exe -NoProfile -Command "$c=Get-NetIPConfiguration ^| Where-Object {$_.IPv4DefaultGateway -and $_.IPv4Address} ^| Select-Object -First 1; if($c){$c.IPv4Address.IPAddress ^| Select-Object -First 1}" 2^>nul') do (
    set "NACS_LAN_IP=%%I"
)

set "NACS_PC_URL=http://127.0.0.1:%NACS_PORT%"
set "NACS_PC_ADMIN=%NACS_PC_URL%/admin"

echo ============================================================
echo NACS-Phil Local Website - PC + Same-Wi-Fi Phone/Tablet
echo ============================================================
echo Project: %CD%
echo.
echo [PC WEBSITE]
echo %NACS_PC_URL%
echo.
echo [PC ADMIN]
echo %NACS_PC_ADMIN%
echo.

if defined NACS_LAN_IP (
    set "NACS_PHONE_URL=http://%NACS_LAN_IP%:%NACS_PORT%"
    set "NACS_PHONE_ADMIN=http://%NACS_LAN_IP%:%NACS_PORT%/admin"

    echo [PHONE / TABLET - SAME WI-FI]
    echo http://%NACS_LAN_IP%:%NACS_PORT%
    echo.
    echo [PHONE / TABLET ADMIN]
    echo http://%NACS_LAN_IP%:%NACS_PORT%/admin
    echo.
    echo The phone website URL is being copied to your Windows clipboard...
    >nul 2>&1 echo http://%NACS_LAN_IP%:%NACS_PORT%| clip
    echo.
    echo PHONE TEST:
    echo   1. Keep this CMD window open.
    echo   2. Connect the PC and phone/tablet to the SAME trusted Wi-Fi.
    echo   3. Open the PHONE / TABLET URL above in the phone browser.
    echo   4. Test portrait and landscape, the menu, Home/About, forms, and scrolling.
    echo   5. If Windows Firewall asks about PHP, allow PRIVATE networks only.
    echo.
) else (
    echo [PHONE / TABLET]
    echo LAN IPv4 address was not detected automatically.
    echo The PC website will still run.
    echo For phone testing, run ipconfig and use the active Wi-Fi IPv4 address:
    echo   http://YOUR-PC-IP:%NACS_PORT%
    echo.
)

echo IMPORTANT:
echo   - This is a LOCAL development server, not the public internet website.
echo   - Phone/tablet access normally works only while both devices share the same LAN/Wi-Fi.
echo   - Do not expose this development server through router port-forwarding.
echo   - Keep this window open while testing.
echo   - Press Ctrl+C to stop the Laravel server.
echo.
echo Starting NACS-Phil...
echo.

start "" "%NACS_PC_URL%"

php artisan serve --host=0.0.0.0 --port=%NACS_PORT%
set "NACS_EXIT=%ERRORLEVEL%"

echo.
if not "%NACS_EXIT%"=="0" (
    echo [FAIL] Laravel server stopped with exit code %NACS_EXIT%.
    echo If port %NACS_PORT% is already in use, close the other local server and run this BAT again.
    pause
)

exit /b %NACS_EXIT%
