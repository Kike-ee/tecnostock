// Validación inmediata en el navegador (el servidor la repite en PHP).
const form   = document.getElementById('form-login');
const correo = document.getElementById('correo');
const clave  = document.getElementById('clave');

form.addEventListener('submit', function (evento) {
    let valido = true;

    // Correo: no vacío y con formato de correo
    if (!correo.value.trim() || !correo.checkValidity()) {
        correo.classList.add('is-invalid');
        valido = false;
    } else {
        correo.classList.remove('is-invalid');
    }

    // Contraseña: no vacía
    if (clave.value === '') {
        clave.classList.add('is-invalid');
        valido = false;
    } else {
        clave.classList.remove('is-invalid');
    }

    if (!valido) {
        evento.preventDefault();   // no envía el formulario al servidor
    }
});
