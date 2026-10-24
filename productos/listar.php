<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requiere_login();

// Filtros que vienen por la URL (GET)
$q         = trim($_GET['q'] ?? '');
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: 0;
$estado    = $_GET['estado'] ?? '1';                    // '1' activos, '0' inactivos, 'todos'
if (!in_array($estado, ['1', '0', 'todos'], true)) {
    $estado = '1';
}

$sql = 'SELECT p.id_producto, p.codigo, p.nombre, p.precio, p.stock_actual, p.stock_minimo,
               p.estado, c.nombre AS categoria
        FROM producto p
        JOIN categoria c ON c.id_categoria = p.id_categoria
        WHERE 1 = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (p.codigo LIKE :q1 OR p.nombre LIKE :q2)';
    $like = '%' . addcslashes($q, '%_\\') . '%';        // los % y _ escritos se tratan como texto
    $params[':q1'] = $like;
    $params[':q2'] = $like;
}
if ($categoria > 0) {
    $sql .= ' AND p.id_categoria = :categoria';
    $params[':categoria'] = $categoria;
}
if ($estado !== 'todos') {
    $sql .= ' AND p.estado = :estado';
    $params[':estado'] = (int) $estado;
}
$sql .= ' ORDER BY p.nombre';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria ORDER BY nombre')->fetchAll();

$titulo = 'Productos';
$activo = 'productos';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Productos</h1>
    <a href="<?= BASE_URL ?>/productos/crear.php" class="btn btn-primary">Nuevo producto</a>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" class="form-control" name="q" value="<?= e($q) ?>" placeholder="Buscar por código o nombre">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="categoria">
            <option value="0">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= $categoria === (int) $c['id_categoria'] ? 'selected' : '' ?>>
                    <?= e($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" name="estado">
            <option value="1"     <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0"     <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
            <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
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
                <th>Código</th><th>Producto</th><th>Categoría</th>
                <th class="text-end">Precio</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th>
                <th>Estado</th><th style="width: 190px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$productos): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron productos.</td></tr>
        <?php endif; ?>
        <?php foreach ($productos as $p): ?>
            <?php $bajo = (int) $p['estado'] === 1 && (int) $p['stock_actual'] < (int) $p['stock_minimo']; ?>
            <tr class="<?= (int) $p['estado'] === 0 ? 'table-secondary' : '' ?>">
                <td><?= e($p['codigo']) ?></td>
                <td><?= e($p['nombre']) ?></td>
                <td><?= e($p['categoria']) ?></td>
                <td class="text-end">$<?= number_format((float) $p['precio'], 0, ',', '.') ?></td>
                <td class="text-end <?= $bajo ? 'text-danger fw-bold' : '' ?>">
                    <?= (int) $p['stock_actual'] ?>
                    <?php if ($bajo): ?><span class="badge text-bg-warning">Stock bajo</span><?php endif; ?>
                </td>
                <td class="text-end"><?= (int) $p['stock_minimo'] ?></td>
                <td>
                    <?= (int) $p['estado'] === 1
                        ? '<span class="badge text-bg-success">Activo</span>'
                        : '<span class="badge text-bg-secondary">Inactivo</span>' ?>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline-primary"
                       href="<?= BASE_URL ?>/productos/editar.php?id=<?= (int) $p['id_producto'] ?>">Editar</a>
                    <form method="post" action="<?= BASE_URL ?>/productos/estado.php" class="d-inline"
                          onsubmit="return confirm('<?= (int) $p['estado'] === 1 ? '¿Desactivar' : '¿Reactivar' ?> este producto?');">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $p['id_producto'] ?>">
                        <input type="hidden" name="estado" value="<?= (int) $p['estado'] === 1 ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm <?= (int) $p['estado'] === 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                            <?= (int) $p['estado'] === 1 ? 'Desactivar' : 'Reactivar' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small"><?= count($productos) ?> producto(s) encontrado(s).</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
