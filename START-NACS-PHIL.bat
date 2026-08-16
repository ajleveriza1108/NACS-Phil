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

set "NACS_PORT="
set "NACS_LAN_IP="

rem Select the first free local port at runtime. Nothing is persisted.
for /f "delims=" %%P in ('powershell.exe -NoProfile -Command "$chosen=$null;foreach($p in 8000..8010){$l=$null;try{$l=[System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Any,$p);$l.Start();$l.Stop();$chosen=$p;break}catch{if($l){try{$l.Stop()}catch{}}}};if($chosen){$chosen}" 2^>nul') do (
    set "NACS_PORT=%%P"
)

if not defined NACS_PORT (
    echo [FAIL] No free local preview port was found from 8000 through 8010.
    echo Close another local NACS/PHP server and run this BAT again.
    pause
    exit /b 1
)

rem Detect the current private LAN IPv4 address at runtime.
rem Wi-Fi is preferred; another active private-LAN adapter may be used as fallback.
for /f "delims=" %%I in ('powershell.exe -NoProfile -Command "$wifi=$null;$fallback=$null;$cfgs=Get-NetIPConfiguration;foreach($c in $cfgs){if($c.NetAdapter.Status -eq 'Up' -and $c.IPv4DefaultGateway -and $c.IPv4Address){foreach($a in $c.IPv4Address){$ip=[string]$a.IPAddress;try{$b=([Net.IPAddress]::Parse($ip)).GetAddressBytes();$private=($b[0]-eq10)-or($b[0]-eq192-and$b[1]-eq168)-or($b[0]-eq172-and$b[1]-ge16-and$b[1]-le31);if($private){if($c.InterfaceAlias -match 'Wi-Fi|WiFi|Wireless'){$wifi=$ip}elseif(-not$fallback){$fallback=$ip}}}catch{}}}};if($wifi){$wifi}elseif($fallback){$fallback}" 2^>nul') do (
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
    echo Copying the CURRENT phone URL to your Windows clipboard...
    >nul 2>&1 echo http://%NACS_LAN_IP%:%NACS_PORT%| clip
    echo.
    echo PHONE TEST:
    echo   1. Keep this CMD window open.
    echo   2. Keep the PC and phone/tablet on the SAME trusted Wi-Fi.
    echo   3. Open the PHONE / TABLET URL printed above.
    echo   4. Test portrait and landscape, mobile menu, Home/About, forms, and scrolling.
    echo   5. If Windows Firewall asks about PHP, allow PRIVATE networks only.
    echo.
) else (
    echo [PHONE / TABLET]
    echo No current private LAN IPv4 address was detected automatically.
    echo The PC website will still run.
    echo.
    echo If both devices are on the same Wi-Fi, run ipconfig and use the CURRENT
    echo active Wi-Fi IPv4 address with the CURRENT port shown above.
    echo Example format only: http://YOUR-CURRENT-PC-IP:%NACS_PORT%
    echo.
)

echo IMPORTANT:
echo   - The LAN IP and port are recalculated every time this BAT starts.
echo   - No previous LAN IP or previous port is stored or reused.
echo   - 127.0.0.1 is only the standard local PC loopback address.
echo   - 0.0.0.0 is only the Laravel bind address for local-network testing.
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
    pause
)

exit /b %NACS_EXIT%
