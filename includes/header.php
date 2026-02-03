<?php
// Recalculamos el header para incluir autenticación
session_start();

// Si no hay sesión y no estamos en login, redirigir
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['usuario_id']) && $current_page != 'login.php' && $current_page != 'crear_admin.php') {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if(isset($_SESSION['usuario_id'])): ?>
    <header>
        <div class="navbar container">
            <a href="/" class="brand">
                <i class="fa-solid fa-store"></i> TiendaPOS
            </a>
            <ul class="nav-links">
                <li><a href="/" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' ?>">Inicio</a></li>
                <li><a href="/ventas/nueva.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'ventas') !== false ? 'active' : '' ?>">Ventas</a></li>
                <li><a href="/productos/listar.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'productos') !== false ? 'active' : '' ?>">Productos</a></li>
                <li>
                    <span style="font-size: 0.9rem; margin-right: 1rem; color: var(--text-light)">
                        <?= $_SESSION['usuario_nombre'] ?? 'Usuario' ?> (<?= ucfirst($_SESSION['usuario_rol'] ?? '') ?>)
                    </span>
                    <a href="/auth/logout.php" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">
                        <i class="fa-solid fa-right-from-bracket"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="container">
        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>" style="padding:1rem; margin-bottom:1rem; border-radius:0.5rem; background: #e0e7ff; color: #3730a3;">
                <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>
