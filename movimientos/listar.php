<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requiere_login();

// Filtros (llegan por GET para poder compartir el enlace)
$producto = filter_input(INPUT_GET, 'producto', FILTER_VALIDATE_INT) ?: 0;
$tipo     = $_GET['tipo'] ?? '';
if (!in_array($tipo, ['ENTRADA', 'SALIDA'], true)) {
    $tipo = '';
}

$sql = 'SELECT m.fecha_hora, m.tipo, m.cantidad, m.observacion,
               p.codigo, p.nombre AS producto, u.nombre AS usuario
        FROM movimiento m
        JOIN producto p ON p.id_producto = m.id_producto
        JOIN usuario  u ON u.id_usuario  = m.id_usuario
        WHERE 1 = 1';
$params = [];

if ($producto > 0) {
    $sql .= ' AND m.id_producto = :producto';
    $params[':producto'] = $producto;
}
if ($tipo !== '') {
    $sql .= ' AND m.tipo = :tipo';
    $params[':tipo'] = $tipo;
}
$sql .= ' ORDER BY m.fecha_hora DESC, m.id_movimiento DESC LIMIT 200';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

$productos = $pdo->query('SELECT id_producto, codigo, nombre FROM producto ORDER BY nombre')->fetchAll();

$titulo = 'Movimientos';
$activo = 'movimientos';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Movimientos de inventario</h1>
    <a href="<?= BASE_URL ?>/movimientos/registrar.php" class="btn btn-primary">Registrar movimiento</a>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-6">
        <select class="form-select" name="producto">
            <option value="0">Todos los productos</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= (int) $p['id_producto'] ?>" <?= $producto === (int) $p['id_producto'] ? 'selected' : '' ?>>
                    <?= e($p['codigo']) ?> · <?= e($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <select class="form-select" name="tipo">
            <option value=""        <?= $tipo === '' ? 'selected' : '' ?>>Entradas y salidas</option>
            <option value="ENTRADA" <?= $tipo === 'ENTRADA' ? 'selected' : '' ?>>Solo entradas</option>
            <option value="SALIDA"  <?= $tipo === 'SALIDA' ? 'selected' : '' ?>>Solo salidas</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle bg-white">
        <thead class="table-light">
            <tr>
                <th>Fecha y hora</th><th>Producto</th><th>Tipo</th>
                <th class="text-end">Cantidad</th><th>Observación</th><th>Usuario</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$movimientos): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron movimientos.</td></tr>
        <?php endif; ?>
        <?php foreach ($movimientos as $m): ?>
            <tr>
                <td><?= e(date('d-m-Y H:i', strtotime($m['fecha_hora']))) ?></td>
                <td><?= e($m['codigo']) ?> · <?= e($m['producto']) ?></td>
                <td>
                    <?= $m['tipo'] === 'ENTRADA'
                        ? '<span class="badge text-bg-success">Entrada</span>'
                        : '<span class="badge text-bg-danger">Salida</span>' ?>
                </td>
                <td class="text-end"><?= (int) $m['cantidad'] ?></td>
                <td><?= e($m['observacion'] ?? '') ?></td>
                <td><?= e($m['usuario']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small"><?= count($movimientos) ?> movimiento(s) (se muestran los 200 más recientes).</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
