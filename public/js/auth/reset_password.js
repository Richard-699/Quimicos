function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);
    if (icon) {
        icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
    }
}
window.togglePassword = togglePassword;

window.addEventListener('load', function () {
    const passwordInput = document.getElementById('inputPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
    const errorSpan = document.getElementById('errorConfirmPassword');
    const submitButton = document.getElementById('btnGuardar');

    function togglePasswordIcon() {
        if (passwordToggle && passwordInput) {
            passwordToggle.style.display = passwordInput.value.length > 0 ? 'block' : 'none';
        }
        if (confirmPasswordToggle && confirmPasswordInput) {
            confirmPasswordToggle.style.display = confirmPasswordInput.value.length > 0 ? 'block' : 'none';
        }
    }

    function validarContrasena() {
        const inputPassword = passwordInput ? passwordInput.value : '';
        const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

        if (!inputPassword && !confirmPassword) {
            errorSpan.textContent = '';
            submitButton.setAttribute('disabled', 'disabled');
            return;
        }

        if (inputPassword.length < 6) {
            errorSpan.textContent = 'La contraseña debe tener al menos 6 caracteres.';
            submitButton.setAttribute('disabled', 'disabled');
            return;
        }

        if (inputPassword !== confirmPassword) {
            errorSpan.textContent = 'Las contraseñas no coinciden.';
            submitButton.setAttribute('disabled', 'disabled');
            return;
        }

        errorSpan.textContent = '';
        submitButton.removeAttribute('disabled');
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', togglePasswordIcon);
        passwordInput.addEventListener('input', validarContrasena);
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', togglePasswordIcon);
        confirmPasswordInput.addEventListener('input', validarContrasena);
    }
});

$('#formResetPassword').on('submit', function (e) {
    e.preventDefault();
    mostrarCarga();

    const form = this;
    const formData = new FormData(form);

    resetPassword();

    async function resetPassword() {
        try {
            const response = await fetch('../../Handler/auth/resetPasswordHandler.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Error en la solicitud: ' + response.statusText);
            }

            const result = await response.json();

            if (result.success) {
                notification('success', 'Se actualizó la contraseña correctamente. Inicie sesión nuevamente.', 2500);
                setTimeout(function () {
                    window.location.href = 'login.php';
                }, 2500);
            } else {
                notification('error', result.message || 'Error al cambiar la contraseña.', 3000);
            }
        } catch (error) {
            notification('error', 'Error al conectar con el servidor.', 2500);
        } finally {
            ocultarCarga();
        }
    }
});
