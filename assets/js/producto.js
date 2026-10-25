
const form = document.getElementById('form-producto');


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


Object.keys(reglas).forEach(nombre => {
    const input = form.elements[nombre];
    if (input) input.addEventListener('input', () => validarCampo(nombre));
});
