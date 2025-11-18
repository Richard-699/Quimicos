window.addEventListener('load', function () {
    const inputEmail = document.getElementById('inputEmail');
    const errorEmail = document.getElementById('errorEmail');
    const submitBtn = document.querySelector('button[type="submit"]');

    inputEmail.addEventListener('input', function () {
        const email = inputEmail.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (regex.test(email)) {
            errorEmail.textContent = "";
            submitBtn.disabled = false;
        } else {
            errorEmail.textContent = "Correo inválido";
            submitBtn.disabled = true;
        }
    });

    $('#formValidateEmail').on('submit', function (e) {
        mostrarCarga();
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);

        validateEmail();

        async function validateEmail(){
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
                    notification('error', result.message, 2500);
                }
            } catch (error) {
                notification('error', 'Error al conectar con el servidor.', 3000);
            } finally {
                ocultarCarga();
            }
        }
    });
});
