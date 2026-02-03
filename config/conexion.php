<?php
// Configuración de la base de datos MySQL (XAMPP)
$serverName = "localhost";
$username = "root"; // Usuario por defecto de XAMPP
$password = "";     // Contraseña por defecto de XAMPP (vacía)
$dbname = "tienda";

// Crear conexión
$conn = new mysqli($serverName, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("<h1>Error de Conexión</h1><p>No se pudo conectar a MySQL.</p><p>Error: " . $conn->connect_error . "</p>");
}

// Establecer charset
$conn->set_charset("utf8");

// Función helper para limpiar datos
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Función para format dinero
function formatMoney($amount) {
    return "$" . number_format($amount, 2);
}
?>
