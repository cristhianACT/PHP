<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "Solo los administradores pueden editar usuarios.";
    $_SESSION['msg_type'] = "danger";
    echo "<script>window.location.href='/';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM usuarios WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    $_SESSION['msg'] = "Usuario no encontrado.";
    $_SESSION['msg_type'] = "warning";
    header("Location: listar.php");
    exit;
}

$u = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = cleanInput($_POST['nombre']);
    $usuario = cleanInput($_POST['usuario']);
    $rol = cleanInput($_POST['rol']);
    $password = $_POST['password'];
    
    if (empty($nombre) || empty($usuario)) {
        $_SESSION['msg'] = "Nombre y Usuario son obligatorios";
        $_SESSION['msg_type'] = "warning";
    } else {
        try {
            if (!empty($password)) {
                
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, password = ?, rol = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $nombre, $usuario, $hash_password, $rol, $id);
            } else {
                
                $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $nombre, $usuario, $rol, $id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Usuario actualizado con éxito";
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
                $_SESSION['msg'] = "Error actualizando usuario: " . $e->getMessage();
            }
            $_SESSION['msg_type'] = "danger";
        }
    }
}
?>

<div class="card fade-in" style="max-width: 600px; margin: auto;">
    <div class="flex justify-between items-center mb-4">
        <h2>Editar Usuario</h2>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nombre Completo del Empleado</label>
            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($u['nombre']) ?>" placeholder="Ej: Juan Pérez">
        </div>
        
        <div class="form-group flex gap-2" style="align-items: end;">
            <div style="flex:1;">
                <label>Nombre de Usuario (Login)</label>
                <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($u['usuario']) ?>" placeholder="Ej: jperez">
                <small style="color:var(--text-light)">Debe ser único, sin espacios.</small>
            </div>
            <div style="flex:1;">
                <label>Nueva Contraseña <small>(Opcional)</small></label>
                <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
            </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
            <label>Nivel de Acceso (Rol)</label>
            <div style="background:var(--bg-light); padding:1rem; border-radius:0.5rem; border:1px solid var(--border-color);">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="radio" name="rol" value="cajero" <?= $u['rol'] == 'cajero' ? 'checked' : '' ?> style="width: auto;">
                    <div>
                        <strong>Cajero (Vendedor)</strong>
                        <p style="margin:0; font-size:0.85em; color:var(--text-light);">Puede hacer ventas y ver historial.</p>
                    </div>
                </label>
                <hr style="margin: 0.8rem 0; border: 0; border-top: 1px solid var(--border-color);">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="radio" name="rol" value="admin" <?= $u['rol'] == 'admin' ? 'checked' : '' ?> style="width: auto;">
                    <div>
                        <strong>Administrador (Dueño)</strong>
                        <p style="margin:0; font-size:0.85em; color:var(--text-light);">Acceso total a inventario, usuarios y reportes.</p>
                    </div>
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary mt-4" style="width: 100%;">Guardar Cambios</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
