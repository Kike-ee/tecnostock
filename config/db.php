<?php
// config/db.php - Conexión a la base de datos con PDO
$host    = 'localhost';
$db      = 'tecnostock';
$user    = 'root';
$pass    = '';          // XAMPP trae root sin contraseña
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // errores como excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // filas como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                    // consultas preparadas reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('No se pudo conectar a la base de datos.');
}
