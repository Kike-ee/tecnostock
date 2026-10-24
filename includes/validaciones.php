<?php
// includes/validaciones.php - Validación de datos de producto (lado servidor).
// Devuelve [datos, errores]. Los datos quedan como texto limpio para volver a mostrarlos.
function validar_producto(array $in, bool $esNuevo): array {
    $d = [
        'codigo'        => strtoupper(trim($in['codigo'] ?? '')),
        'nombre'        => trim($in['nombre'] ?? ''),
        'descripcion'   => trim($in['descripcion'] ?? ''),
        'precio'        => trim($in['precio'] ?? ''),
        'stock_minimo'  => trim($in['stock_minimo'] ?? ''),
        'stock_inicial' => trim($in['stock_inicial'] ?? '0'),
        'id_categoria'  => trim($in['id_categoria'] ?? ''),
    ];
    $err = [];

    if (!preg_match('/^[A-Z0-9\-]{3,30}$/', $d['codigo'])) {
        $err['codigo'] = 'Usa entre 3 y 30 caracteres: letras, números y guiones.';
    }
    if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 120) {
        $err['nombre'] = 'El nombre es obligatorio (máximo 120 caracteres).';
    }
    if (mb_strlen($d['descripcion']) > 255) {
        $err['descripcion'] = 'La descripción admite hasta 255 caracteres.';
    }
    if (!is_numeric($d['precio']) || (float) $d['precio'] < 0 || (float) $d['precio'] > 99999999.99) {
        $err['precio'] = 'Ingresa un precio válido (0 o más).';
    }
    if (filter_var($d['stock_minimo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $err['stock_minimo'] = 'El stock mínimo debe ser un entero de 0 o más.';
    }
    if ($esNuevo && filter_var($d['stock_inicial'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $err['stock_inicial'] = 'El stock inicial debe ser un entero de 0 o más.';
    }
    if (filter_var($d['id_categoria'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
        $err['id_categoria'] = 'Selecciona una categoría.';
    }
    return [$d, $err];
}
