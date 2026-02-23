<?php
include("config/conexion.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = cleanInput($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nombre'] = $row['nombre'];
            $_SESSION['usuario_rol'] = $row['rol'];
            
            header("Location: /");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body style="background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%);">
    
    <div class="login-container" style="background: transparent;">
        <div class="login-box">
            <div class="text-center mb-4">
                <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">TiendaPOS</h1>
                <p style="color: var(--text-light);">Ingresa tus credenciales</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger mb-4" style="background:#fee2e2; color:#991b1b; padding:0.75rem; border-radius:0.5rem; text-align:center;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" placeholder="Ej: admin" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Ingresar</button>
            </form>

            <div class="text-center mt-4" style="font-size: 0.85rem;">
                <p>¿Primera vez? <a href="/crear_admin.php" style="color: var(--primary);">Crear usuario admin</a></p>
            </div>
        </div>
    </div>

</body>
</html>
