<?php
require __DIR__ . '/../includes/auth.php';
iniciar_sesion();

$_SESSION = [];       // vacía los datos de la sesión
session_destroy();    // elimina la sesión en el servidor

header('Location: ' . BASE_URL . '/auth/login.php');
exit;
