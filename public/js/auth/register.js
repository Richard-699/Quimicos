document.addEventListener('DOMContentLoaded', async function () {
    const passwordInput = document.getElementById('inputPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    const cedula_administrador = document.getElementById('cedula_administrador');

    function togglePasswordIcon() {
        passwordToggle.style.display = passwordInput.value.length > 0 ? 'block' : 'none';
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);
        icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
    }

    if (passwordInput) passwordInput.addEventListener('input', togglePasswordIcon);

    if (cedula_administrador) {
        cedula_administrador.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    window.togglePassword = togglePassword;

    const form = document.getElementById('formRegistro');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        mostrarCarga();

        const formData = new FormData(form);

        try {
            const response = await fetch('../../Handler/auth/registerHandler.php', {
                method: 'POST',
                body: formData
            });

            if (!response) {
                throw new Error('Error en la solicitud: ' + response.statusText);
            }

            const result = await response.json();

            ocultarCarga();

            if (result.success) {
                notification('alert', 'Registro exitoso, debes esperar la aprobación.', 3000);
                setTimeout(() => window.location.href = 'login.php', 4000);
            } else if (result.status) {
                notification('error', 'Este correo ya se encuentra registrado, inicie sesión.', 3000);
                setTimeout(() => window.location.href = 'login.php', 4000);
            } else {
                notification('error', result.message, 3000);
            }
        } catch (error) {
            ocultarCarga();
            notification('error', error, 3000);
            console.error('Error:', error);
        }
    });
});
