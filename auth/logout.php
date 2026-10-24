<?php
require __DIR__ . '/../includes/auth.php';
iniciar_sesion();

$_SESSION = [];       
session_destroy();    

header('Location: ' . BASE_URL . '/auth/login.php');
exit;
