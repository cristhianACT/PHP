<?php
include("../config/conexion.php");
include("../includes/header.php");

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $sql = "UPDATE productos SET activo = 0 WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['msg'] = "Producto eliminado correctamente.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error al eliminar: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    echo "<script>window.location.href='/productos/listar.php';</script>";
    exit;
}

$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY nombre";
$result = $conn->query($sql);
?>

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <h1>Inventario de Productos</h1>
    <a href="/productos/crear.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Producto</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
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
                    <a href="/productos/listar.php?eliminar=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 0.4rem 0.6rem;" onclick="return confirm('¿Eliminar este producto?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include("../includes/footer.php"); ?>
