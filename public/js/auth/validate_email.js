window.addEventListener('load', function () {
    const inputEmail = document.getElementById('inputEmail');
    const errorEmail = document.getElementById('errorEmail');
    const submitBtn = document.getElementById('validate_email_btn');

    function validarEmail() {
        const email = inputEmail ? inputEmail.value.trim() : '';
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            errorEmail.textContent = '';
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        if (regex.test(email)) {
            errorEmail.textContent = '';
            if (submitBtn) submitBtn.disabled = false;
        } else {
            errorEmail.textContent = 'Ingrese un correo electrónico válido.';
            if (submitBtn) submitBtn.disabled = true;
        }
    }

    if (inputEmail) {
        inputEmail.addEventListener('input', validarEmail);
        inputEmail.addEventListener('blur', validarEmail);
    }

    $('#formValidateEmail').on('submit', function (e) {
        e.preventDefault();
        mostrarCarga();

        const form = this;
        const formData = new FormData(form);

        validateEmail();

        async function validateEmail() {
            try {
                const response = await fetch('../../Handler/auth/validateEmailHandler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Error en la solicitud: ' + response.statusText);
                }

                const result = await response.json();

                if (result.success) {
                    notification('success', 'Se envió a su correo la contraseña temporal.', 2500);
                    setTimeout(function () {
                        window.location.href = 'login.php';
                    }, 2500);
                } else {
                    notification('error', result.message || 'Error al validar el correo.', 3000);
                }
            } catch (error) {
                notification('error', 'Error al conectar con el servidor.', 3000);
            } finally {
                ocultarCarga();
            }
        }
    });
});
