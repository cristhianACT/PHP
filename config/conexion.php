<?php

if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {

    define('DB_SERVER', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'tienda');
}
else {
    // Entorno de producción (CPanel)
    // ADVERTENCIA: Cambia estos datos con los que crees en tu CPanel
    define('DB_SERVER', 'localhost');
    define('DB_USER', 'usuario_cpanel');
    define('DB_PASS', 'password_segura');
    define('DB_NAME', 'base_de_datos_cpanel');
}

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        die("<h1>Error de Conexión (Local)</h1><p>Asegúrate de que MySQL esté activo en XAMPP.</p>");
    }
    else {
        die("<h1>Error de Conexión (Producción)</h1><p>Verifica las credenciales en config/conexion.php</p>");
    }
}

$conn->set_charset("utf8");

function cleanInput($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function formatMoney($amount)
{
    return "S/ " . number_format($amount, 2);
}
?>
