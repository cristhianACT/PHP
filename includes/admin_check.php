<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    $_SESSION['msg'] = "❌ Acceso denegado. Se requieren permisos de Administrador.";
    $_SESSION['msg_type'] = "danger";
    
    // Si la petición es AJAX, responder con JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
        exit;
    }

    // Redirigir al inicio
    header("Location: /");
    exit;
}
?>
