<?php
include("../config/conexion.php");
include("../includes/header.php");

$errors = [];
$nombre = "";
$codigo = "";
$precio = "";
$stock = "";
$descripcion = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $codigo = cleanInput($_POST['codigo']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $descripcion = cleanInput($_POST['descripcion']);

    if (empty($nombre) || $precio <= 0) {
        $errors[] = "Nombre y Precio son obligatorios.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, codigo_barras, precio, stock, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $nombre, $codigo, $precio, $stock, $descripcion);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Producto creado con éxito";
            $_SESSION['msg_type'] = "success";
            echo "<script>window.location.href='/productos/listar.php';</script>";
            exit;
        } else {
            $errors[] = "Error al guardar: " . $conn->error;
        }
    }
}
?>

<div class="login-box" style="margin: 0 auto; max-width: 600px;">
    <h2 class="mb-4">Nuevo Producto</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-4" style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem;">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nombre del Producto *</label>
            <input type="text" name="nombre" class="form-control" value="<?= $nombre ?>" required>
        </div>
        
        <div class="flex gap-2">
            <div class="form-group" style="flex:1">
                <label class="form-label">Código (Barras)</label>
                <input type="text" name="codigo" class="form-control" value="<?= $codigo ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">Stock Inicial</label>
                <input type="number" name="stock" class="form-control" value="<?= $stock ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Precio de Venta ($) *</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $precio ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= $descripcion ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">Guardar Producto</button>
        <a href="/productos/listar.php" class="btn btn-secondary mt-4" style="width:100%">Cancelar</a>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
