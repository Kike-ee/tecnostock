// Validación del formulario de producto en el navegador (el servidor la repite en PHP).
const form = document.getElementById('form-producto');

// Cada regla devuelve un mensaje de error, o '' si el campo está bien.
const reglas = {
    codigo: v => /^[A-Za-z0-9-]{3,30}$/.test(v.trim())
        ? '' : 'Usa entre 3 y 30 caracteres: letras, números y guiones.',
    nombre: v => (v.trim() !== '' && v.length <= 120)
        ? '' : 'El nombre es obligatorio (máximo 120 caracteres).',
    descripcion: v => v.length <= 255
        ? '' : 'La descripción admite hasta 255 caracteres.',
    id_categoria: v => v !== ''
        ? '' : 'Selecciona una categoría.',
    precio: v => (v !== '' && !isNaN(v) && Number(v) >= 0)
        ? '' : 'Ingresa un precio válido (0 o más).',
    stock_minimo: v => /^\d+$/.test(v)
        ? '' : 'El stock mínimo debe ser un entero de 0 o más.',
    stock_inicial: v => /^\d+$/.test(v)
        ? '' : 'El stock inicial debe ser un entero de 0 o más.',
};

function validarCampo(nombre) {
    const input = form.elements[nombre];
    if (!input || !reglas[nombre]) return true;       // el campo no existe en este formulario
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
        evento.preventDefault();                       // no envía el formulario
        const primero = form.querySelector('.is-invalid');
        if (primero) primero.focus();
    }
});

// Al corregir un campo, el aviso se limpia al instante.
Object.keys(reglas).forEach(nombre => {
    const input = form.elements[nombre];
    if (input) input.addEventListener('input', () => validarCampo(nombre));
});
