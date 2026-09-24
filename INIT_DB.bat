@echo off
REM ========================================
REM MusicAll - Database Initialization
REM ========================================

cls
echo.
echo ╔════════════════════════════════════════════════╗
echo ║   🎵 MUSICALL - Database Initialization 🎵    ║
echo ╚════════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

echo Initializing database...
echo.

php init-db.php

if %errorlevel% neq 0 (
    echo.
    echo ❌ ERROR: Database initialization failed
    echo.
    pause
    exit /b 1
)

echo.
echo.
echo ╔════════════════════════════════════════════════╗
echo ║              NEXT STEPS                         ║
echo ╚════════════════════════════════════════════════╝
echo.
echo 1️⃣  Start server:
echo    .\START_SERVER.bat
echo.
echo 2️⃣  Or manually:
echo    php -S localhost:8000
echo.
echo 3️⃣  Access:
echo    http://localhost:8000
echo.
echo 4️⃣  Login:
echo    Username: admin
echo    Password: admin123
echo.
echo ⚠️  Change password at first login:
echo    http://localhost:8000/profile.php
echo.
pause
