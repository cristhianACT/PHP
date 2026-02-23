@echo off
set PHP_BIN=php

if exist "C:\xampp\php\php.exe" set PHP_BIN="C:\xampp\php\php.exe"

echo Iniciando servidor POS usando: %PHP_BIN%
echo.
echo -----------------------------------------------------------
echo   ABRE TU NAVEGADOR EN: http://localhost:8000/crear_admin.php
echo -----------------------------------------------------------
echo.
echo Presiona Ctrl+C para detener el servidor.

%PHP_BIN% -S localhost:8000
if %errorlevel% neq 0 (
    echo.
    echo ERROR: No se pudo iniciar PHP.
    echo Verifica que tengas XAMPP instalado en C:\xampp
)
pause
