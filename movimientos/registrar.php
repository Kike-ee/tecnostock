<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/validaciones.php';
requiere_login();

// Solo productos activos pueden recibir movimientos.
$productos = $pdo->query(
    'SELECT id_producto, codigo, nombre, stock_actual
     FROM producto WHERE estado = 1 ORDER BY nombre'
)->fetchAll();

// Desde el listado de productos se puede abrir el formulario con el producto elegido.
$preseleccion = filter_input(INPUT_GET, 'id_producto', FILTER_VALIDATE_INT) ?: '';
$datos = [
    'id_producto' => (string) $preseleccion,
    'tipo'        => '',
    'cantidad'    => '',
    'observacion' => '',
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores['general'] = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        [$datos, $errores] = validar_movimiento($_POST);

        if (!$errores) {
            try {
                $pdo->beginTransaction();

                // Bloquea la fila del producto hasta el commit: si dos personas registran
                // una salida al mismo tiempo, la segunda espera y ve el stock ya actualizado.
                $sel = $pdo->prepare(
                    'SELECT stock_actual FROM producto
                     WHERE id_producto = :id AND estado = 1 FOR UPDATE'
                );
                $sel->execute([':id' => (int) $datos['id_producto']]);
                $stock = $sel->fetchColumn();

                if ($stock === false) {
                    $pdo->rollBack();
                    $errores['id_producto'] = 'El producto no existe o está inactivo.';
                } elseif ($datos['tipo'] === 'SALIDA' && (int) $datos['cantidad'] > (int) $stock) {
                    // Regla 4: una salida no puede dejar el stock bajo cero.
                    $pdo->rollBack();
                    $errores['cantidad'] = 'Stock insuficiente: hay ' . (int) $stock . ' unidad(es) disponible(s).';
                } else {
                    $cantidad   = (int) $datos['cantidad'];
                    $nuevoStock = $datos['tipo'] === 'ENTRADA' ? (int) $stock + $cantidad : (int) $stock - $cantidad;

                    // Regla 6: el stock se actualiza automáticamente con cada movimiento válido.
                    $upd = $pdo->prepare('UPDATE producto SET stock_actual = :stock WHERE id_producto = :id');
                    $upd->execute([':stock' => $nuevoStock, ':id' => (int) $datos['id_producto']]);

                    // Regla 3: fecha (automática), tipo, cantidad, producto y usuario responsable.
                    $ins = $pdo->prepare(
                        'INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                         VALUES (:tipo, :cantidad, :observacion, :producto, :usuario)'
                    );
                    $ins->execute([
                        ':tipo'        => $datos['tipo'],
                        ':cantidad'    => $cantidad,
                        ':observacion' => $datos['observacion'] !== '' ? $datos['observacion'] : null,
                        ':producto'    => (int) $datos['id_producto'],
                        ':usuario'     => $_SESSION['id_usuario'],
                    ]);

                    $pdo->commit();
                    flash('success', ($datos['tipo'] === 'ENTRADA' ? 'Entrada' : 'Salida')
                        . ' registrada. Stock actual: ' . $nuevoStock . '.');
                    header('Location: ' . BASE_URL . '/movimientos/listar.php');
                    exit;
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log($e->getMessage());
                $errores['general'] = 'No se pudo registrar el movimiento. Inténtalo de nuevo.';
            }
        }
    }
}

function cls(array $errores, string $campo): string {
    return isset($errores[$campo]) ? ' is-invalid' : '';
}

$titulo  = 'Registrar movimiento';
$activo  = 'movimientos';
$scripts = ['movimiento.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Registrar movimiento</h1>

<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger" role="alert"><?= e($errores['general']) ?></div>
<?php endif; ?>

<form id="form-movimiento" method="post" action="" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="id_producto" class="form-label">Producto</label>
            <select class="form-select<?= cls($errores, 'id_producto') ?>" id="id_producto" name="id_producto" required>
                <option value="">Selecciona...</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= (int) $p['id_producto'] ?>"
                            data-stock="<?= (int) $p['stock_actual'] ?>"
                        <?= (string) $p['id_producto'] === (string) $datos['id_producto'] ? 'selected' : '' ?>>
                        <?= e($p['codigo']) ?> · <?= e($p['nombre']) ?> (stock: <?= (int) $p['stock_actual'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errores['id_producto'] ?? '') ?></div>
        </div>
        <div class="col-md-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select<?= cls($errores, 'tipo') ?>" id="tipo" name="tipo" required>
                <option value="">Selecciona...</option>
                <option value="ENTRADA" <?= $datos['tipo'] === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                <option value="SALIDA"  <?= $datos['tipo'] === 'SALIDA' ? 'selected' : '' ?>>Salida</option>
            </select>
            <div class="invalid-feedback"><?= e($errores['tipo'] ?? '') ?></div>
        </div>
        <div class="col-md-3">
            <label for="cantidad" class="form-label">Cantidad</label>
            <input type="number" class="form-control<?= cls($errores, 'cantidad') ?>" id="cantidad" name="cantidad"
                   value="<?= e($datos['cantidad']) ?>" min="1" max="1000000" step="1" required>
            <div class="invalid-feedback"><?= e($errores['cantidad'] ?? '') ?></div>
        </div>
        <div class="col-12">
            <label for="observacion" class="form-label">Observación <span class="text-muted">(opcional)</span></label>
            <input type="text" class="form-control<?= cls($errores, 'observacion') ?>" id="observacion" name="observacion"
                   value="<?= e($datos['observacion']) ?>" maxlength="255" placeholder="Ej.: Compra a proveedor, venta mostrador">
            <div class="invalid-feedback"><?= e($errores['observacion'] ?? '') ?></div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= BASE_URL ?>/movimientos/listar.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
