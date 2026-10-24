<?php

function cls(array $errores, string $campo): string {
    return isset($errores[$campo]) ? ' is-invalid' : '';
}
?>
<h1 class="h3 mb-4"><?= e($titulo) ?></h1>

<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger" role="alert"><?= e($errores['general']) ?></div>
<?php endif; ?>

<form id="form-producto" method="post" action="" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="codigo" class="form-label">Código</label>
            <input type="text" class="form-control<?= cls($errores, 'codigo') ?>" id="codigo" name="codigo"
                   value="<?= e($datos['codigo']) ?>" maxlength="30" required>
            <div class="invalid-feedback"><?= e($errores['codigo'] ?? '') ?></div>
        </div>
        <div class="col-md-8">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control<?= cls($errores, 'nombre') ?>" id="nombre" name="nombre"
                   value="<?= e($datos['nombre']) ?>" maxlength="120" required>
            <div class="invalid-feedback"><?= e($errores['nombre'] ?? '') ?></div>
        </div>
        <div class="col-12">
            <label for="descripcion" class="form-label">Descripción <span class="text-muted">(opcional)</span></label>
            <input type="text" class="form-control<?= cls($errores, 'descripcion') ?>" id="descripcion" name="descripcion"
                   value="<?= e($datos['descripcion']) ?>" maxlength="255">
            <div class="invalid-feedback"><?= e($errores['descripcion'] ?? '') ?></div>
        </div>
        <div class="col-md-4">
            <label for="id_categoria" class="form-label">Categoría</label>
            <select class="form-select<?= cls($errores, 'id_categoria') ?>" id="id_categoria" name="id_categoria" required>
                <option value="">Selecciona...</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c['id_categoria'] ?>"
                        <?= (string) $c['id_categoria'] === (string) $datos['id_categoria'] ? 'selected' : '' ?>>
                        <?= e($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errores['id_categoria'] ?? '') ?></div>
        </div>
        <div class="col-md-4">
            <label for="precio" class="form-label">Precio ($)</label>
            <input type="number" class="form-control<?= cls($errores, 'precio') ?>" id="precio" name="precio"
                   value="<?= e($datos['precio']) ?>" min="0" step="0.01" required>
            <div class="invalid-feedback"><?= e($errores['precio'] ?? '') ?></div>
        </div>
        <div class="col-md-4">
            <label for="stock_minimo" class="form-label">Stock mínimo</label>
            <input type="number" class="form-control<?= cls($errores, 'stock_minimo') ?>" id="stock_minimo" name="stock_minimo"
                   value="<?= e($datos['stock_minimo']) ?>" min="0" step="1" required>
            <div class="invalid-feedback"><?= e($errores['stock_minimo'] ?? '') ?></div>
        </div>

        <?php if ($esNuevo): ?>
        <div class="col-md-4">
            <label for="stock_inicial" class="form-label">Stock inicial</label>
            <input type="number" class="form-control<?= cls($errores, 'stock_inicial') ?>" id="stock_inicial" name="stock_inicial"
                   value="<?= e($datos['stock_inicial']) ?>" min="0" step="1">
            <div class="invalid-feedback"><?= e($errores['stock_inicial'] ?? '') ?></div>
            <div class="form-text">Si es mayor a 0, se registra como una entrada.</div>
        </div>
        <?php else: ?>
        <div class="col-md-4">
            <label class="form-label">Stock actual</label>
            <input type="text" class="form-control" value="<?= (int) $stockActual ?>" disabled>
            <div class="form-text">Cambia solo con movimientos de entrada o salida.</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= BASE_URL ?>/productos/listar.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
