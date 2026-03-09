<?php

define('DB_SERVER', 'localhost');
define('DB_USER', 'federico_admin');
define('DB_PASS', 'cdT-QUgYLShv_6aa');
define('DB_NAME', 'federico_tienda');

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Error de conexion a la base de datos de producci贸n']));
    }

    die("<h1>Error de Conexion</h1><p>No se pudo conectar a la base de datos del servidor.</p><p>Verifica que el usuario y la contraseña en config/conexion.php coincidan con los de tu cPanel.</p>");
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
