<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
requiere_login();   // si no hay sesión, redirige al login

// Productos activos con stock actual menor que el mínimo (vista de la base)
$stockBajo = $pdo->query('SELECT codigo, nombre, stock_actual, stock_minimo, categoria FROM v_stock_bajo')
                 ->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio · TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">TecnoStock</span>
        <div class="d-flex align-items-center text-white">
            <span class="me-3">Hola, <?= e($_SESSION['nombre']) ?></span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php">Salir</a>
        </div>
    </div>
</nav>

<main class="container py-4">
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
</main>
</body>
</html>
