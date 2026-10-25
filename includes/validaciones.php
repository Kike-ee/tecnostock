<?php

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

function validar_movimiento(array $in): array {
    $d = [
        'id_producto' => trim($in['id_producto'] ?? ''),
        'tipo'        => strtoupper(trim($in['tipo'] ?? '')),
        'cantidad'    => trim($in['cantidad'] ?? ''),
        'observacion' => trim($in['observacion'] ?? ''),
    ];
    $err = [];

    if (filter_var($d['id_producto'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
        $err['id_producto'] = 'Selecciona un producto.';
    }
    if (!in_array($d['tipo'], ['ENTRADA', 'SALIDA'], true)) {
        $err['tipo'] = 'Selecciona el tipo de movimiento.';
    }
    if (filter_var($d['cantidad'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]) === false) {
        $err['cantidad'] = 'La cantidad debe ser un entero entre 1 y 1.000.000.';
    }
    if (mb_strlen($d['observacion']) > 255) {
        $err['observacion'] = 'La observación admite hasta 255 caracteres.';
    }
    return [$d, $err];
}
