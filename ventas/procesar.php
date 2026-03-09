<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => "Error Fatal en Servidor: " . $error['message'] . " en " . basename($error['file']) . ":" . $error['line']
        ]);
        exit;
    }
});

include("../config/conexion.php");
session_start();

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inv谩lidos']);
    exit;
}

$items = $data['items'];
$totalVenta = 0;

foreach ($items as $item) {
    $totalVenta += ($item['precio'] * $item['cantidad']);
}

$conn->begin_transaction();

try {
    $metodo = isset($data['metodo_pago']) ? $conn->real_escape_string($data['metodo_pago']) : 'Efectivo';
    $usuarioId = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;
    
    $sqlVenta = "INSERT INTO ventas (total, metodo_pago, usuario_id, estado, fecha) VALUES (?, ?, ?, 'COMPLETADA', NOW())";
    $stmt = $conn->prepare($sqlVenta);
    
    if (!$stmt) {
        $sqlVenta = "INSERT INTO ventas (total, estado, fecha) VALUES (?, 'COMPLETADA', NOW())";
        $stmt = $conn->prepare($sqlVenta);
        $stmt->bind_param("d", $totalVenta);
    } else {
        $stmt->bind_param("dsi", $totalVenta, $metodo, $usuarioId);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar venta: " . $stmt->error);
    }
    
    $ventaId = $conn->insert_id;

    $sqlDetalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDetalle = $conn->prepare($sqlDetalle);
    if (!$stmtDetalle) throw new Exception("Error en preparaci贸n de detalle: " . $conn->error);

    $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
    $stmtStock = $conn->prepare($sqlStock);
    if (!$stmtStock) throw new Exception("Error en preparaci贸n de stock: " . $conn->error);

    foreach ($items as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        
        $stmtDetalle->bind_param("iiidd", $ventaId, $item['id'], $item['cantidad'], $item['precio'], $subtotal);
        if (!$stmtDetalle->execute()) {
            throw new Exception("Error al insertar detalle para: " . $item['nombre'] . " (" . $stmtDetalle->error . ")");
        }

        $stmtStock->bind_param("iii", $item['cantidad'], $item['id'], $item['cantidad']);
        $stmtStock->execute();
        
        if ($stmtStock->affected_rows === 0) {
            throw new Exception("Stock insuficiente para: " . $item['nombre']);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'id' => $ventaId]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
