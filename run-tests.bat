@echo off
REM Script per eseguire test MusicAll

echo ========================================
echo   MusicAll Test Suite
echo ========================================
echo.

if "%1"=="" goto all
if "%1"=="unit" goto unit
if "%1"=="integration" goto integration
if "%1"=="feature" goto feature
if "%1"=="coverage" goto coverage
goto help

:all
echo Esecuzione TUTTI i test...
echo.
vendor\bin\phpunit
goto end

:unit
echo Esecuzione Unit Tests...
echo.
vendor\bin\phpunit --testsuite Unit
goto end

:integration
echo Esecuzione Integration Tests...
echo.
vendor\bin\phpunit --testsuite Integration
goto end

:feature
echo Esecuzione Feature Tests...
echo.
vendor\bin\phpunit --testsuite Feature
goto end

:coverage
echo Generazione Coverage Report...
echo.
vendor\bin\phpunit --coverage-html coverage/
echo.
echo Report generato in: coverage/index.html
goto end

:help
echo.
echo Uso: run-tests.bat [opzione]
echo.
echo Opzioni:
echo   (nessuna)    - Esegue tutti i test
echo   unit         - Solo unit tests
echo   integration  - Solo integration tests
echo   feature      - Solo feature tests
echo   coverage     - Genera report coverage
echo.
goto end

:end
echo.
echo ========================================
pause
