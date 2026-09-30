@echo off
setlocal

set "PHP_EXE=C:\EasyPHP-Webserver-14.1b2\binaries\php8\php.exe"
set "WEB_ROOT=C:\EasyPHP-Webserver-14.1b2\www"

if not exist "%PHP_EXE%" (
    echo PHP 8 non trovato in "%PHP_EXE%".
    exit /b 1
)

echo Arresta prima Apache EasyPHP: la porta 888 deve essere libera.
echo Avvio PHP 8 su http://192.168.1.190:888/MusicAllGestionale/
"%PHP_EXE%" -S 0.0.0.0:888 -t "%WEB_ROOT%"
