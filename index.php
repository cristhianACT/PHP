<?php
include("config/conexion.php");
include("includes/header.php");

$sqlVentas = "SELECT SUM(total) as total_hoy FROM ventas WHERE DATE(fecha) = CURDATE() AND estado = 'COMPLETADA'";
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

$sqlCountVentas = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
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

<div class="grid-dashboard">
    <div class="card">
        <h3>Ventas de Hoy</h3>
        <div class="value" style="color: var(--success);"><?= formatMoney($totalHoy) ?></div>
        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
            <?= $countVentas ?> transacciones realizadas
        </p>
    </div>

    <div class="card">
        <h3>Productos Activos</h3>
        <div class="value" style="color: var(--primary);"><?= $totalProd ?></div>
        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
            En inventario
        </p>
    </div>

    <div class="card">
        <h3>Accesos Rápidos</h3>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem;">
            <a href="/ventas/nueva.php" class="btn btn-primary" style="font-size: 0.8rem;"><i class="fa-solid fa-cash-register"></i> Nueva Venta</a>
            <a href="/productos/listar.php" class="btn btn-secondary" style="font-size: 0.8rem;"><i class="fa-solid fa-box"></i> Inventario</a>
        </div>
    </div>
</div>

<div class="card">
    <h3>Últimas 5 Ventas</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sqlUltimas = "SELECT * FROM ventas ORDER BY fecha DESC LIMIT 5";
                $resultUltimas = $conn->query($sqlUltimas);
                if($resultUltimas && $resultUltimas->num_rows > 0):
                    while($venta = $resultUltimas->fetch_assoc()):
                        $fecha = new DateTime($venta['fecha']);
                ?>
                <tr>
                    <td>#<?= $venta['id'] ?></td>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td><?= formatMoney($venta['total']) ?></td>
                    <td>
                        <span class="badge <?= $venta['estado'] == 'COMPLETADA' ? 'badge-success' : 'badge-danger' ?>">
                            <?= $venta['estado'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if($venta['estado'] == 'COMPLETADA'): ?>
                            <a href="/ventas/cancelar.php?id=<?= $venta['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="return confirm('¿Seguro que desea cancelar esta venta? Esto devolverá el stock.')">Cancelar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr><td colspan="5" class="text-center">No hay ventas registradas</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("includes/footer.php"); ?>
