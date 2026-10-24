-- TecnoStock - Script de base de datos
-- Motor: MySQL / MariaDB (XAMPP)

DROP DATABASE IF EXISTS tecnostock;
CREATE DATABASE tecnostock
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE tecnostock;


CREATE TABLE usuario (
    id_usuario  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL,
    clave       VARCHAR(255) NOT NULL,          -- hash, nunca texto plano
    estado      TINYINT(1)   NOT NULL DEFAULT 1, -- 1 = activo, 0 = inactivo
    CONSTRAINT pk_usuario        PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuario_correo UNIQUE (correo)
) ENGINE=InnoDB;


CREATE TABLE categoria (
    id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(80)  NOT NULL,
    descripcion  VARCHAR(255) NULL,
    estado       TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT pk_categoria        PRIMARY KEY (id_categoria),
    CONSTRAINT uq_categoria_nombre UNIQUE (nombre)
) ENGINE=InnoDB;


CREATE TABLE producto (
    id_producto  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    codigo       VARCHAR(30)   NOT NULL,
    nombre       VARCHAR(120)  NOT NULL,
    descripcion  VARCHAR(255)  NULL,
    precio       DECIMAL(10,2) NOT NULL,
    stock_actual INT           NOT NULL DEFAULT 0,
    stock_minimo INT           NOT NULL DEFAULT 0,
    estado       TINYINT(1)    NOT NULL DEFAULT 1,
    id_categoria INT UNSIGNED  NOT NULL,
    CONSTRAINT pk_producto        PRIMARY KEY (id_producto),
    CONSTRAINT uq_producto_codigo UNIQUE (codigo),
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria)
        REFERENCES categoria (id_categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_producto_precio       CHECK (precio >= 0),
    CONSTRAINT ck_producto_stock_actual CHECK (stock_actual >= 0),
    CONSTRAINT ck_producto_stock_minimo CHECK (stock_minimo >= 0),
    INDEX idx_producto_nombre (nombre)
) ENGINE=InnoDB;


CREATE TABLE movimiento (
    id_movimiento INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha_hora    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo          ENUM('ENTRADA','SALIDA') NOT NULL,
    cantidad      INT          NOT NULL,
    observacion   VARCHAR(255) NULL,
    id_producto   INT UNSIGNED NOT NULL,
    id_usuario    INT UNSIGNED NOT NULL,
    CONSTRAINT pk_movimiento PRIMARY KEY (id_movimiento),
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (id_producto)
        REFERENCES producto (id_producto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_movimiento_cantidad CHECK (cantidad > 0),
    INDEX idx_movimiento_producto (id_producto),
    INDEX idx_movimiento_fecha (fecha_hora)
) ENGINE=InnoDB;


CREATE VIEW v_stock_bajo AS
SELECT p.id_producto, p.codigo, p.nombre, p.stock_actual, p.stock_minimo,
       c.nombre AS categoria
FROM producto p
JOIN categoria c ON c.id_categoria = p.id_categoria
WHERE p.estado = 1
  AND p.stock_actual < p.stock_minimo;

-- Prueba
-- Usuario rike  /  Clave: rike2212
INSERT INTO usuario (nombre, correo, clave) VALUES
('Encargado de Inventario', 'rike',
 '$2y$10$j4u7OqGwcuYqDYVdS/sJT.Zlw82lk4qw3M6CIncl2624tzGuhEgJS');

INSERT INTO categoria (nombre, descripcion) VALUES
('Periféricos', 'Mouse, teclados y webcams'),
('Cables y adaptadores', 'Cables HDMI, USB y adaptadores'),
('Audio', 'Audífonos y parlantes'),
('Almacenamiento', 'Pendrives y discos externos');

INSERT INTO producto
    (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
('ACC-001', 'Mouse inalámbrico',  'Mouse 2.4GHz con receptor USB',  12990, 25,  5, 1, 1),
('ACC-002', 'Teclado mecánico',   'Teclado mecánico español',       39990,  3,  5, 1, 1),
('ACC-003', 'Cable HDMI 2m',      'Cable HDMI 4K de 2 metros',       5990, 40, 10, 1, 2),
('ACC-004', 'Audífonos Bluetooth','Audífonos inalámbricos con micrófono', 24990, 8, 8, 1, 3),
('ACC-005', 'Cargador USB-C 20W', 'Cargador rápido de pared',       11990,  0,  5, 1, 2),
('ACC-006', 'Pendrive 64GB',      'Pendrive USB 3.0 de 64GB',        7990, 30, 10, 1, 4),
('ACC-007', 'Webcam HD',          'Producto descontinuado',         19990,  2,  3, 0, 1);


INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
('2026-10-01 09:00:00', 'ENTRADA', 30, 'Compra inicial',      1, 1),
('2026-10-05 15:30:00', 'SALIDA',   5, 'Venta mostrador',     1, 1),
('2026-10-01 09:10:00', 'ENTRADA', 10, 'Compra inicial',      2, 1),
('2026-10-06 11:00:00', 'SALIDA',   7, 'Venta online',        2, 1),
('2026-10-01 09:20:00', 'ENTRADA', 50, 'Compra inicial',      3, 1),
('2026-10-07 16:45:00', 'SALIDA',  10, 'Venta online',        3, 1),
('2026-10-01 09:30:00', 'ENTRADA', 10, 'Compra inicial',      4, 1),
('2026-10-08 12:15:00', 'SALIDA',   2, 'Venta mostrador',     4, 1),
('2026-10-01 09:40:00', 'ENTRADA', 10, 'Compra inicial',      5, 1),
('2026-10-09 10:00:00', 'SALIDA',  10, 'Venta por mayor',     5, 1),
('2026-10-01 09:50:00', 'ENTRADA', 30, 'Compra inicial',      6, 1),
('2026-09-15 10:00:00', 'ENTRADA',  5, 'Compra inicial',      7, 1),
('2026-09-28 17:00:00', 'SALIDA',   3, 'Venta mostrador',     7, 1);
