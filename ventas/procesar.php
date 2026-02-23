<?php
include("../config/conexion.php");
session_start();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
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
    
    $sqlVenta = "INSERT INTO ventas (total, metodo_pago, estado, fecha) VALUES (?, ?, 'COMPLETADA', NOW())";
    $stmt = $conn->prepare($sqlVenta);
    $stmt->bind_param("ds", $totalVenta, $metodo);
    $stmt->execute();
    $ventaId = $conn->insert_id;

    $sqlDetalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDetalle = $conn->prepare($sqlDetalle);

    $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
    $stmtStock = $conn->prepare($sqlStock);

    foreach ($items as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        
        $stmtDetalle->bind_param("iiidd", $ventaId, $item['id'], $item['cantidad'], $item['precio'], $subtotal);
        $stmtDetalle->execute();

        $stmtStock->bind_param("ii", $item['cantidad'], $item['id']);
        $stmtStock->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'id' => $ventaId]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
