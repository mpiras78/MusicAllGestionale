@echo off
setlocal

set "PHP_CGI=C:\EasyPHP-Webserver-14.1b2\binaries\php8\php-cgi.exe"

if not exist "%PHP_CGI%" (
    echo PHP 8 CGI non trovato in "%PHP_CGI%".
    exit /b 1
)

echo Avvio PHP 8 FastCGI su 127.0.0.1:9000.
"%PHP_CGI%" -b 127.0.0.1:9000
