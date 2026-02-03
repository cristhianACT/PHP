<?php
include("../config/conexion.php");
session_start();

if (!isset($_GET['id'])) {
    die("ID de venta requerido");
}

$id = intval($_GET['id']);

// Datos Venta
$venta = $conn->query("SELECT * FROM ventas WHERE id = $id")->fetch_assoc();
// Datos Detalle
$detalles = $conn->query("SELECT d.*, p.nombre FROM detalle_venta d JOIN productos p ON d.producto_id = p.id WHERE d.venta_id = $id");

// Empresa info (Hardcoded por ahora)
$empresa = [
    "nombre" => "LUIS DC STORE",
    "direccion" => "Av. Principal 123, Centro",
    "telefono" => "555-1234",
    "email" => "contacto@luisdc.com"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= $id ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #555; display: flex; justify-content: center; padding: 20px; }
        .ticket { background: white; width: 300px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; font-size: 12px; }
        th { text-align: left; }
        td { text-align: right; }
        td:first-child { text-align: left; }
        .btn-print { display: block; width: 100%; padding: 10px; background: #333; color: white; text-align: center; text-decoration: none; margin-top: 20px; font-family: sans-serif; cursor: pointer; border: none; }
        
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .ticket { width: 100%; box-shadow: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
    <!-- html2pdf si se requiere guardar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

    <div class="ticket" id="invoice">
        <div class="center">
            <h3><?= $empresa['nombre'] ?></h3>
            <p><?= $empresa['direccion'] ?></p>
            <p>Tel: <?= $empresa['telefono'] ?></p>
        </div>
        
        <div class="divider"></div>
        
        <p><strong>Ticket Nº:</strong> <?= str_pad($venta['id'], 6, "0", STR_PAD_LEFT) ?></p>
        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
        
        <div class="divider"></div>
        
        <table>
            <thead>
                <tr>
                    <th style="width:50%">Prod</th>
                    <th>Cant</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $detalles->fetch_assoc()): ?>
                <tr>
                    <td><?= substr($item['nombre'], 0, 15) ?></td>
                    <td><?= $item['cantidad'] ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="divider"></div>
        
        <div style="text-align: right;">
            <h3>TOTAL: $<?= number_format($venta['total'], 2) ?></h3>
        </div>
        
        <div class="center" style="margin-top: 20px;">
            <p>¡Gracias por su compra!</p>
            <small>Conserve este ticket</small>
        </div>
    </div>
    
    <div style="position: fixed; top: 20px; right: 20px; display: flex; flex-direction: column; gap: 10px;">
        <button onclick="window.print()" class="btn-print" style="margin: 0; background: #4f46e5; border-radius: 5px;">Imprimir</button>
        <button onclick="downloadPDF()" class="btn-print" style="margin: 0; background: #ec4899; border-radius: 5px;">Descargar PDF</button>
        <a href="/" class="btn-print" style="margin: 0; background: #64748b; border-radius: 5px;">Volver</a>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice');
            const opt = {
                margin:       10,
                filename:     'Ticket_<?= $id ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a6', orientation: 'portrait' } // Formato ticket
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
