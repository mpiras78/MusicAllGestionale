@echo off
REM ========================================
REM MusicAll - Script Avvio Server Locale
REM ========================================

cls
echo.
echo ╔════════════════════════════════════════════════╗
echo ║     🎵 MUSICALL - AVVIO SERVER LOCALE 🎵       ║
echo ╚════════════════════════════════════════════════╝
echo.

REM Verifica PHP
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ ERRORE: PHP non trovato in PATH
    echo.
    echo Soluzione:
    echo 1. Installa PHP da: https://www.php.net/downloads
    echo 2. Aggiungi PHP al PATH di Windows
    echo 3. Riavvia questo script
    pause
    exit /b 1
)

echo ✅ PHP trovato
php --version | findstr /r "PHP"
echo.

REM Verifica vendor
if not exist "vendor\" (
    echo ⚠️  vendor/ non trovato - Installo dipendenze...
    echo.
    
    REM Verifica composer.phar locale
    if exist "composer.phar" (
        php composer.phar install
    ) else (
        echo ❌ ERRORE: composer.phar non trovato
        echo.
        echo Scarica composer.phar da: https://getcomposer.org/download/
        pause
        exit /b 1
    )
    echo.
)

echo ✅ Dipendenze OK
echo.

REM Verifica database
if not exist "database\musicall.sqlite" (
    echo ⚠️  Database non trovato - SQLite lo creerà automaticamente
    echo.
)

echo ╔════════════════════════════════════════════════╗
echo ║              AVVIO SERVER IN CORSO              ║
echo ╚════════════════════════════════════════════════╝
echo.
echo 🌐 Accedi a: http://localhost:8000
echo.
echo Credenziali:
echo   👤 Username: admin
echo   🔑 Password: admin123
echo.
echo ⚠️  IMPORTANTE: Cambia password al primo login!
echo   Vai a: http://localhost:8000/profile.php
echo.
echo Per fermare il server: Premi CTRL+C
echo.
echo ════════════════════════════════════════════════
echo.

REM Avvia server
php -S localhost:8000

REM Se il server si chiude, mostra messaggio
if %errorlevel% neq 0 (
    echo.
    echo ❌ ERRORE: Server chiuso unexpectedly
    echo.
    pause
)
