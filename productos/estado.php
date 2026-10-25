<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requiere_login();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido($_POST['csrf'] ?? null)) {
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $estado = ($_POST['estado'] ?? '') === '1' ? 1 : 0;

    $existe = false;
    if ($id) {
        $chk = $pdo->prepare('SELECT 1 FROM producto WHERE id_producto = :id');
        $chk->execute([':id' => $id]);
        $existe = (bool) $chk->fetchColumn();
    }

    if ($existe) {
        $upd = $pdo->prepare('UPDATE producto SET estado = :estado WHERE id_producto = :id');
        $upd->execute([':estado' => $estado, ':id' => $id]);
        flash('success', $estado === 1 ? 'Producto reactivado.' : 'Producto desactivado.');
    } else {
        flash('danger', 'El producto no existe.');
    }
} else {
    flash('danger', 'No se pudo completar la acción.');
}
header('Location: ' . BASE_URL . '/productos/listar.php');
exit;
