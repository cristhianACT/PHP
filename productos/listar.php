<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "Acceso denegado. No tienes permisos para gestionar productos.";
    $_SESSION['msg_type'] = "danger";
    echo "<script>window.location.href='/';</script>";
    exit;
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $sql = "DELETE FROM productos WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['msg'] = "Producto eliminado permanentemente de la base de datos.";
            $_SESSION['msg_type'] = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        
        if ($conn->errno == 1451) {
            $_SESSION['msg'] = "No se puede eliminar: Este producto tiene historial de ventas. Se recomienda solo desactivarlo.";
        } else {
            $_SESSION['msg'] = "Error al eliminar: " . $e->getMessage();
        }
        $_SESSION['msg_type'] = "danger";
    }
    echo "<script>window.location.href='/productos/listar.php';</script>";
    exit;
}

$sql = "SELECT * FROM productos ORDER BY nombre";
$result = $conn->query($sql);
if (!$result) {
    die("Error en la Base de Datos: " . $conn->error . ". 多Olvidaste ejecutar actualizar_db.php?");
}
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Inventario de Productos</h1>
    <a href="/productos/crear.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Producto</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if($row['imagen']): ?>
                        <img src="<?= $row['imagen'] ?>" alt="<?= $row['nombre'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-light);">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= $row['codigo_barras'] ?></td>
                <td>
                    <div style="font-weight: 600;"><?= $row['nombre'] ?></div>
                    <small style="color:var(--text-light)"><?= substr($row['descripcion'], 0, 50) ?></small>
                </td>
                <td style="font-weight: 700; color: var(--text);"><?= formatMoney($row['precio']) ?></td>
                <td>
                    <span class="badge <?= $row['stock'] < 10 ? 'badge-danger' : 'badge-success' ?>">
                        <?= $row['stock'] ?> u.
                    </span>
                </td>
                <td>
                    <a href="/productos/editar.php?id=<?= $row['id'] ?>" class="btn btn-secondary" style="padding: 0.4rem 0.6rem;"><i class="fa-solid fa-pen"></i></a>
                    <a href="/productos/listar.php?eliminar=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" onclick="return confirm('Eliminar este producto')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include("../includes/footer.php"); ?>
