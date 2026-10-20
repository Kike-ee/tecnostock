<?php
// includes/auth.php - Funciones de sesión y protección de páginas
const BASE_URL = '/tecnostock';   // carpeta del proyecto dentro de htdocs

function iniciar_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,    // JavaScript no puede leer la cookie de sesión
            'samesite' => 'Lax',   // el navegador no la envía desde otros sitios
        ]);
        session_start();
    }
}

// Se llama al inicio de cada página privada: si no hay login, manda al formulario.
function requiere_login(): void {
    iniciar_sesion();
    if (empty($_SESSION['id_usuario'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Escapa texto antes de mostrarlo en HTML (evita XSS).
function e(?string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
