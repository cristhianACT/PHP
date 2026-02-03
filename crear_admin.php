<?php
include("config/conexion.php");
session_start();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = cleanInput($_POST['usuario']);
    $password = $_POST['password'];
    $nombre = cleanInput($_POST['nombre']);
    $clave_maestra = $_POST['clave_maestra'];

    if ($clave_maestra === "sistema123") {
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, nombre, rol) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $usuario, $hash, $nombre);
        
        if ($stmt->execute()) {
            $msg = "<div style='color:green'>Usuario creado. <a href='/login.php'>Ir a Login</a></div>";
        } else {
            $msg = "<div style='color:red'>Error al crear usuario (¿Ya existe?).</div>";
        }

    } else {
        $msg = "<div style='color:red'>Clave maestra incorrecta.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Admin</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; min-height:100vh;">
    <div class="login-box">
        <h2>Setup Inicial: Crear Admin</h2>
        <?= $msg ?>
        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Clave Maestra (sistema123)</label>
                <input type="password" name="clave_maestra" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>
    </div>
</body>
</html>
