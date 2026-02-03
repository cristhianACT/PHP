<?php
include("../config/conexion.php");
session_start();

// Recibir JSON
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

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Crear Venta
    $sqlVenta = "INSERT INTO ventas (total, estado, fecha) VALUES (?, 'COMPLETADA', NOW())";
    $stmt = $conn->prepare($sqlVenta);
    $stmt->bind_param("d", $totalVenta);
    $stmt->execute();
    $ventaId = $conn->insert_id;

    // 2. Insertar Detalles y Actualizar Stock
    $sqlDetalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDetalle = $conn->prepare($sqlDetalle);

    $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
    $stmtStock = $conn->prepare($sqlStock);

    foreach ($items as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        
        // Insertar detalle
        $stmtDetalle->bind_param("iiidd", $ventaId, $item['id'], $item['cantidad'], $item['precio'], $subtotal);
        $stmtDetalle->execute();

        // Actualizar Stock
        $stmtStock->bind_param("ii", $item['cantidad'], $item['id']);
        $stmtStock->execute();
    }

    $conn->commit();
    // Return ID para redireccionar
    echo json_encode(['success' => true, 'id' => $ventaId]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
