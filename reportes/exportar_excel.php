<?php
include("../config/conexion.php");

$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-t');

$filename = "Reporte_Ventas_" . $fecha_inicio . "_al_" . $fecha_fin . ".xls";

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header("Pragma: no-cache"); 
header("Expires: 0");

$sql = "SELECT v.id, v.fecha, v.total, v.metodo_pago, v.estado, p.nombre as producto, d.cantidad, d.precio_unitario, d.subtotal 
        FROM ventas v
        JOIN detalle_venta d ON v.id = d.venta_id
        JOIN productos p ON d.producto_id = p.id
        WHERE DATE(v.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ORDER BY v.fecha DESC";

$result = $conn->query($sql);
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th { background-color: #4f46e5; color: white; border: 1px solid #000000; padding: 10px; text-align: center; }
        td { border: 1px solid #cccccc; padding: 8px; vertical-align: middle; }
        .num { text-align: right; }
        .center { text-align: center; }
        .total-row { background-color: #f3f4f6; font-weight: bold; font-size: 1.1em; }
        .canceled { color: #991b1b; background-color: #fee2e2; text-decoration: line-through; }
        .completed { color: #065f46; background-color: #d1fae5; }
    </style>
</head>
<body>
    <h3>Reporte de Ventas Detallado</h3>
    <p><strong>Periodo:</strong> Del <?= $fecha_inicio ?> al <?= $fecha_fin ?></p>
    
    <table>
        <thead>
            <tr>
                <th style="background-color:#4338ca; color:#ffffff;">ID Venta</th>
                <th style="background-color:#4338ca; color:#ffffff;">Fecha y Hora</th>
                <th style="background-color:#4338ca; color:#ffffff;">Estado</th>
                <th style="background-color:#4338ca; color:#ffffff;">Pago</th>
                <th style="background-color:#4338ca; color:#ffffff;">Producto</th>
                <th style="background-color:#4338ca; color:#ffffff;">Cantidad</th>
                <th style="background-color:#4338ca; color:#ffffff;">Precio Unit.</th>
                <th style="background-color:#4338ca; color:#ffffff;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $gran_total = 0;
            while ($row = $result->fetch_assoc()): 
                if($row['estado'] == 'COMPLETADA') {
                    $gran_total += $row['subtotal'];
                    $style_estado = "background-color:#d1fae5; color:#065f46;";
                    $style_row = "";
                } else {
                    $style_estado = "background-color:#fee2e2; color:#991b1b; font-weight:bold;";
                    $style_row = "color:#999999;";
                }
            ?>
            <tr style="<?= $style_row ?>">
                <td class="center"><?= $row['id'] ?></td>
                <td class="center"><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?></td>
                <td class="center" style="<?= $style_estado ?>"><?= $row['estado'] ?></td>
                <td class="center"><?= $row['metodo_pago'] ?? 'Efectivo' ?></td>
                <td><?= mb_convert_encoding($row['producto'], 'HTML-ENTITIES', 'UTF-8') ?></td>
                <td class="center"><?= $row['cantidad'] ?></td>
                <td class="num">S/ <?= number_format($row['precio_unitario'], 2) ?></td>
                <td class="num" style="<?= $row['estado'] == 'COMPLETADA' ? 'background-color:#e0e7ff; color:#3730a3;' : '' ?>">
                    S/ <?= number_format($row['subtotal'], 2) ?>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <tr class="total-row">
                <td colspan="7" style="text-align: right;">TOTAL (Solo Ventas Completadas)</td>
                <td class="num" style="background-color:#c7d2fe; color:#312e81; border: 2px solid #312e81;">
                    S/ <?= number_format($gran_total, 2) ?>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
