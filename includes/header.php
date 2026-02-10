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
    <title>Sistema POS Premium</title>
    
    <!-- Google Fonts: Outfit para un look moderno -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Librería para PDF (html2pdf) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="fade-in">
    <?php if(isset($_SESSION['usuario_id'])): ?>
    <header>
        <div class="navbar container">
            <a href="/" class="brand">
                <i class="fa-solid fa-layer-group"></i> TiendaPOS
            </a>
            
            <button class="hamburger-btn" id="mobile-menu-btn" aria-label="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <ul class="nav-links" id="nav-links">
                <li><a href="/" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Inicio</a></li>
                <li><a href="/ventas/nueva.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'ventas') !== false ? 'active' : '' ?>"><i class="fa-solid fa-cash-register"></i> Ventas</a></li>
                <li><a href="/productos/listar.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'productos') !== false ? 'active' : '' ?>"><i class="fa-solid fa-box-open"></i> Productos</a></li>
                <li><a href="/reportes/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'reportes') !== false ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> Reportes</a></li>
                
                <li style="margin-left: 1rem;" class="logout-item">
                    <a href="/auth/logout.php" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 99px;">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                </li>
            </ul>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const navLinks = document.getElementById('nav-links');

            if(mobileBtn) {
                mobileBtn.addEventListener('click', () => {
                    navLinks.classList.toggle('mobile-active');
                    const icon = mobileBtn.querySelector('i');
                    if (navLinks.classList.contains('mobile-active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-xmark');
                    } else {
                        icon.classList.remove('fa-xmark');
                        icon.classList.add('fa-bars');
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
    
    <main class="container">
        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>" style="padding:1rem; margin-bottom:1.5rem; border-radius:0.75rem; background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; display: flex; align-items: center; gap: 0.5rem; animation: fadeIn 0.3s ease;">
                <i class="fa-solid fa-circle-info"></i> <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>
