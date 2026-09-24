@echo off
REM Set Admin Password - Windows Batch Helper
REM Usage: SET_PASSWORD.bat [password]
REM Default password: admin123

setlocal enabledelayedexpansion

cd /d "%~dp0"

echo.
echo ╔═══════════════════════════════════════════════╗
echo ║     🔑 MUSICALL - Set Admin Password          ║
echo ╚═══════════════════════════════════════════════╝
echo.

set PASSWORD=%1
if "%PASSWORD%"=="" set PASSWORD=admin123

echo Starting PHP Script...
echo.

php set-password.php %PASSWORD%

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ❌ Script failed with error code %ERRORLEVEL%
    echo.
    echo Troubleshooting:
    echo 1. Assicurati che PHP sia installato e nel PATH
    echo 2. Verifica che il file set-password.php esista
    echo 3. Verifica che la cartella database/ sia scrivibile
    echo.
    pause
    exit /b 1
)

echo.
pause
