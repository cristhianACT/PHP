<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "Acceso denegado. Se requieren permisos de Administrador.";
    $_SESSION['msg_type'] = "danger";
    echo "<script>window.location.href='/';</script>";
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);

    if ($id_eliminar == $_SESSION['usuario_id']) {
        $_SESSION['msg'] = "No puedes eliminar tu propia cuenta mientras estás logueado.";
        $_SESSION['msg_type'] = "warning";
    } else {
        try {
            $sql = "DELETE FROM usuarios WHERE id = $id_eliminar";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['msg'] = "Usuario eliminado correctamente.";
                $_SESSION['msg_type'] = "success";
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            if ($conn->errno == 1451) {
                $_SESSION['msg'] = "No se puede eliminar: El usuario tiene ventas registradas a su nombre.";
            } else {
                $_SESSION['msg'] = "Error al eliminar: " . $e->getMessage();
            }
            $_SESSION['msg_type'] = "danger";
        }
    }
    echo "<script>window.location.href='/usuarios/listar.php';</script>";
    exit;
}

$sql = "SELECT id, usuario, nombre, rol FROM usuarios ORDER BY rol ASC, nombre ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Gestión de Usuarios</h1>
    <a href="/usuarios/crear.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Nuevo Usuario</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario (Login)</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <div style="font-weight: 600;"><?= htmlspecialchars($row['nombre']) ?></div>
                    <?php if($row['id'] == $_SESSION['usuario_id']): ?>
                        <small style="color:var(--primary);">(Tú)</small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td>
                    <span class="badge <?= $row['rol'] == 'admin' ? 'badge-primary' : 'badge-secondary' ?>" style="text-transform: uppercase;">
                        <?= $row['rol'] ?>
                    </span>
                </td>
                <td>
                    <a href="/usuarios/editar.php?id=<?= $row['id'] ?>" class="btn btn-secondary" style="padding: 0.4rem 0.6rem;" title="Editar"><i class="fa-solid fa-pen"></i></a>
                    <?php if($row['id'] != $_SESSION['usuario_id']): ?>
                        <a href="/usuarios/listar.php?eliminar=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" onclick="return confirm('¿Seguro de eliminar este usuario?')" title="Eliminar"><i class="fa-solid fa-trash"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include("../includes/footer.php"); ?>
