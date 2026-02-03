<?php
include("../config/conexion.php");
include("../includes/header.php");

// Cargar productos para JS
$sql = "SELECT id, nombre, precio, stock, codigo_barras FROM productos WHERE activo = 1 AND stock > 0";
$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<div class="pos-layout">
    <!-- Panel Izquierdo: Selección -->
    <div style="display: flex; flex-direction: column; gap: 1rem; height: 100%;">
        <div class="card" style="margin-bottom: 0;">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Buscar producto por nombre o código..." autofocus>
        </div>
        
        <div class="product-grid" id="productGrid">
            <!-- Renderizado por JS -->
        </div>
    </div>

    <!-- Panel Derecho: Carrito -->
    <div class="cart-panel">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 1.25rem;">Venta Actual</h2>
            <div style="font-size: 0.8rem; color: var(--text-light);"><?= date('d M Y') ?></div>
        </div>

        <div class="cart-items" id="cartItems">
            <!-- Items del carrito -->
            <div class="text-center" style="margin-top: 2rem; color: var(--text-light);">
                El carrito está vacío
            </div>
        </div>

        <div class="cart-summary">
            <div class="flex justify-between mb-4">
                <span style="font-size: 1.25rem; font-weight: 600;">Total</span>
                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);" id="totalDisplay">$0.00</span>
            </div>
            
            <button onclick="procesarVenta()" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                <i class="fa-solid fa-check"></i> Cobrar y Finalizar
            </button>
            <button onclick="limpiarCarrito()" class="btn btn-danger mt-4" style="width: 100%;">
                <i class="fa-solid fa-trash"></i> Cancelar Venta
            </button>
        </div>
    </div>
</div>

<script>
    // Datos desde PHP
    const products = <?= json_encode($products) ?>;
    let cart = [];

    const searchInput = document.getElementById('searchInput');
    const productGrid = document.getElementById('productGrid');
    const cartItems = document.getElementById('cartItems');
    const totalDisplay = document.getElementById('totalDisplay');

    // Inicializar
    renderProducts(products);

    // Búsqueda
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = products.filter(p => 
            p.nombre.toLowerCase().includes(term) || 
            (p.codigo_barras && p.codigo_barras.includes(term))
        );
        renderProducts(filtered);
    });

    function renderProducts(list) {
        productGrid.innerHTML = list.map(p => `
            <div class="pos-product-card" onclick="addToCart(${p.id})">
                <div style="font-weight: 600; margin-bottom: 0.5rem;">${p.nombre}</div>
                <div style="color: var(--primary); font-weight: 700;">$${parseFloat(p.precio).toFixed(2)}</div>
                <small style="color: var(--text-light);">Stock: ${p.stock}</small>
            </div>
        `).join('');
    }

    function addToCart(id) {
        const product = products.find(p => p.id == id);
        
        // Verificar si ya está en carrito
        const existing = cart.find(item => item.id == id);
        
        if (existing) {
            if (existing.cantidad < product.stock) {
                existing.cantidad++;
            } else {
                alert("No hay más stock disponible de este producto");
            }
        } else {
            cart.push({ ...product, cantidad: 1 });
        }
        updateCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id != id);
        updateCart();
    }

    function updateCart() {
        if (cart.length === 0) {
            cartItems.innerHTML = '<div class="text-center" style="margin-top: 2rem; color: var(--text-light);">El carrito está vacío</div>';
            totalDisplay.innerText = "$0.00";
            return;
        }

        let total = 0;
        cartItems.innerHTML = cart.map(item => {
            const subtotal = item.cantidad * item.precio;
            total += subtotal;
            return `
                <div class="flex justify-between items-center mb-4" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem;">
                    <div>
                        <div style="font-weight: 600;">${item.nombre}</div>
                        <div style="font-size: 0.85rem; color: var(--text-light);">
                            ${item.cantidad} x $${parseFloat(item.precio).toFixed(2)}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600;">$${subtotal.toFixed(2)}</div>
                        <button onclick="removeFromCart(${item.id})" style="color: var(--danger); background: none; border: none; cursor: pointer; font-size: 0.8rem;">Eliminar</button>
                    </div>
                </div>
            `;
        }).join('');

        totalDisplay.innerText = "$" + total.toFixed(2);
    }

    function limpiarCarrito() {
        if(confirm('¿Borrar toda la venta actual?')) {
            cart = [];
            updateCart();
        }
    }

    function procesarVenta() {
        if (cart.length === 0) {
            alert("El carrito está vacío");
            return;
        }

        if (!confirm('¿Procesar venta por ' + totalDisplay.innerText + '?')) return;

        // Enviar a PHP
        fetch('procesar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Venta realizada con éxito!');
                window.location.href = '/'; // Volver al inicio o ticket
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión');
        });
    }
</script>

<?php include("../includes/footer.php"); ?>
