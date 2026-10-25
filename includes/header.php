<?php

$titulo = $titulo ?? 'TecnoStock';
$activo = $activo ?? '';
$flash  = tomar_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">TecnoStock</a>
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link<?= $activo === 'inicio' ? ' active' : '' ?>" href="<?= BASE_URL ?>/index.php">Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $activo === 'productos' ? ' active' : '' ?>" href="<?= BASE_URL ?>/productos/listar.php">Productos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $activo === 'movimientos' ? ' active' : '' ?>" href="<?= BASE_URL ?>/movimientos/listar.php">Movimientos</a>
            </li>
        </ul>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 d-none d-sm-inline">Hola, <?= e($_SESSION['nombre'] ?? '') ?></span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php">Salir</a>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['tipo']) ?> " role="alert">
        <?= e($flash['mensaje']) ?>
    </div>
<?php endif; ?>
