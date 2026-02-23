<?php
$serverName = "localhost";
$username = "root";
$password = "";
$dbname = "tienda";

$conn = new mysqli($serverName, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<h1>Error de Conexión</h1><p>No se pudo conectar a MySQL.</p><p>Error: " . $conn->connect_error . "</p>");
}

$conn->set_charset("utf8");

function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function formatMoney($amount) {
    return "S/ " . number_format($amount, 2);
}
?>
