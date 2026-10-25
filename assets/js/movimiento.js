// Validación en el navegador del formulario de movimientos.
// Las mismas reglas se repiten en PHP (includes/validaciones.php y movimientos/registrar.php).
const form = document.getElementById('form-movimiento');

// Stock del producto elegido (se lee del atributo data-stock de la opción seleccionada).
function stockSeleccionado() {
    const opcion = form.elements['id_producto'].selectedOptions[0];
    return opcion && opcion.dataset.stock !== undefined ? Number(opcion.dataset.stock) : null;
}

const reglas = {
    id_producto: v => v !== ''
        ? '' : 'Selecciona un producto.',
    tipo: v => (v === 'ENTRADA' || v === 'SALIDA')
        ? '' : 'Selecciona el tipo de movimiento.',
    cantidad: v => {
        if (!/^\d+$/.test(v) || Number(v) < 1 || Number(v) > 1000000) {
            return 'La cantidad debe ser un entero entre 1 y 1.000.000.';
        }
        const stock = stockSeleccionado();
        if (form.elements['tipo'].value === 'SALIDA' && stock !== null && Number(v) > stock) {
            return 'Stock insuficiente: hay ' + stock + ' unidad(es) disponible(s).';
        }
        return '';
    },
    observacion: v => v.length <= 255
        ? '' : 'La observación admite hasta 255 caracteres.',
};

function validarCampo(nombre) {
    const input = form.elements[nombre];
    if (!input || !reglas[nombre]) return true;
    const mensaje = reglas[nombre](input.value);
    const aviso = input.parentElement.querySelector('.invalid-feedback');
    input.classList.toggle('is-invalid', mensaje !== '');
    if (aviso) aviso.textContent = mensaje;
    return mensaje === '';
}

form.addEventListener('submit', function (evento) {
    let valido = true;
    Object.keys(reglas).forEach(nombre => {
        if (!validarCampo(nombre)) valido = false;
    });
    if (!valido) {
        evento.preventDefault();
        const primero = form.querySelector('.is-invalid');
        if (primero) primero.focus();
    }
});

// Validación en vivo mientras se escribe o se cambia una opción.
Object.keys(reglas).forEach(nombre => {
    const input = form.elements[nombre];
    if (input) input.addEventListener('input', () => validarCampo(nombre));
});

// Si cambia el producto o el tipo, la cantidad se vuelve a revisar (depende del stock).
['id_producto', 'tipo'].forEach(nombre => {
    form.elements[nombre].addEventListener('change', () => {
        if (form.elements['cantidad'].value !== '') validarCampo('cantidad');
    });
});
