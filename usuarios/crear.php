<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "Solo los administradores pueden crear usuarios.";
    $_SESSION['msg_type'] = "danger";
    echo "<script>window.location.href='/';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = cleanInput($_POST['nombre']);
    $usuario = cleanInput($_POST['usuario']);
    $password = cleanInput($_POST['password']);
    $rol = cleanInput($_POST['rol']);
    
    if (empty($nombre) || empty($usuario) || empty($password)) {
        $_SESSION['msg'] = "Todos los campos son obligatorios";
        $_SESSION['msg_type'] = "warning";
    } else {
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error en preparación: " . $conn->error);
            }
            
            $stmt->bind_param("ssss", $nombre, $usuario, $hash_password, $rol);
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Usuario creado con éxito";
                $_SESSION['msg_type'] = "success";
                echo "<script>window.location.href='/usuarios/listar.php';</script>";
                exit;
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            if ($conn->errno == 1062) {
                $_SESSION['msg'] = "El nombre de usuario (Login) ya existe. Elige otro.";
            } else {
                $_SESSION['msg'] = "Error guardando usuario: " . $e->getMessage();
            }
            $_SESSION['msg_type'] = "danger";
        }
    }
}
?>

<div class="card fade-in" style="max-width: 600px; margin: auto;">
    <div class="flex justify-between items-center mb-4">
        <h2>Crear Nuevo Usuario</h2>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nombre Completo del Empleado</label>
            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
        </div>
        
        <div class="form-group flex gap-2" style="align-items: end;">
            <div style="flex:1;">
                <label>Nombre de Usuario (Para Login)</label>
                <input type="text" name="usuario" class="form-control" required placeholder="Ej: jperez">
                <small style="color:var(--text-light)">Debe ser único, sin espacios.</small>
            </div>
            <div style="flex:1;">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="Al menos 6 caracteres">
            </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
            <label>Nivel de Acceso (Rol)</label>
            <div style="background:var(--bg-light); padding:1rem; border-radius:0.5rem; border:1px solid var(--border-color);">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="radio" name="rol" value="cajero" checked style="width: auto;">
                    <div>
                        <strong>Cajero (Vendedor)</strong>
                        <p style="margin:0; font-size:0.85em; color:var(--text-light);">Solo puede hacer ventas y ver su propio historial.</p>
                    </div>
                </label>
                <hr style="margin: 0.8rem 0; border: 0; border-top: 1px solid var(--border-color);">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="radio" name="rol" value="admin" style="width: auto;">
                    <div>
                        <strong>Administrador (Dueño)</strong>
                        <p style="margin:0; font-size:0.85em; color:var(--text-light);">Acceso total a inventario, usuarios y finanzas.</p>
                    </div>
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary mt-4" style="width: 100%;">Crear Cuenta</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
