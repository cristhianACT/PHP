<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "Acceso denegado.";
    $_SESSION['msg_type'] = "danger";
    echo "<script>window.location.href='/';</script>";
    exit;
}

$errors = [];
$nombre = "";
$codigo = "";
$precio = "";
$stock = "";
$descripcion = "";
$imagen = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $codigo = cleanInput($_POST['codigo']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $descripcion = cleanInput($_POST['descripcion']);
    $imagen = cleanInput($_POST['imagen']);

    if (empty($nombre) || $precio <= 0) {
        $errors[] = "Nombre y Precio son obligatorios.";
    }

    if (empty($errors)) {
        $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
        $stmt = $conn->prepare("INSERT INTO productos (nombre, codigo_barras, precio, stock, descripcion, imagen, categoria_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
             die("Error en la Base de Datos: " . $conn->error . ". ¿Olvidaste ejecutar actualizar_db.php?");
        }
        
        $stmt->bind_param("ssdissi", $nombre, $codigo, $precio, $stock, $descripcion, $imagen, $categoria_id);
        
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

        <div class="form-group">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-control">
                <option value="">-- Sin Categoría --</option>
                <?php
                $resCat = $conn->query("SELECT * FROM categorias ORDER BY nombre");
                while($c = $resCat->fetch_assoc()):
                ?>
                <option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="flex gap-2">
            <div class="form-group" style="flex:1">
                <label class="form-label">Código (Barras)</label>
                <input type="text" name="codigo" class="form-control" value="<?= $codigo ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">Stock Inicial</label>
                <input type="number" name="stock" class="form-control" value="<?= $stock ?>" min="0" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Precio de Venta (S/) *</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $precio ?>" min="0.01" required>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= $descripcion ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Link de Imagen (URL)</label>
            <input type="url" name="imagen" class="form-control" value="<?= $imagen ?>" placeholder="https://ejemplo.com/imagen.jpg">
            <small style="color:var(--text-light)">Pega el link directo de la imagen (Google, Pinterest, etc.)</small>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">Guardar Producto</button>
        <a href="/productos/listar.php" class="btn btn-secondary mt-4" style="width:100%">Cancelar</a>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
