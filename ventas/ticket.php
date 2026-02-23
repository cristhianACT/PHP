<?php
include("../config/conexion.php");
session_start();

if (!isset($_GET['id'])) {
    die("ID de venta requerido");
}

$id = intval($_GET['id']);

$venta = $conn->query("SELECT * FROM ventas WHERE id = $id")->fetch_assoc();
$detalles = $conn->query("SELECT d.*, p.nombre FROM detalle_venta d JOIN productos p ON d.producto_id = p.id WHERE d.venta_id = $id");

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
        :root {
            --primary: #4f46e5;
            --secondary: #ec4899;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body { 
            font-family: 'Courier New', Courier, monospace; 
            background: #e2e8f0; 
            display: flex; 
            justify-content: center; 
            padding: 20px;
            padding-bottom: 100px;
            margin: 0;
            min-height: 100vh;
        }

        .ticket { 
            background: white; 
            width: 100%; 
            max-width: 350px; 
            padding: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin: auto;
            position: relative;
        }

        .center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; font-size: 13px; border-collapse: collapse; }
        th { text-align: left; padding: 5px 0; }
        td { text-align: right; padding: 5px 0; }
        td:first-child { text-align: left; }
        
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 100;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }

        .btn:active { transform: scale(0.98); }
        .btn-print { background: var(--primary); }
        .btn-pdf { background: var(--secondary); }
        .btn-back { background: var(--gray); }

        .btn svg { margin-right: 8px; width: 16px; height: 16px; }

        @media print {
            body { background: white; margin: 0; padding: 0; display: block; }
            .ticket { width: 100%; max-width: none; box-shadow: none; padding: 0; margin: 0; }
            .action-buttons { display: none !important; }
            @page { margin: 0; }
        }

        @media (max-width: 768px) {
            body { padding: 10px; padding-bottom: 90px; align-items: flex-start; }
            
            .ticket { 
                margin-top: 10px;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            }

            .action-buttons {
                top: auto;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                flex-direction: row;
                padding: 15px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
                border-top: 1px solid #e2e8f0;
                justify-content: center;
            }

            .btn {
                flex: 1;
                padding: 12px;
                font-size: 0.9rem;
            }
        }
    </style>
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
        
        <p><strong>Nº Ticket:</strong> <?= str_pad($venta['id'], 6, "0", STR_PAD_LEFT) ?></p>
        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
        <p><strong>Pago:</strong> <?= $venta['metodo_pago'] ?? 'Efectivo' ?></p>
        
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
    
    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir
        </button>
        <button onclick="downloadPDF()" class="btn btn-pdf">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            PDF
        </button>
        <a href="/" class="btn btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice');
            const opt = {
                margin:       5,
                filename:     'Ticket_<?= $id ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: [80, 200], orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
