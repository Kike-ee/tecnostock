<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
iniciar_sesion();

// Si ya inició sesión, no tiene sentido ver el login.
if (!empty($_SESSION['id_usuario'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Token anti-CSRF: se genera una vez por sesión y viaja oculto en el formulario.
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error  = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';
    $token  = $_POST['csrf'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $token)) {
        $error = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
        // Validación del servidor (repite la que hace JavaScript)
        $error = 'Ingresa un correo válido y tu contraseña.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nombre, clave FROM usuario
             WHERE correo = :correo AND estado = 1'
        );
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($clave, $usuario['clave'])) {
            session_regenerate_id(true);   // nuevo ID de sesión tras autenticarse
            $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        // Mismo mensaje si falla el correo o la clave: no revela cuál estaba mal.
        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-11 col-sm-8 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-1">TecnoStock</h1>
                    <p class="text-center text-muted mb-4">Control de inventario</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form id="form-login" method="post" novalidate>
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo"
                                   value="<?= e($correo) ?>" autocomplete="username" required>
                            <div class="invalid-feedback">Ingresa un correo válido.</div>
                        </div>

                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave"
                                   autocomplete="current-password" required>
                            <div class="invalid-feedback">Ingresa tu contraseña.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/login.js"></script>
</body>
</html>
