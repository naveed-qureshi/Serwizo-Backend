@echo off
title SerWizo Backend Server
echo ====================================================
echo Starting SerWizo Backend Server
echo Access from PC: http://127.0.0.1:8000
echo Access from Phone (Wi-Fi): http://192.168.100.100:8000
echo ====================================================

set "PHP_DIR=C:\Users\Naveed Qureshi\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe"
set "PATH=%PHP_DIR%;%PATH%"

cd /d "%~dp0"
"%PHP_DIR%\php.exe" artisan serve --host=0.0.0.0 --port=8000
pause
