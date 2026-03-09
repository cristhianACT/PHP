<?php
include("../config/conexion.php");
include("../includes/header.php");

$sql = "SELECT id, nombre, precio, stock, codigo_barras, imagen, categoria_id FROM productos WHERE activo = 1";
$result = $conn->query($sql);
if (!$result) {
    die("Error en la Base de Datos: " . $conn->error . ". ¿Olvidaste ejecutar actualizar_db.php?");
}
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$resCat = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categories = [];
while($c = $resCat->fetch_assoc()) {
    $categories[] = $c;
}
?>

<div class="pos-layout">
    <div style="display: flex; flex-direction: column; gap: 1rem; height: 100%;">
        <div class="card" style="margin-bottom: 0;">
            <input type="text" id="searchInput" class="form-control" placeholder=" Buscar producto por nombre o código..." autofocus>
            
            <div class="category-filter mt-4" id="categoryFilter">
                <button class="cat-btn active" data-id="all">Todos</button>
                <?php foreach($categories as $cat): ?>
                <button class="cat-btn" data-id="<?= $cat['id'] ?>"><?= $cat['nombre'] ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="product-grid" id="productGrid">
        </div>
    </div>

    <div class="cart-panel">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
            <h2 style="font-size: 1.25rem;">Venta Actual</h2>
            <div style="font-size: 0.8rem; color: var(--text-light);"><?= date('d M Y') ?></div>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="text-center" style="margin-top: 2rem; color: var(--text-light);">
                El carrito está vacío
            </div>
        </div>

        <div class="cart-summary">
            <div class="mb-4">
                <label class="form-label" style="font-size: 0.9rem;">Método de Pago</label>
                <select id="metodoPago" class="form-control">
                    <option value="Efectivo"> Efectivo</option>
                    <option value="Tarjeta"> Tarjeta Débito/Crédito</option>
                </select>
            </div>

            <div class="flex justify-between mb-4">
                <span style="font-size: 1.25rem; font-weight: 600;">Total</span>
                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);" id="totalDisplay">S/ 0.00</span>
            </div>
            
            <button onclick="procesarVenta()" id="btnCobrar" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                <i class="fa-solid fa-check"></i> Cobrar y Finalizar
            </button>
            <button onclick="limpiarCarrito()" class="btn btn-danger mt-4" style="width: 100%;">
                <i class="fa-solid fa-trash"></i> Cancelar Venta
            </button>
        </div>
    </div>
</div>

<script>
    const products = <?= json_encode($products) ?>;
    let cart = [];

    const searchInput = document.getElementById('searchInput');
    const productGrid = document.getElementById('productGrid');
    const cartItems = document.getElementById('cartItems');
    const totalDisplay = document.getElementById('totalDisplay');

    renderProducts(products);

    let currentCategory = 'all';

    searchInput.addEventListener('input', () => {
        filterProducts();
    });

    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCategory = btn.dataset.id;
            filterProducts();
        });
    });

    function filterProducts() {
        const term = searchInput.value.toLowerCase();
        const filtered = products.filter(p => {
            const matchesSearch = p.nombre.toLowerCase().includes(term) || (p.codigo_barras && p.codigo_barras.includes(term));
            const matchesCat = currentCategory === 'all' || p.categoria_id == currentCategory;
            return matchesSearch && matchesCat;
        });
        renderProducts(filtered);
    }

    function renderProducts(list) {
        productGrid.innerHTML = list.map(p => {
            const outOfStock = p.stock <= 0;
            return `
                <div class="pos-product-card ${outOfStock ? 'out-of-stock' : ''}" 
                     onclick="${outOfStock ? '' : `addToCart(${p.id})`}"
                     style="${outOfStock ? 'opacity: 0.6; cursor: not-allowed;' : 'cursor: pointer;'}">
                    
                    <div class="product-img-container" style="width: 100%; height: 100px; margin-bottom: 0.75rem; border-radius: 8px; overflow: hidden; background: #f1f5f9;">
                        ${p.imagen 
                            ? `<img src="${p.imagen}" style="width: 100%; height: 100%; object-fit: cover;">` 
                            : `<div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-light);"><i class="fa-solid fa-image fa-2x"></i></div>`
                        }
                    </div>

                    <div style="font-weight: 600; margin-bottom: 0.25rem; font-size: 0.9rem; line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${p.nombre}</div>
                    <div style="color: var(--primary); font-weight: 700; margin-top: 0.25rem;">S/ ${parseFloat(p.precio).toFixed(2)}</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <small style="color: ${outOfStock ? 'var(--danger)' : 'var(--text-light)'}; font-weight: ${outOfStock ? '700' : '400'}">
                            ${outOfStock ? 'SIN STOCK' : `Stock: ${p.stock}`}
                        </small>
                    </div>
                </div>
            `;
        }).join('');
    }

    function addToCart(id) {
        const product = products.find(p => p.id == id);
        
        if (!product || product.stock <= 0) {
            alert("Este producto no tiene stock disponible.");
            return;
        }

        const existing = cart.find(item => item.id == id);
        
        if (existing) {
            if (existing.cantidad < product.stock) {
                existing.cantidad++;
            } else {
                alert("No puedes agregar más de este producto. Stock máximo alcanzado (" + product.stock + ")");
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
            totalDisplay.innerText = "S/ 0.00";
            return;
        }

        let total = 0;
        cartItems.innerHTML = cart.map(item => {
            const subtotal = item.cantidad * item.precio;
            total += subtotal;
            return `
                <div class="flex justify-between items-center mb-4" style="border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: #e2e8f0;">
                        ${item.imagen 
                            ? `<img src="${item.imagen}" style="width: 100%; height: 100%; object-fit: cover;">`
                            : `<div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-light); font-size: 0.7rem;"><i class="fa-solid fa-image"></i></div>`
                        }
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem;">${item.nombre}</div>
                        <div style="font-size: 0.85rem; color: var(--text-light);">
                            ${item.cantidad} x S/ ${parseFloat(item.precio).toFixed(2)}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600;">S/ ${subtotal.toFixed(2)}</div>
                        <button onclick="removeFromCart(${item.id})" style="color: var(--danger); background: none; border: none; cursor: pointer; font-size: 0.8rem;">Eliminar</button>
                    </div>
                </div>
            `;
        }).join('');

        totalDisplay.innerText = "S/ " + total.toFixed(2);
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

        const btnCobrar = document.getElementById('btnCobrar');
        btnCobrar.disabled = true;
        btnCobrar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

        const metodo = document.getElementById('metodoPago').value;

        fetch('procesar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart, metodo_pago: metodo })
        })
        .then(res => {

            return res.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {

                    throw new Error("Respuesta no válida del servidor: " + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'ticket.php?id=' + data.id;
            } else {
                alert(' ERROR: ' + data.message);
                btnCobrar.disabled = false;
                btnCobrar.innerHTML = '<i class="fa-solid fa-check"></i> Cobrar y Finalizar';
            }
        })
        .catch(err => {
            console.error(err);
            alert(' ' + err.message);
            btnCobrar.disabled = false;
            btnCobrar.innerHTML = '<i class="fa-solid fa-check"></i> Cobrar y Finalizar';
        });
    }
</script>

<style>
    .category-filter {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    .cat-btn {
        background: white;
        border: 1px solid var(--border);
        padding: 0.4rem 0.8rem;
        border-radius: 99px;
        white-space: nowrap;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cat-btn:hover {
        background: #f8fafc;
        border-color: var(--primary);
    }
    .cat-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        font-weight: 600;
    }
</style>

<?php include("../includes/footer.php"); ?>
