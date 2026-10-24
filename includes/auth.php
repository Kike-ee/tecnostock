<?php

const BASE_URL = '/tecnostock';   

function iniciar_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,    
            'samesite' => 'Lax',   
        ]);
        session_start();
    }
}


function requiere_login(): void {
    iniciar_sesion();
    if (empty($_SESSION['id_usuario'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}


function e(?string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}


function csrf_token(): string {
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido(?string $token): bool {
    iniciar_sesion();
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}


function flash(string $tipo, string $mensaje): void {
    iniciar_sesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function tomar_flash(): ?array {
    iniciar_sesion();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
