const passwordInput = document.getElementById('password_administrador');
const passwordToggle = document.getElementById('passwordToggle');
const passwordIcon = document.getElementById('passwordIcon');

passwordInput.addEventListener('input', togglePasswordIcon);

function togglePasswordIcon() {
    passwordToggle.style.display = passwordInput.value.length > 0 ? 'block' : 'none';
}

function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);
    icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
}


$('#formLogin').on('submit', function (e) {
    mostrarCarga();
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);

    login();

    async function login(){
        try {
            const response = await fetch('../../Handler/auth/loginHandler.php', {
                method: 'POST',
                body: formData
            });
            if (!response) {
                throw new Error('Error en la solicitud: ' + response.statusText);
            }

            const result = await response.json();

            if (result.success && result.is_temporal) {
                window.location.href = 'reset_password.php';
            } else if (result.success && !result.approved) {
                notification('alert', 'Aún no has sido aprobado en el sistema.', 2500);
            }
            else if (result.success && result.approved) {
                window.location.href = '../quimicos/index.php';
            }else{
                notification('error', result.message, 2500);
            }
        } catch (error) {
            console.error('Error en fetch:', error); 
            notification('error', 'Error al conectar con el servidor.', 3000);
        } finally {
            ocultarCarga();
        }
    }
});