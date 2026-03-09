<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("Acceso denegado.");
}
include("../config/conexion.php");

$sql = "ALTER TABLE ventas ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Efectivo' AFTER total";

if ($conn->query($sql) === TRUE) {
    echo "<h1> Actualizacion exitosa</h1>";
    echo "<p>Se agrego la columna 'metodo_pago' a la tabla 'ventas'.</p>";
} else {
    if (strpos($conn->error, "Duplicate column") !== false) {
        echo "<h1> Ya estaba actualizado</h1>";
        echo "<p>La columna 'metodo_pago' ya existia.</p>";
    } else {
        echo "<h1> Error</h1>";
        echo "<p>" . $conn->error . "</p>";
    }
}

echo "<br><a href='/'>Volver al Inicio</a>";
?>
