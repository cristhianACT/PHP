<?php
include("../config/conexion.php");
include("../includes/header.php");

$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-t');

$sqlTotal = "SELECT SUM(total) as gran_total, COUNT(*) as cant_ventas FROM ventas 
             WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' AND estado='COMPLETADA'";
$resTotal = $conn->query($sqlTotal)->fetch_assoc();
$totalVendido = $resTotal['gran_total'] ?: 0;
$totalTransacciones = $resTotal['cant_ventas'] ?: 0;

$sqlTop = "SELECT p.nombre, SUM(d.cantidad) as total_vendidos 
           FROM detalle_venta d 
           JOIN productos p ON d.producto_id = p.id 
           JOIN ventas v ON d.venta_id = v.id
           WHERE DATE(v.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' AND v.estado='COMPLETADA'
           GROUP BY p.id 
           ORDER BY total_vendidos DESC LIMIT 5";
$resTop = $conn->query($sqlTop);

$sqlDiario = "SELECT DATE(fecha) as dia, SUM(total) as venta_dia 
              FROM ventas 
              WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' AND estado='COMPLETADA'
              GROUP BY DATE(fecha) ORDER BY dia ASC";
$resDiario = $conn->query($sqlDiario);
?>

<div class="flex justify-between items-center mb-4 fade-in reports-header-desktop">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800;">Reportes y Estadísticas</h1>
        <p class="text-light">Analiza el rendimiento de tu negocio</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <form class="flex items-center" style="background: white; padding: 0.5rem 1rem; border-radius: 99px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); gap: 1rem;">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar" style="color: var(--primary);"></i>
                <input type="date" name="inicio" value="<?= $fecha_inicio ?>" class="form-control" style="border:none; background:transparent; max-width:130px; padding: 0; font-family: var(--font-main);">
            </div>
            
            <span style="color:var(--text-light); font-weight: 500;">hasta</span>
            
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar" style="color: var(--primary);"></i>
                <input type="date" name="fin" value="<?= $fecha_fin ?>" class="form-control" style="border:none; background:transparent; max-width:130px; padding: 0; font-family: var(--font-main);">
            </div>
            
            <div style="width: 1px; height: 20px; background: var(--border); margin: 0 0.5rem;"></div>

            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.9rem;">
                Filtrar
            </button>
        </form>
        
        <a href="exportar_excel.php?inicio=<?= $fecha_inicio ?>&fin=<?= $fecha_fin ?>" target="_blank" class="btn btn-success" style="border-radius: 99px; padding-left: 1.2rem; padding-right: 1.2rem;">
            <i class="fa-solid fa-file-excel"></i> Exportar
        </a>
    </div>
</div>

<div class="reports-header-mobile mb-4 fade-in">
    <div class="mb-4">
        <h1 style="font-size: 1.5rem; font-weight: 800;">Reportes y Estadísticas</h1>
        <p class="text-light" style="font-size: 0.9rem;">Analiza el rendimiento de tu negocio</p>
    </div>
    
    <form class="flex-col gap-2 mb-2" style="background: white; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <div class="flex items-center gap-2">
            <i class="fa-regular fa-calendar" style="color: var(--primary);"></i>
            <input type="date" name="inicio" value="<?= $fecha_inicio ?>" class="form-control" style="flex: 1; font-family: var(--font-main);">
        </div>
        
        <div class="flex items-center gap-2">
            <i class="fa-regular fa-calendar" style="color: var(--primary);"></i>
            <input type="date" name="fin" value="<?= $fecha_fin ?>" class="form-control" style="flex: 1; font-family: var(--font-main);">
        </div>

        <button type="submit" class="btn btn-primary w-full">
            <i class="fa-solid fa-filter"></i> Filtrar
        </button>
    </form>
    
    <a href="exportar_excel.php?inicio=<?= $fecha_inicio ?>&fin=<?= $fecha_fin ?>" target="_blank" class="btn btn-success w-full">
        <i class="fa-solid fa-file-excel"></i> Exportar a Excel
    </a>
</div>

<div class="grid-dashboard fade-in">
    <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: white;">
        <h3 style="color: rgba(255,255,255,0.8);">Ventas del Período</h3>
        <div class="value" style="color: white;"><?= formatMoney($totalVendido) ?></div>
        <p style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
            <i class="fa-solid fa-receipt"></i> <?= $totalTransacciones ?> ventas realizadas
        </p>
    </div>
    
    <div class="card">
        <h3>Ticket Promedio</h3>
        <div class="value" style="color: var(--secondary);">
            <?= $totalTransacciones > 0 ? formatMoney($totalVendido / $totalTransacciones) : '$0.00' ?>
        </div>
        <p class="text-light" style="margin-top: 0.5rem; font-size: 0.8rem;">Promedio de compra por cliente</p>
    </div>
</div>

<div class="pos-layout" style="height: auto; grid-template-columns: 1fr 1fr; margin-bottom: 3rem;">
    <div class="card">
        <h3><i class="fa-solid fa-calendar-day"></i> Venta Diaria</h3>
        <div class="table-container" style="box-shadow: none; border: none; max-height: 300px; overflow-y: auto;">
            <table>
                <thead>
                    <tr><th>Fecha</th><th style="text-align: right;">Total</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $resDiario->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['dia'])) ?></td>
                        <td style="text-align: right; font-weight: 700; color: var(--success);"><?= formatMoney($row['venta_dia']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-crown"></i> Productos Más Vendidos</h3>
        <div class="table-container" style="box-shadow: none; border: none;">
            <table>
                <thead>
                    <tr><th>Producto</th><th style="text-align: right;">Cantidad</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $resTop->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= $row['nombre'] ?></td>
                        <td style="text-align: right;"><span class="badge badge-purple"><?= $row['total_vendidos'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
