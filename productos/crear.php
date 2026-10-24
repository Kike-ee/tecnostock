<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/validaciones.php';
requiere_login();

$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();

$datos = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'precio' => '',
          'stock_minimo' => '0', 'stock_inicial' => '0', 'id_categoria' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores['general'] = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        [$datos, $errores] = validar_producto($_POST, true);

        // La categoría debe existir y estar activa
        if (!$errores) {
            $q = $pdo->prepare('SELECT 1 FROM categoria WHERE id_categoria = :id AND estado = 1');
            $q->execute([':id' => (int) $datos['id_categoria']]);
            if (!$q->fetchColumn()) {
                $errores['id_categoria'] = 'Selecciona una categoría válida.';
            }
        }

        if (!$errores) {
            try {
                // Transacción: o se guarda todo (producto + movimiento) o no se guarda nada.
                $pdo->beginTransaction();

                $ins = $pdo->prepare(
                    'INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria)
                     VALUES (:codigo, :nombre, :descripcion, :precio, :stock, :minimo, :categoria)'
                );
                $ins->execute([
                    ':codigo'      => $datos['codigo'],
                    ':nombre'      => $datos['nombre'],
                    ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                    ':precio'      => $datos['precio'],
                    ':stock'       => (int) $datos['stock_inicial'],
                    ':minimo'      => (int) $datos['stock_minimo'],
                    ':categoria'   => (int) $datos['id_categoria'],
                ]);
                $idProducto = (int) $pdo->lastInsertId();

                if ((int) $datos['stock_inicial'] > 0) {
                    $mov = $pdo->prepare(
                        "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                         VALUES ('ENTRADA', :cantidad, 'Stock inicial', :producto, :usuario)"
                    );
                    $mov->execute([
                        ':cantidad' => (int) $datos['stock_inicial'],
                        ':producto' => $idProducto,
                        ':usuario'  => $_SESSION['id_usuario'],
                    ]);
                }

                $pdo->commit();
                flash('success', 'Producto creado correctamente.');
                header('Location: ' . BASE_URL . '/productos/listar.php');
                exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if (($e->errorInfo[1] ?? 0) == 1062) {          // clave UNIQUE repetida
                    $errores['codigo'] = 'Ya existe un producto con ese código.';
                } else {
                    error_log($e->getMessage());
                    $errores['general'] = 'No se pudo guardar el producto. Inténtalo de nuevo.';
                }
            }
        }
    }
}

$titulo  = 'Nuevo producto';
$activo  = 'productos';
$esNuevo = true;
$scripts = ['producto.js'];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
