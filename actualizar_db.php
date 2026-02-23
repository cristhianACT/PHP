<?php
include("config/conexion.php");

$check = $conn->query("SHOW COLUMNS FROM ventas LIKE 'metodo_pago'");

echo "<div style='font-family:sans-serif; padding: 2rem; text-align:center;'>";

if ($check->num_rows > 0) {
    echo "<h1 style='color:green'>✅ Todo en Orden</h1>";
    echo "<p>La columna 'metodo_pago' <strong>YA EXISTE</strong> en tu base de datos.</p>";
    echo "<p>Si te sale error al vender, el problema podría ser que el navegador tiene una versión vieja en caché.</p>";
    echo "<p>Prueba presionar <strong>CTRL + F5</strong> en la pantalla de ventas.</p>";
} else {
    $sql = "ALTER TABLE ventas ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Efectivo' AFTER total";
    try {
        if ($conn->query($sql) === TRUE) {
            echo "<h1 style='color:green'>✅ Columna Creada Exitosamente</h1>";
        }
    } catch (Exception $e) {
        echo "<h1 style='color:red'>❌ Error</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
}

echo "<br><br><a href='/' style='background:#4f46e5; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Volver al Sistema</a>";
echo "</div>";
?>
