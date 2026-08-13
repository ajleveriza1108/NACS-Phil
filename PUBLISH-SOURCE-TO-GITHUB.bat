@echo off
setlocal
cd /d "%~dp0"
echo ============================================================
echo NACS-Phil - Validate and Publish Safe Source to GitHub
echo ============================================================
echo This does not publish .env, the SQLite database, uploads,
echo vendor dependencies, node_modules, logs, or local backups.
echo.
set /p CONFIRM=Type PUBLISH to continue: 
if /I not "%CONFIRM%"=="PUBLISH" (
  echo Cancelled.
  pause
  exit /b 1
)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\nacs\publish-source.ps1"
set "EXITCODE=%ERRORLEVEL%"
echo.
pause
exit /b %EXITCODE%
