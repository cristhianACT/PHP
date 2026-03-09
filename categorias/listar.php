<?php
include("../config/conexion.php");
include("../includes/header.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("Acceso denegado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);
    }
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Categoría guardada correctamente";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    echo "<script>window.location.href='/categorias/listar.php';</script>";
    exit;
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    $conn->query("UPDATE productos SET categoria_id = NULL WHERE categoria_id = $id");
    if ($conn->query("DELETE FROM categorias WHERE id = $id")) {
        $_SESSION['msg'] = "Categoría eliminada";
        $_SESSION['msg_type'] = "success";
    }
    echo "<script>window.location.href='/categorias/listar.php';</script>";
    exit;
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre");
?>

<div class="flex justify-between items-center mb-6">
    <h1>Gestión de Categorías</h1>
    <button onclick="openModal()" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva Categoría</button>
</div>

<div class="table-container" style="max-width: 600px;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($cat = $categorias->fetch_assoc()): ?>
            <tr>
                <td>#<?= $cat['id'] ?></td>
                <td style="font-weight: 600;"><?= $cat['nombre'] ?></td>
                <td>
                    <button onclick="openModal(<?= $cat['id'] ?>, '<?= $cat['nombre'] ?>')" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;"><i class="fa-solid fa-pen"></i></button>
                    <a href="?eliminar=<?= $cat['id'] ?>" class="btn btn-danger" style="padding: 0.3rem 0.6rem;" onclick="return confirm('¿Eliminar categoría? Los productos vinculados quedarán sin categoría.')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="catModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:none; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:400px; width:100%; animation: slideUp 0.3s ease;">
        <h3 id="modalTitle" class="mb-4">Nueva Categoría</h3>
        <form method="POST">
            <input type="hidden" name="id" id="catId">
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" id="catNombre" class="form-control" required autofocus>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="submit" class="btn btn-primary" style="flex:1">Guardar</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex:1">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id = '', nombre = '') {
    const modal = document.getElementById('catModal');
    document.getElementById('catId').value = id;
    document.getElementById('catNombre').value = nombre;
    document.getElementById('modalTitle').innerText = id ? 'Editar Categoría' : 'Nueva Categoría';
    modal.style.display = 'flex';
    document.getElementById('catNombre').focus();
}

function closeModal() {
    document.getElementById('catModal').style.display = 'none';
}
</script>

<?php include("../includes/footer.php"); ?>
