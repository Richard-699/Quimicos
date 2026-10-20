<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

$session_dir = __DIR__ . '/../../../../../sessions';
if (!is_dir($session_dir)) {
    @mkdir($session_dir, 0777, true);
}
$session_path = realpath($session_dir);
if ($session_path !== false && is_dir($session_path) && is_writable($session_path)) {
    ini_set('session.save_path', $session_path);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['administrador'])) {
    header('Location: login.php');
    exit;
}

$administrador = $_SESSION['administrador'];
$email = is_object($administrador) ? ($administrador->correo_hwi_administrador ?? '') : ($administrador['correo_hwi_administrador'] ?? '');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Químicos HWI</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_reset_password.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
        include('../../../Shared/Util/spinner.php');
    ?>
</head>

<body>
    <div class="reset-card">
        <div class="header-section">
            <img src="../../../../../public/img/LogoBlanco.png" alt="HWI Logo" class="logo">
            <h3 class="title">Cambiar <strong>Contraseña</strong></h3>
            <p class="subtitle">Se recomienda usar una contraseña segura que no uses para ningún otro sitio.</p>
        </div>

        <form id="formResetPassword">
            <input type="hidden" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" name="correo_hwi_administrador">
            
            <div class="form-group">
                <input type="password" class="custom-input" id="inputPassword" placeholder=" ">
                <label for="inputPassword" class="floating-label">Nueva Contraseña: *</label>
                <button class="password-toggle" id="passwordToggle" type="button" onclick="togglePassword('inputPassword')">
                    <i class="material-icons" id="passwordIcon">visibility</i>
                </button>
            </div>

            <div class="form-group">
                <input type="password" class="custom-input" id="confirmPassword" placeholder=" " name="password_administrador">
                <label for="confirmPassword" class="floating-label">Confirmar Contraseña: *</label>
                <button class="password-toggle" id="confirmPasswordToggle" type="button" onclick="togglePassword('confirmPassword')">
                    <i class="material-icons" id="confirmPasswordIcon">visibility</i>
                </button>
            </div>

            <span id="errorConfirmPassword" class="error-message"></span>

            <div class="d-grid mt-2 mb-3">
                <button disabled type="submit" id="btnGuardar" class="btn-guardar">Guardar Cambios</button>
            </div>

            <p class="text-center mt-3 mb-0">
                <a href="login.php">Cancelar y volver al inicio de sesión</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/auth/reset_password.js"></script>
</body>

</html>