@echo off
setlocal EnableExtensions
title PasarLokal - One Click Launcher

REM ==========================================================
REM PASARLOKAL ONE-CLICK LAUNCHER
REM Project : C:\xampp\htdocs\pasarlokal_website
REM Services: MySQL + Laravel + Vite
REM ==========================================================

set "PROJECT=C:\xampp\htdocs\pasarlokal_website"
set "XAMPP=C:\xampp"
set "URL=http://127.0.0.1:8000"
set "LARAVEL_PORT=8000"
set "VITE_PORT=5173"
set "MYSQL_PORT=3306"

color 0A
echo.
echo  =========================================================
echo       PASARLOKAL ^| ONE-CLICK DEVELOPMENT LAUNCHER
echo  =========================================================
echo.

if not exist "%PROJECT%\artisan" (
    color 0C
    echo [ERROR] File artisan tidak ditemukan.
    echo Periksa lokasi project:
    echo %PROJECT%
    echo Jika folder project berbeda, edit nilai PROJECT di file BAT ini.
    pause
    exit /b 1
)

where php >nul 2>&1
if errorlevel 1 (
    color 0C
    echo [ERROR] PHP tidak ditemukan di PATH.
    echo Tambahkan C:\xampp\php ke PATH, atau jalankan dari terminal XAMPP.
    pause
    exit /b 1
)

if not exist "%PROJECT%\vendor\autoload.php" (
    color 0C
    echo [ERROR] Folder vendor belum tersedia.
    echo Buka terminal di folder project lalu jalankan: composer install
    pause
    exit /b 1
)

echo [1/5] Memeriksa MySQL XAMPP...
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (Get-NetTCPConnection -State Listen -LocalPort %MYSQL_PORT% -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if errorlevel 1 (
    if exist "%XAMPP%\mysql_start.bat" (
        echo       MySQL belum terdeteksi. Mencoba memulai MySQL...
        start "PasarLokal - MySQL" /min "%XAMPP%\mysql_start.bat"
        timeout /t 5 /nobreak >nul
    ) else (
        echo [PERINGATAN] mysql_start.bat tidak ditemukan.
        echo Silakan nyalakan MySQL dari XAMPP Control Panel.
    )
) else (
    echo       MySQL sudah berjalan pada port %MYSQL_PORT%.
)

echo.
echo [2/5] Memeriksa port Laravel %LARAVEL_PORT%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (Get-NetTCPConnection -State Listen -LocalPort %LARAVEL_PORT% -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if errorlevel 1 (
    echo       Menjalankan Laravel...
    start "PasarLokal - Laravel" cmd /k "cd /d ""%PROJECT%"" && php artisan serve --host=127.0.0.1 --port=%LARAVEL_PORT%"
) else (
    echo       Port %LARAVEL_PORT% sudah digunakan. Tidak menjalankan server Laravel kedua.
)

echo.
echo [3/5] Memeriksa kebutuhan Vite...
if not exist "%PROJECT%\package.json" (
    echo [PERINGATAN] package.json tidak ditemukan. Vite dilewati.
    goto :skip_vite
)
if not exist "%PROJECT%\node_modules" (
    echo [PERINGATAN] node_modules belum ada.
    echo Jalankan "npm install" di folder project terlebih dahulu.
    goto :skip_vite
)

echo [4/5] Memeriksa port Vite %VITE_PORT%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (Get-NetTCPConnection -State Listen -LocalPort %VITE_PORT% -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if errorlevel 1 (
    echo       Menjalankan Vite...
    start "PasarLokal - Vite" cmd /k "cd /d ""%PROJECT%"" && npm run dev"
) else (
    echo       Port %VITE_PORT% sudah digunakan. Tidak menjalankan Vite kedua.
)

:skip_vite
echo.
echo [5/5] Membuka PasarLokal di browser...
timeout /t 3 /nobreak >nul
start "" "%URL%"

echo.
echo  =========================================================
echo   Launcher selesai dijalankan.
echo.
echo   Website : %URL%
echo   Project : %PROJECT%
echo.
echo   Biarkan jendela Laravel dan Vite tetap terbuka saat coding.
echo   Tutup masing-masing jendela server untuk menghentikannya.
echo  =========================================================
echo.
pause
endlocal
