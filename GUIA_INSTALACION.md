# 🛠 Guía de Instalación de PHP con SQL Server

Para que el sistema funcione, necesitamos instalar **PHP** y los **Controladores de SQL Server**. Es un proceso de 3 pasos.

## Paso 1: Descargar PHP
1. Entra a: [https://windows.php.net/download/](https://windows.php.net/download/)
2. Busca la sección **VS16 x64 Thread Safe**.
3. Descarga el **Zip**.
4. Crea una carpeta en tu disco C llamada `php` (es decir: `C:\php`).
5. Extrae todo el contenido del Zip ahí.

## Paso 2: Configurar Variables de Entorno
1. Presiona la tecla `Windows`, escribe **"Variables de entorno"** y ábrelo.
2. Haz clic en el botón **"Variables de entorno..."**.
3. En la lista de abajo ("Variables del sistema"), busca la que dice **Path** y dale doble clic.
4. Dale a **"Nuevo"** y escribe: `C:\php`
5. Acepta todo.

## Paso 3: Conectar PHP con SQL Server (Drivers)
PHP no trae soporte para SQL Server por defecto. Debemos agregarlo.

1. **Descargar Drivers**:
   - Entra a: [Microsoft Drivers for PHP for SQL Server](https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)
   - Descarga el archivo `.exe` y ejecútalo. Te pedirá una carpeta, elige una temporal.
   - Busca los archivos que coincidan con tu versión de PHP (probablemente 8.2 o 8.3).
   - Necesitas copiar estos dos archivos a la carpeta `C:\php\ext`:
     - `php_sqlsrv_82_ts_x64.dll` (Si bajaste PHP 8.2)
     - `php_pdo_sqlsrv_82_ts_x64.dll`

2. **Activar en php.ini**:
   - Ve a `C:\php`.
   - Busca el archivo `php.ini-development` y cámbiale el nombre a `php.ini`.
   - Ábrelo con el Bloc de Notas.
   - Agrega estas líneas al final del archivo:
     ```ini
     extension=php_sqlsrv_82_ts_x64.dll
     extension=php_pdo_sqlsrv_82_ts_x64.dll
     extension=openssl
     extension=mbstring
     ```
   - (Asegúrate de que los nombres de los archivos `.dll` coincidan exactamente con los que copiaste).

## Paso 4: Instalar ODBC Driver
Para que Windows hable con SQL Server:
1. Descarga el [ODBC Driver 17 for SQL Server](https://go.microsoft.com/fwlink/?linkid=2137027).
2. Instálalo (Siguiente, Siguiente...).

---
## ¡Listo!
Ahora cierra cualquier terminal que tengas abierta, abre una nueva y prueba ejecutando:
`php -v`

Si sale la versión, ¡ya puedes ejecutar el archivo `iniciar_sistema.bat`!
