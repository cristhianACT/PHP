<?php
include("../config/conexion.php");
include("../includes/header.php");

$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($_GET['desde'])) {
    $where .= " AND DATE(fecha) >= ?";
    $params[] = $_GET['desde'];
    $types .= "s";
}
if (!empty($_GET['hasta'])) {
    $where .= " AND DATE(fecha) <= ?";
    $params[] = $_GET['hasta'];
    $types .= "s";
}
if (!empty($_GET['metodo'])) {
    $where .= " AND metodo_pago = ?";
    $params[] = $_GET['metodo'];
    $types .= "s";
}
if (!empty($_GET['estado'])) {
    $where .= " AND estado = ?";
    $params[] = $_GET['estado'];
    $types .= "s";
}

$sql = "SELECT v.*, u.nombre as cajero_nombre FROM ventas v 
        LEFT JOIN usuarios u ON v.usuario_id = u.id 
        $where ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="flex justify-between items-center mb-6">
    <h1>Historial de Ventas</h1>
    <a href="/reportes/exportar_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary">
        <i class="fa-solid fa-file-excel"></i> Exportar a Excel
    </a>
</div>

<!-- Filtros -->
<div class="card mb-6">
    <form method="GET" class="flex gap-4 flex-wrap items-end">
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size: 0.8rem;">Desde</label>
            <input type="date" name="desde" class="form-control" value="<?= $_GET['desde'] ?? '' ?>">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size: 0.8rem;">Hasta</label>
            <input type="date" name="hasta" class="form-control" value="<?= $_GET['hasta'] ?? '' ?>">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size: 0.8rem;">Método</label>
            <select name="metodo" class="form-control">
                <option value="">Todos</option>
                <option value="Efectivo" <?= ($_GET['metodo'] ?? '') == 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                <option value="Tarjeta" <?= ($_GET['metodo'] ?? '') == 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size: 0.8rem;">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="COMPLETADA" <?= ($_GET['estado'] ?? '') == 'COMPLETADA' ? 'selected' : '' ?>>Completada</option>
                <option value="CANCELADA" <?= ($_GET['estado'] ?? '') == 'CANCELADA' ? 'selected' : '' ?>>Cancelada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="/ventas/historial.php" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Limpiar</a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Fecha/Hora</th>
                <th>Cajero</th>
                <th>Método</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($venta = $result->fetch_assoc()): 
                $v_id = $venta['id'];
                $fecha = new DateTime($venta['fecha']);
                
                $sqlD = "SELECT d.*, p.nombre FROM detalle_venta d JOIN productos p ON d.producto_id = p.id WHERE d.venta_id = $v_id";
                $resD = $conn->query($sqlD);
            ?>
            <tr onclick="toggleDetalle(<?= $v_id ?>)" style="cursor: pointer;">
                <td><i class="fa-solid fa-chevron-down" id="icon-<?= $v_id ?>" style="color:var(--primary)"></i></td>
                <td style="font-weight:700;">#<?= $v_id ?></td>
                <td><?= $fecha->format('d/m/Y H:i') ?></td>
                <td><?= $venta['cajero_nombre'] ?? 'Sistema' ?></td>
                <td><span class="badge badge-info"><?= $venta['metodo_pago'] ?></span></td>
                <td style="font-weight:700; color:var(--success)"><?= formatMoney($venta['total']) ?></td>
                <td>
                    <span class="badge <?= $venta['estado'] == 'COMPLETADA' ? 'badge-success' : 'badge-danger' ?>">
                        <?= $venta['estado'] ?>
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="/ventas/ticket.php?id=<?= $v_id ?>" class="btn btn-secondary" style="padding: 0.3rem 0.5rem;"><i class="fa-solid fa-print"></i></a>
                        <?php if($venta['estado'] == 'COMPLETADA'): ?>
                        <a href="/ventas/cancelar.php?id=<?= $v_id ?>" class="btn btn-danger" style="padding: 0.3rem 0.5rem;" onclick="event.stopPropagation(); return confirm('¿Cancelar esta venta?')"><i class="fa-solid fa-ban"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr id="detalle-<?= $v_id ?>" style="display: none; background: #fafafa;">
                <td colspan="8" style="padding: 1rem;">
                    <div style="background:white; padding:1.5rem; border:1px solid var(--border); border-radius:1rem;">
                        <h5 style="margin-bottom:1rem; color:var(--text-light); text-transform:uppercase; font-size:0.75rem;">Detalle de la Transacción</h5>
                        <table style="width:100%; font-size: 0.85rem;">
                             <thead>
                                 <tr>
                                     <th style="background:none; border:none; padding:4px;">Producto</th>
                                     <th style="background:none; border:none; padding:4px; text-align:center;">Cant.</th>
                                     <th style="background:none; border:none; padding:4px; text-align:right;">Precio</th>
                                     <th style="background:none; border:none; padding:4px; text-align:right;">Subtotal</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php while($it = $resD->fetch_assoc()): ?>
                                 <tr>
                                     <td style="padding:4px;"><?= $it['nombre'] ?></td>
                                     <td style="padding:4px; text-align:center;"><?= $it['cantidad'] ?></td>
                                     <td style="padding:4px; text-align:right;"><?= formatMoney($it['precio_unitario']) ?></td>
                                     <td style="padding:4px; text-align:right; font-weight:600;"><?= formatMoney($it['subtotal']) ?></td>
                                 </tr>
                                 <?php endwhile; ?>
                             </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function toggleDetalle(id) {
    const row = document.getElementById('detalle-' + id);
    const icon = document.getElementById('icon-' + id);
    if(row.style.display === 'none') {
        row.style.display = 'table-row';
        icon.style.transform = 'rotate(180deg)';
    } else {
        row.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>

<?php include("../includes/footer.php"); ?>
