# TecnoStock · Sistema de inventario

Aplicación web para controlar el catálogo de productos y las entradas y salidas de existencias de TecnoStock, un emprendimiento que vende accesorios tecnológicos. Reemplaza la planilla actual, que provocaba códigos duplicados y errores en el stock.

**Tecnologías:** PHP 8 + PDO, MySQL/MariaDB, HTML5 + Bootstrap 5, JavaScript. Sin frameworks.

## 1. Problema y alcance

- **Problema:** la planilla permite códigos repetidos, cantidades erróneas y no muestra el stock real.
- **Actor:** Encargado de inventario (administra productos, categorías y movimientos).
- **Dentro del alcance:** inicio de sesión, gestión de productos, búsqueda, movimientos de entrada y salida, alerta de stock bajo.
- **Fuera del alcance:** múltiples roles, reportes, compras a proveedores, gestión de categorías desde la interfaz (se cargan por script SQL).

## 2. Requerimientos funcionales y dónde se cumplen

| N° | Requerimiento | Archivos |
| --- | --- | --- |
| RF1 | Iniciar sesión con un usuario registrado | `auth/login.php`, `assets/js/login.js`, `auth/logout.php` |
| RF2 | Registrar productos (código, nombre, categoría, descripción, precio, stock actual y mínimo) | `productos/crear.php`, `productos/_form.php`, `assets/js/producto.js` |
| RF3 | Consultar el listado de productos | `productos/listar.php` |
| RF4 | Buscar por nombre, código o categoría | `productos/listar.php` (filtros por GET) |
| RF5 | Modificar precio, descripción, categoría y cantidades | `productos/editar.php` (datos) y `movimientos/registrar.php` (cantidades) |
| RF6 | Desactivar productos que ya no se comercializan | `productos/estado.php` |
| RF7 | Registrar entradas y salidas de existencias | `movimientos/registrar.php`, `movimientos/listar.php`, `assets/js/movimiento.js` |
| RF8 | Alerta cuando el stock actual sea menor al mínimo | vista `v_stock_bajo`, `index.php`, `productos/listar.php` |

## 3. Reglas de negocio y dónde se aplican

| N° | Regla | Base de datos | PHP |
| --- | --- | --- | --- |
| RN1 | El código de producto no puede repetirse | `UNIQUE (codigo)` | Mensaje al detectar el error 1062 en `crear.php` y `editar.php` |
| RN2 | Precio, stocks y cantidades no pueden ser negativos | `CHECK` en `producto` y `movimiento` | `includes/validaciones.php` |
| RN3 | Todo movimiento registra fecha, tipo, cantidad, producto y usuario | Columnas `NOT NULL`, claves foráneas, fecha automática | `movimientos/registrar.php` (usuario tomado de la sesión) |
| RN4 | Una salida no puede dejar el stock bajo cero | `CHECK (stock_actual >= 0)` | Verificación con bloqueo `SELECT ... FOR UPDATE` en `movimientos/registrar.php` |
| RN5 | Un producto con movimientos se desactiva, no se elimina | `ON DELETE RESTRICT` | No existe ningún `DELETE`; `productos/estado.php` cambia el estado |
| RN6 | El stock se actualiza automáticamente con cada movimiento válido | — | Transacción en `movimientos/registrar.php` y `productos/crear.php` |

## 4. Modelo de datos

Cuatro entidades relacionadas: **usuario**, **categoria**, **producto** (pertenece a una categoría) y **movimiento** (pertenece a un producto y a un usuario). El script `sql/tecnostock.sql` crea las tablas con claves primarias, foráneas, únicas y `CHECK`, la vista `v_stock_bajo` y los datos de prueba. Es reproducible: borra y vuelve a crear la base `tecnostock`.

## 5. Instalación en XAMPP

1. Copiar la carpeta del proyecto en `C:\xampp\htdocs\tecnostock`.
2. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
3. En phpMyAdmin (`http://localhost/phpmyadmin`), importar `sql/tecnostock.sql`.
4. Abrir `http://localhost/tecnostock/auth/login.php`.

La conexión está en `config/db.php` (usuario `root` sin contraseña, valores por defecto de XAMPP).

**Usuario de prueba:** `fl4gas@gmail.com` · **Clave:** `rike2212`

## 6. Estructura

```
tecnostock/
├── assets/js/      login.js, producto.js, movimiento.js
├── auth/           login.php, logout.php
├── config/         db.php
├── includes/       auth.php, header.php, footer.php, validaciones.php
├── movimientos/    registrar.php, listar.php
├── productos/      listar.php, crear.php, editar.php, estado.php, _form.php
├── sql/            tecnostock.sql
├── index.php
├── PRUEBAS.md
└── README.md
```

## 7. Seguridad y calidad

- Consultas preparadas con PDO (`EMULATE_PREPARES` desactivado) contra inyección SQL.
- Salida de datos escapada con `e()` contra XSS.
- Token CSRF en todos los formularios que modifican datos, y cookie de sesión `HttpOnly` y `SameSite`.
- Claves guardadas con `password_hash` y verificadas con `password_verify`; el identificador de sesión se regenera al iniciar sesión.
- Validaciones repetidas en JavaScript (experiencia de uso) y en PHP (seguridad).
- Transacciones para que el stock y el historial de movimientos siempre coincidan.
- Pruebas documentadas en `PRUEBAS.md`.
