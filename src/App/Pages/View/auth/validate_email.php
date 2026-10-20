<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Químicos HWI</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_validate_email.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php
        include('../../../Shared/Util/spinner.php');
    ?>
</head>

<body>
    <div class="validate-card">
        <div class="header-section">
            <img src="../../../../../public/img/LogoBlanco.png" alt="HWI Logo" class="logo">
            <h3 class="title">Restablecer <strong>Contraseña</strong></h3>
            <p class="subtitle">Ingresa tu correo corporativo y te enviaremos una contraseña temporal de acceso.</p>
        </div>

        <form id="formValidateEmail">
            <div class="form-group">
                <input type="email" class="custom-input" id="inputEmail" placeholder=" " name="correo_hwi_administrador" required autocomplete="email">
                <label for="inputEmail" class="floating-label">Correo Corporativo: *</label>
            </div>
            
            <span id="errorEmail" class="error-message"></span>

            <div class="d-grid mt-2 mb-3">
                <button id="validate_email_btn" disabled type="submit" class="btn-restablecer">
                    Restablecer Contraseña
                </button>
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
    <script src="../../../../../public/js/auth/validate_email.js"></script>
</body>

</html>