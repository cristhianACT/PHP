<?php
include("../config/conexion.php");
session_start();

if (!isset($_GET['id'])) {
    header("Location: /");
    exit;
}

$id = intval($_GET['id']);

$sqlDetalles = "SELECT * FROM detalle_venta WHERE venta_id = $id";
$result = $conn->query($sqlDetalles);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $prodId = $row['producto_id'];
        $cant = $row['cantidad'];

        $conn->query("UPDATE productos SET stock = stock + $cant WHERE id = $prodId");
    }

    $conn->query("UPDATE ventas SET estado = 'CANCELADA' WHERE id = $id");

    $_SESSION['msg'] = "Venta #$id cancelada y stock restaurado.";
    $_SESSION['msg_type'] = "success";
} else {
    $_SESSION['msg'] = "Error al cancelar venta.";
    $_SESSION['msg_type'] = "danger";
}

header("Location: /");
exit;
?>
