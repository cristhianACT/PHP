# Sistema POS - Tienda Local

Este es un sistema de Punto de Venta (POS) desarrollado en PHP y Microsoft SQL Server.

## 🚀 Instalación y Configuración

### 1. Base de Datos (SQL Server)
1. Abra SQL Server Management Studio (SSMS).
2. Abra el archivo `sql/setup.sql` que se encuentra en este proyecto.
3. Ejecute el script completo para crear la base de datos `tienda` y las tablas necesarias.

### 2. Conexión PHP
1. Abra el archivo `config/conexion.php`.
2. Edite las credenciales según su configuración local:
   ```php
   $serverName = "localhost"; // O su instancia, ej: DESKTOP-XYZ\SQLEXPRESS
   $connectionOptions = array(
       "Database" => "tienda",
       "Uid" => "sa", // Su usuario SQL
       "PWD" => "su_contraseña" // Su contraseña SQL
   );
   ```
3. Asegúrese de tener habilitada la extensión `sqlsrv` en su archivo `php.ini`.

### 3. Crear Primer Usuario (Admin)
1. Una vez configurada la base de datos, abra el navegador en:
   `http://localhost/crear_admin.php`
2. Llene el formulario.
3. En "Clave Maestra", ingrese: `sistema123`
4. Esto creará su usuario administrador.

### 4. Ingresar al Sistema
1. Vaya a `http://localhost/` o `http://localhost/login.php`.
2. Ingrese con el usuario y contraseña creados anteriormente.

## 📦 Funcionalidades

- **Dashboard**: Vista general de ventas del día e inventario.
- **Productos**:
  - Listar todos los productos.
  - Crear nuevos productos con control de stock.
  - Editar precios y detalles.
  - Eliminar productos (Soft delete).
- **Ventas (POS)**:
  - Interfaz rápida para cobro.
  - Búsqueda de productos en tiempo real.
  - Cálculo automático de totales.
  - Actualización automática de inventario al vender.
- **Seguridad**:
  - Login de usuarios.
  - Protección de rutas.
  - Encriptación de contraseñas.

## 🛠 Tecnologías
- PHP 8.x
- Microsoft SQL Server
- HTML5 / CSS3 (Diseño Moderno)
- JavaScript (Vainilla)
