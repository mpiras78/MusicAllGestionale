@echo off
REM ========================================
REM Reset Rate Limiter
REM ========================================

cls
echo.
echo ╔════════════════════════════════════════════════╗
echo ║  🔓 MUSICALL - Reset Rate Limiter             ║
echo ╚════════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

echo Resetting rate limiter...
echo.

php reset-rate-limit.php

if %errorlevel% neq 0 (
    echo.
    echo ❌ ERROR: Reset failed
    echo.
    pause
    exit /b 1
)

echo.
echo Ready to login!
echo.
pause
