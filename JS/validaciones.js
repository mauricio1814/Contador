function soloLetrasTecla(e) {
    e.target.value = e.target.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúñÑ\s]/g, "");
}

function validarSoloLetras(valor) {
    if (valor.trim() === "") return true;
    return /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor.trim());
}

function soloNumerosTecla(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, "");
}

function validarSoloNumeros(valor) {
    if (valor.trim() === "") return true;
    return /^[0-9]+$/.test(valor);
}

function validarCorreo(valor) {
    if (valor.trim() === "") return true;
    const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$/;
    return regexCorreo.test(valor.trim());
}

document.addEventListener("DOMContentLoaded", () => {

    // VALIDACIÓN EN TIEMPO REAL
    document.querySelectorAll(".solo-letras").forEach(input => {
        input.addEventListener("input", soloLetrasTecla);
        input.addEventListener("input", () => {
            validarSoloLetras(input.value) ?
                input.classList.remove("is-invalid") || input.classList.add("is-valid") :
                input.classList.add("is-invalid");
        });
    });

    document.querySelectorAll(".solo-numeros").forEach(input => {
        input.addEventListener("input", soloNumerosTecla);
        input.addEventListener("input", () => {
            validarSoloNumeros(input.value) ?
                input.classList.remove("is-invalid") || input.classList.add("is-valid") :
                input.classList.add("is-invalid");
        });
    });

    document.querySelectorAll(".correo").forEach(input => {
        input.addEventListener("input", () => {
            validarCorreo(input.value) ?
                input.classList.remove("is-invalid") || input.classList.add("is-valid") :
                input.classList.add("is-invalid");
        });
    });

    // VALIDAR AL ENVIAR FORMULARIO
    document.querySelectorAll("form").forEach(form => {

        form.addEventListener("submit", function(e) {
            let error = false;
            let mensaje = "";

            form.querySelectorAll(".solo-letras").forEach(campo => {
                if (!validarSoloLetras(campo.value)) {
                    error = true;
                    mensaje = "El campo '" + (campo.placeholder || campo.name) + "' solo permite letras.";
                    campo.classList.add("is-invalid");
                }
            });

            form.querySelectorAll(".solo-numeros").forEach(campo => {
                if (!validarSoloNumeros(campo.value)) {
                    error = true;
                    mensaje = "El campo '" + (campo.placeholder || campo.name) + "' solo permite números.";
                    campo.classList.add("is-invalid");
                }
            });

            form.querySelectorAll(".correo").forEach(campo => {
                if (!validarCorreo(campo.value)) {
                    error = true;
                    mensaje = "El correo electrónico no es válido.";
                    campo.classList.add("is-invalid");
                }
            });

            if (error) {
                e.preventDefault();
                Swal.fire({
                    icon: "error",
                    title: "Datos inválidos",
                    text: mensaje,
                    confirmButtonColor: "#4a5bf6"
                });
                return;
            }


        });

    });

});