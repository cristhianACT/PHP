-- Crear Base de Datos
CREATE DATABASE IF NOT EXISTS tienda;
USE tienda;

-- Tabla: Usuarios (Para login)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'cajero',
    nombre VARCHAR(100),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: Productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(50) UNIQUE NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: Ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'COMPLETADA',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla: Detalle Venta
CREATE TABLE IF NOT EXISTS detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Datos de prueba
INSERT INTO productos (nombre, precio, stock, codigo_barras) VALUES 
('Coca Cola 600ml', 1.50, 100, '5449000000996'),
('Papas Lays Clásicas', 2.00, 50, '7791234567890'),
('Galletas Oreo', 1.20, 80, '7622300090000'),
('Agua Mineral 1L', 1.00, 120, '8901234567890');
