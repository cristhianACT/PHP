<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("<div style='font-family:sans-serif; text-align:center; padding:5rem;'><h2> Acceso Denegado</h2><p>Solo el administrador puede ejecutar actualizaciones de base de datos.</p><a href='/'>Volver al Inicio</a></div>");
}

echo "<div style='font-family:sans-serif; padding: 2rem; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 1rem; margin-top: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>";
echo "<h2 style='color:#4f46e5; text-align:center;'> Actualizador de Base de Datos</h2>";
echo "<hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;'>";

$pasos = [
    "Añadiendo columna 'metodo_pago' a la tabla ventas" => "ALTER TABLE ventas ADD metodo_pago VARCHAR(50) DEFAULT 'Efectivo' AFTER total",
    
    "Añadiendo columna 'usuario_id' a la tabla ventas" => "ALTER TABLE ventas ADD usuario_id INT AFTER id",
    
    "Activando protección de stock (UNSIGNED)" => "ALTER TABLE productos MODIFY stock INT UNSIGNED NOT NULL DEFAULT 0",
    
    "Verificando integridad de tabla detalle_venta" => "CREATE TABLE IF NOT EXISTS detalle_venta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venta_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (venta_id) REFERENCES ventas(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    )",

    "Añadiendo columna 'imagen' para links de fotos" => "ALTER TABLE productos ADD imagen VARCHAR(255) NULL AFTER descripcion",

    "Creando tabla de categorías" => "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE
    )",
    
    "Vinculando productos con categorías" => "ALTER TABLE productos ADD categoria_id INT NULL AFTER id"
];



    foreach ($pasos as $descripcion => $sql) {
        echo "<div style='margin-bottom: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;'>";
        echo "<strong>$descripcion...</strong><br>";
        
        $success = false;
        $error_msg = "";
        
        try {
            if ($conn->query($sql)) {
                $success = true;
            } else {
                $error_msg = $conn->error;
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }

        if ($success) {
            echo "<span style='color:green; font-size: 0.9rem;'> Completado</span>";
        } else {
    
            if (strpos($error_msg, 'Duplicate column') !== false || strpos($error_msg, 'already exists') !== false || strpos($error_msg, 'Duplicate key') !== false) {
                echo "<span style='color:blue; font-size: 0.9rem;'> Ya existía (Correcto)</span>";
            } else {
                echo "<span style='color:red; font-size: 0.9rem;'> Error: " . $error_msg . "</span>";
            }
        }
        echo "</div>";
    }

echo "<hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;'>";
echo "<div style='text-align:center;'>";
echo "<p style='color:#64748b; margin-bottom: 1.5rem;'>Base de datos sincronizada correctamente.</p>";
echo "<a href='/' style='background:#4f46e5; color:white; padding:0.75rem 1.5rem; text-decoration:none; border-radius:0.5rem; font-weight:600;'>Volver al Inicio</a>";
echo "</div>";
echo "</div>";
?>
