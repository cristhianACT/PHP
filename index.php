<?php
include("config/conexion.php");
include("includes/header.php");

$filtro_usuario = "";

$sqlVentas = "SELECT SUM(total) as total_hoy FROM ventas WHERE DATE(fecha) = CURDATE() AND estado = 'COMPLETADA'" . $filtro_usuario;
$resultVentas = $conn->query($sqlVentas);
$totalHoy = 0;
if ($resultVentas && $row = $resultVentas->fetch_assoc()) {
    $totalHoy = $row['total_hoy'] ?: 0;
}

$sqlProd = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
$resultProd = $conn->query($sqlProd);
$totalProd = 0;
if ($resultProd && $row = $resultProd->fetch_assoc()) {
    $totalProd = $row['total'];
}

$sqlCountVentas = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha) = CURDATE()" . $filtro_usuario;
$resultCount = $conn->query($sqlCountVentas);
$countVentas = 0;
if ($resultCount && $row = $resultCount->fetch_assoc()) {
    $countVentas = $row['total'];
}
?>

<div class="header-section mb-4">
    <h1 style="font-size: 2rem; font-weight: 800; color: var(--text);">Panel de Control</h1>
    <p style="color: var(--text-light);">Bienvenido al sistema de gestión.</p>
</div>

<?php
// Ventas del Mes
$sqlMes = "SELECT SUM(total) as total_mes FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado = 'COMPLETADA'" . $filtro_usuario;
$resultMes = $conn->query($sqlMes);
$totalMes = 0;
if ($resultMes && $row = $resultMes->fetch_assoc()) {
    $totalMes = $row['total_mes'] ?: 0;
}

// Productos con Bajo Stock (Menos de 6 unidades)
$sqlLowStock = "SELECT nombre, stock FROM productos WHERE stock <= 5 AND activo = 1 ORDER BY stock ASC LIMIT 3";
$resultLowStock = $conn->query($sqlLowStock);
$lowStockItems = [];
while($row = $resultLowStock->fetch_assoc()) {
    $lowStockItems[] = $row;
}
?>

<div class="grid-dashboard">
    <div class="card">
        <h3>Ventas de Hoy</h3>
        <div class="value" style="color: var(--success);"><?= formatMoney($totalHoy) ?></div>
        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
            <?= $countVentas ?> transacciones
        </p>
    </div>

    <div class="card">
        <h3>Ventas del Mes</h3>
        <div class="value" style="color: var(--secondary);"><?= formatMoney($totalMes) ?></div>
        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
            Acumulado actual
        </p>
    </div>

    <div class="card <?= count($lowStockItems) > 0 ? 'border-danger' : '' ?>">
        <h3>Alertas de Stock</h3>
        <?php if(count($lowStockItems) > 0): ?>
            <div style="margin-top: 0.5rem;">
                <?php foreach($lowStockItems as $item): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem; color: var(--danger); font-weight: 600;">
                        <span><?= $item['nombre'] ?></span>
                        <span><?= $item['stock'] ?> u.</span>
                    </div>
                <?php endforeach; ?>
                <a href="/productos/listar.php" style="font-size: 0.75rem; color: var(--primary); text-decoration: none;">Ver todo →</a>
            </div>
        <?php else: ?>
            <div class="value" style="color: var(--primary); font-size: 1.5rem;">OK</div>
            <p style="font-size: 0.8rem; color: var(--text-light);">Todo en orden</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Accesos Rápidos</h3>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
            <a href="/ventas/nueva.php" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.5rem;"><i class="fa-solid fa-cash-register"></i> Ventas</a>
            <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="/productos/listar.php" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.5rem;"><i class="fa-solid fa-box"></i> Stock</a>
            <a href="/actualizar_db.php" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.5rem;"><i class="fa-solid fa-database"></i> DB</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .border-danger { border-left: 4px solid var(--danger) !important; }
</style>

