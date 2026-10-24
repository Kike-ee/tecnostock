
const form   = document.getElementById('form-login');
const correo = document.getElementById('correo');
const clave  = document.getElementById('clave');

form.addEventListener('submit', function (evento) {
    let valido = true;

    
    if (!correo.value.trim() || !correo.checkValidity()) {
        correo.classList.add('is-invalid');
        valido = false;
    } else {
        correo.classList.remove('is-invalid');
    }

   
    if (clave.value === '') {
        clave.classList.add('is-invalid');
        valido = false;
    } else {
        clave.classList.remove('is-invalid');
    }

    if (!valido) {
        evento.preventDefault();  
    }
});
