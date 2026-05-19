@echo off
chcp 65001 >nul
cd /d "%~dp0"

set "PHP_EXE="

php -v >nul 2>&1
if not errorlevel 1 set "PHP_EXE=php"

if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE for /d %%D in ("C:\laragon\bin\php\php-*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"

if not defined PHP_EXE (
    echo.
    echo [HATA] PHP bulunamadi. Secenekler:
    echo   - XAMPP: https://www.apachefriends.org/ — projeyi htdocs altina koyup
    echo     http://localhost/iha-sistem/index.html
    echo   - veya Windows icin PHP yukleyip PATH'e ekleyin
    echo.
    pause
    exit /b 1
)

echo.
echo Proje: %cd%
echo Tarayicida acin: http://localhost:8000/index.html
echo Sisteme Giris butonu index.php sayfasini acar. Durdurmak: Ctrl+C
echo.
"%PHP_EXE%" -S localhost:8000
