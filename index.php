<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
requiere_login();   

$stockBajo = $pdo->query('SELECT codigo, nombre, stock_actual, stock_minimo, categoria FROM v_stock_bajo')
                 ->fetchAll();

$titulo = 'Inicio';
$activo = 'inicio';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Inicio</h1>

<?php if (count($stockBajo) > 0): ?>
    <div class="alert alert-warning" role="alert">
        <strong>Atención:</strong> <?= count($stockBajo) ?> producto(s) con stock bajo el mínimo.
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered bg-white">
            <thead class="table-light">
                <tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mínimo</th></tr>
            </thead>
            <tbody>
            <?php foreach ($stockBajo as $p): ?>
                <tr>
                    <td><?= e($p['codigo']) ?></td>
                    <td><?= e($p['nombre']) ?></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td class="text-danger fw-bold"><?= (int) $p['stock_actual'] ?></td>
                    <td><?= (int) $p['stock_minimo'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-success">Todo el inventario está sobre el stock mínimo.</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
