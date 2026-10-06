CREATE DATABASE IF NOT EXISTS `gastrosoft_db` ;

USE gastrosoft_db;

CREATE TABLE rol(
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR (50) NOT NULL UNIQUE,
description VARCHAR(255) NULL,
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario(
id INT AUTO_INCREMENT PRIMARY KEY,
rol_id INT NOT NULL,
nombre_completo VARCHAR (150) NOT NULL,
email VARCHAR (100) NOT NULL UNIQUE,
password VARCHAR (255) NOT NULL,
estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) 
REFERENCES `rol` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE mesa(
id INT AUTO_INCREMENT PRIMARY KEY,
numero_mesa INT NOT NULL UNIQUE,
ubicacion VARCHAR(80) NOT NULL DEFAULT 'Salon principal',
estado ENUM('Disponible','Ocupada','Precuenta','Reservada') DEFAULT 'Disponible',
capacidad INT DEFAULT 4,
pos_x INT DEFAULT 50,
pos_y INT DEFAULT 50,
forma ENUM('Redonda','Rectangular') DEFAULT 'Rectangular',
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categoria(
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL UNIQUE,
description VARCHAR (255) NULL,
activo TINYINT(1) DEFAULT 1,
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE producto(
id INT AUTO_INCREMENT PRIMARY KEY,
categoria_id INT NOT NULL,
nombre VARCHAR (120) NOT NULL,
description TEXT NULL,
precio_venta DECIMAL(10,2) NOT NULL,
disponible TINYINT(1) DEFAULT 1,
img_url VARCHAR (255) NULL,
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) 
REFERENCES `categoria` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE inventario(
id INT AUTO_INCREMENT PRIMARY KEY,
producto_id INT NOT NULL UNIQUE,
stock_actual DECIMAL (10,2) NOT NULL DEFAULT 0.00,
stock_bajo DECIMAL(10,2) NOT NULL DEFAULT 5.00,
und_medida VARCHAR (30) NOT NULL DEFAULT 'Unidad',
actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
CONSTRAINT `fk_inventario_producto` FOREIGN KEY (`producto_id`)
REFERENCES `producto`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE pedido(
id INT AUTO_INCREMENT PRIMARY KEY,
mesa_id INT NOT NULL,
usuario_id INT NOT NULL,
total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
estado ENUM('Pendiente','En Preparacion','Listo','Entregado','Pagado','Cancelado') DEFAULT 'Pendiente',
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_pedido_mesa` FOREIGN KEY (`mesa_id`)
REFERENCES `mesa`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`)
REFERENCES `usuario`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE detalle_pedido(
id INT AUTO_INCREMENT PRIMARY KEY,
pedido_id INT NOT NULL,
producto_id INT NOT NULL,
cantidad INT NOT NULL DEFAULT 1,
precio_unitario DECIMAL (10,2) NOT NULL,
subtotal DECIMAL (10,2) NOT NULL,
observaciones VARCHAR(255) NULL,
estado_kds ENUM ('Pendiente','Cocinando','Listo') DEFAULT 'Pendiente',
CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`pedido_id`)
REFERENCES `pedido`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `fk,detalle_producto` FOREIGN KEY (`producto_id`)
REFERENCES `producto`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE pago(
id INT AUTO_INCREMENT PRIMARY KEY,
pedido_id INT NOT NULL UNIQUE,
metodo_pago ENUM('Efectivo','Transferencia','Tarjeta','Mixto') DEFAULT 'Efectivo',
monto DECIMAL (10,2) NOT NULL,
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`)
REFERENCES `pedido`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE notificacion(
id INT AUTO_INCREMENT PRIMARY KEY,
pedido_id INT NULL,
usuario_id INT NULL,
tipo ENUM ('comanda_lista', 'cambio_comanda', 'solicitar_cuenta', 'llamado_mesero') NOT NULL,
mensaje VARCHAR (255) NOT NULL,
leido TINYINT(1) DEFAULT 0,
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_notif_pedido` FOREIGN KEY (`pedido_id`) 
REFERENCES `pedido` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) 
REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE gasto(
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL,
description VARCHAR (255) NOT NULL,
monto DECIMAL (10,2) NOT NULL,
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT `fk_gasto_usuario` FOREIGN KEY (`usuario_id`)
REFERENCES `usuario`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE cierre_caja(
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL,
fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
monto_ventas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
monto_gastos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
monto_esperado DECIMAL (10,2) NOT NULL DEFAULT 0.00,
monto_real DECIMAL (10,2) NOT NULL DEFAULT 0.00,
diferencia DECIMAL (10,2) NOT NULL DEFAULT 0.00,
observaciones VARCHAR (255) NULL,
CONSTRAINT `fk_cierre_usuario` FOREIGN KEY (`usuario_id`)
REFERENCES `usuario`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE reserva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT NOT NULL,
    nombre_cliente VARCHAR(120) NOT NULL,
    telefono VARCHAR(25) NOT NULL,
    cantidad_personas INT NOT NULL DEFAULT 2,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NULL,
    estado ENUM('confirmada', 'en_mesa', 'finalizada', 'cancelada') DEFAULT 'confirmada',
    observaciones VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reserva_mesa` FOREIGN KEY (`mesa_id`) 
        REFERENCES `mesa` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

INSERT INTO `rol` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de inventario, finanzas y usuarios'),
(2, 'Mesero', 'Atención en salón, toma de pedidos y precuenta'),
(3, 'Cocinero', 'Monitoreo de comandas en KDS y preparación'),
(4, 'Cajero', 'Liquidación de cuentas y caja');

-- Contraseña de prueba genérica: 'admin123' hasheada
INSERT INTO `usuario` (`rol_id`, `nombre_completo`, `email`, `password`, `estado`) VALUES
(1, 'Omar Trujillo', 'admin@gastrosoft.com', '$2y$10$wT8KzU7E8QoG5D6eP8k8g.BqV9p4b7x/1aY7xQ.5w3w0s3r6u2f4y', 'activo'),
(2, 'Juan Rodriguez ', 'mesero@gastrosoft.com', '$2y$10$wT8KzU7E8QoG5D6eP8k8g.BqV9p4b7x/1aY7xQ.5w3w0s3r6u2f4y', 'activo'),
(3, 'Diego Losada', 'cocina@gastrosoft.com', '$2y$10$wT8KzU7E8QoG5D6eP8k8g.BqV9p4b7x/1aY7xQ.5w3w0s3r6u2f4y', 'activo'),
(4, 'Daniela Penagos', 'caja@gastrosoft.com', '$2y$10$wT8KzU7E8QoG5D6eP8k8g.BqV9p4b7x/1aY7xQ.5w3w0s3r6u2f4y', 'activo');

-- Mesas iniciales con coordenadas para el mapa
INSERT INTO `mesa` (`numero_mesa`, `ubicacion`, `estado`, `capacidad`, `pos_x`, `pos_y`, `forma`) VALUES
(1, 'Salón Principal', 'disponible', 4, 120, 100, 'redonda'),
(2, 'Salón Principal', 'ocupada', 4, 300, 100, 'cuadrada'),
(3, 'Salón Principal', 'precuenta', 6, 480, 100, 'cuadrada'),
(4, 'Terraza', 'disponible', 2, 120, 240, 'redonda'),
(5, 'Terraza', 'ocupada', 4, 300, 240, 'cuadrada');

INSERT INTO `categoria` (`nombre`, `descripcion`) VALUES
('Platos Fuertes', 'Plato inicial para comer de entrada'),
('Cervezas & Licores', 'Cervezas artesanales e importadas'),
('Postres', 'Postres para degustar al final de la comida');

INSERT INTO `producto` (`categoria_id`, `nombre`, `descripcion`, `precio_venta`, `disponible`) VALUES
(2, 'Margarita Clásica', 'Tequila blanco, triple sec y zumo de lima fresco', 25000.00, 1),
(1, 'Hamburguesa Gastro', '200g carne de res madurada, queso cheddar y tocineta', 32000.00, 1),
(1, 'Papas Rústicas Trufadas', 'Papas crocantes con aceite de trufa y queso parmesano', 18000.00, 1),
(3, 'Tiramisu', 'Tradicional y popular postre frío de origen italiano', 5000.00, 1);

INSERT INTO `inventario` (`producto_id`, `stock_actual`, `stock_minimo`, `und_medida`) VALUES
(1, 1000.00, 10.00, 'ml'),
(2, 35.00, 10.00, 'unidad'),
(3, 50.00, 15.00, 'unidad'),
(4, 30.00, 10.00, 'unidad');