<div class="card">
    <h3>Últimas 5 Ventas</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Verificar si existe la columna usuario_id para evitar errores
                $checkColumn = $conn->query("SHOW COLUMNS FROM ventas LIKE 'usuario_id'");
                $hasUsuarioId = ($checkColumn && $checkColumn->num_rows > 0);
                
                if ($hasUsuarioId) {
                    $sqlUltimas = "SELECT v.*, u.nombre as cajero_nombre FROM ventas v LEFT JOIN usuarios u ON v.usuario_id = u.id WHERE 1=1" . $filtro_usuario . " ORDER BY v.fecha DESC LIMIT 5";
                } else {
                    $sqlUltimas = "SELECT * FROM ventas WHERE 1=1" . $filtro_usuario . " ORDER BY fecha DESC LIMIT 5";
                }

                $resultUltimas = $conn->query($sqlUltimas);
                if (!$resultUltimas) {
                    echo "<tr><td colspan='6' class='text-center text-danger'>Error en consulta: " . $conn->error . "</td></tr>";
                } else if($resultUltimas->num_rows > 0):
                    while($venta = $resultUltimas->fetch_assoc()):
                        $fecha = new DateTime($venta['fecha']);
                        $venta_id = $venta['id'];
                        
                        // Obtener detalles de esta venta
                        $sqlDetalles = "SELECT d.*, p.nombre FROM detalle_venta d JOIN productos p ON d.producto_id = p.id WHERE d.venta_id = $venta_id";
                        $resDetalles = $conn->query($sqlDetalles);
                ?>
                <tr style="cursor: pointer;" onclick="toggleDetalle(<?= $venta_id ?>)">
                    <td style="color: var(--primary);"><i class="fa-solid fa-chevron-down" id="icon-<?= $venta_id ?>"></i></td>
                    <td style="font-weight: 700;">#<?= $venta_id ?></td>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td style="font-weight: 700; color: var(--success);"><?= formatMoney($venta['total']) ?></td>
                    <td>
                        <span class="badge <?= $venta['estado'] == 'COMPLETADA' ? 'badge-success' : 'badge-danger' ?>">
                            <?= $venta['estado'] ?>
                        </span>
                        <br><small style="color: var(--text-light); font-size: 0.7rem;">Cajero: <?= $venta['cajero_nombre'] ?? 'Sistema' ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="/ventas/ticket.php?id=<?= $venta_id ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"><i class="fa-solid fa-print"></i></a>
                            <?php if($venta['estado'] == 'COMPLETADA'): ?>
                                <a href="/ventas/cancelar.php?id=<?= $venta_id ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="event.stopPropagation(); return confirm('¿Seguro que desea cancelar esta venta? Esto devolverá el stock.')">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <!-- Fila de Detalle (Oculta por defecto) -->
                <tr id="detalle-<?= $venta_id ?>" style="display: none; background: #f8fafc;">
                    <td colspan="6" style="padding: 1.5rem;">
                        <div style="background: white; border-radius: 0.5rem; border: 1px solid var(--border); padding: 1rem; box-shadow: inset var(--shadow-sm);">
                            <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-light); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-list-ul"></i> Productos en el ticket
                            </h4>
                            <table style="width: 100%; font-size: 0.9rem;">
                                <thead style="background: none;">
                                    <tr>
                                        <th style="padding: 0.5rem; text-transform: none; font-size: 0.8rem;">Producto</th>
                                        <th style="padding: 0.5rem; text-transform: none; font-size: 0.8rem; text-align: center;">Cant.</th>
                                        <th style="padding: 0.5rem; text-transform: none; font-size: 0.8rem; text-align: right;">Precio</th>
                                        <th style="padding: 0.5rem; text-transform: none; font-size: 0.8rem; text-align: right;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($item = $resDetalles->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding: 0.5rem;"><?= $item['nombre'] ?></td>
                                        <td style="padding: 0.5rem; text-align: center;"><span class="badge badge-purple"><?= $item['cantidad'] ?></span></td>
                                        <td style="padding: 0.5rem; text-align: right;"><?= formatMoney($item['precio_unitario']) ?></td>
                                        <td style="padding: 0.5rem; text-align: right; font-weight: 700;"><?= formatMoney($item['subtotal']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border); text-align: right;">
                                <span style="color: var(--text-light); font-size: 0.85rem;">Método: <strong><?= $venta['metodo_pago'] ?></strong></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr><td colspan="6" class="text-center">No hay ventas registradas</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleDetalle(id) {
    const row = document.getElementById('detalle-' + id);
    const icon = document.getElementById('icon-' + id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
        icon.style.transform = 'rotate(180deg)';
        icon.style.color = 'var(--secondary)';
    } else {
        row.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
        icon.style.color = 'var(--primary)';
    }
}
</script>

<?php include("includes/footer.php"); ?>
