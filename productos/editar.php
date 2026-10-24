<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/validaciones.php';
requiere_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT * FROM producto WHERE id_producto = :id');
$stmt->execute([':id' => $id ?: 0]);
$producto = $stmt->fetch();

if (!$producto) {
    flash('danger', 'El producto no existe.');
    header('Location: ' . BASE_URL . '/productos/listar.php');
    exit;
}

$categorias  = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();
$stockActual = $producto['stock_actual'];

$datos = [
    'codigo'       => $producto['codigo'],
    'nombre'       => $producto['nombre'],
    'descripcion'  => $producto['descripcion'] ?? '',
    'precio'       => $producto['precio'],
    'stock_minimo' => (string) $producto['stock_minimo'],
    'id_categoria' => (string) $producto['id_categoria'],
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores['general'] = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        [$datos, $errores] = validar_producto($_POST, false);

        if (!$errores) {
            $q = $pdo->prepare('SELECT 1 FROM categoria WHERE id_categoria = :id AND estado = 1');
            $q->execute([':id' => (int) $datos['id_categoria']]);
            if (!$q->fetchColumn()) {
                $errores['id_categoria'] = 'Selecciona una categoría válida.';
            }
        }

        if (!$errores) {
            try {
                
                $upd = $pdo->prepare(
                    'UPDATE producto
                     SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion,
                         precio = :precio, stock_minimo = :minimo, id_categoria = :categoria
                     WHERE id_producto = :id'
                );
                $upd->execute([
                    ':codigo'      => $datos['codigo'],
                    ':nombre'      => $datos['nombre'],
                    ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                    ':precio'      => $datos['precio'],
                    ':minimo'      => (int) $datos['stock_minimo'],
                    ':categoria'   => (int) $datos['id_categoria'],
                    ':id'          => $producto['id_producto'],
                ]);
                flash('success', 'Producto actualizado correctamente.');
                header('Location: ' . BASE_URL . '/productos/listar.php');
                exit;
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? 0) == 1062) {
                    $errores['codigo'] = 'Ya existe otro producto con ese código.';
                } else {
                    error_log($e->getMessage());
                    $errores['general'] = 'No se pudo actualizar el producto. Inténtalo de nuevo.';
                }
            }
        }
    }
}

$titulo  = 'Editar producto';
$activo  = 'productos';
$esNuevo = false;
$scripts = ['producto.js'];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
