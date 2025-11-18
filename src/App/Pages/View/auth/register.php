<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Inventario HWI</title>
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_register.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <?php
    include('../../../Shared/Util/spinner.php');
    ?>
</head>

<body>
    <div class="form-container">
        <img src="../../../../../public/img/LogoBlanco.png" alt="HWI Logo" class="logo">
        <h2 class="title">Registro</h2><br>
        <form id="formRegistro">
            <div class="row">
                <div class="col-12 form-group mt-5">
                    <input type="text" class="custom-input" id="cedula_administrador" placeholder=" " name="cedula_administrador">
                    <label for="cedula_administrador" class="floating-label">Cédula: *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="inputUsername" placeholder=" " name="nombre_administrador">
                    <label for="inputUsername" class="floating-label">Nombre(s): *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="inputUsername" placeholder=" " name="apellidos_administrador">
                    <label for="inputUsername" class="floating-label">Apellido(s): *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="correo_hwi_administrador" placeholder=" " name="correo_hwi_administrador">
                    <label for="correo_hwi_administrador" class="floating-label">Correo Corporativo: *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="password" class="custom-input" name="password_administrador" id="inputPassword" placeholder=" ">
                    <label for="inputPassword" class="floating-label">Crear contraseña: *</label>
                    <button class="password-toggle" id="passwordToggle" type="button" onclick="togglePassword('inputPassword')">
                        <i class="material-icons" id="passwordIcon">visibility</i>
                    </button>
                </div>
                <div class="d-grid mb-4 justify-content-center">
                    <button type="submit" value="Ingresar" name="btningresar" class="btn btn-success align-items-center w-100 mx-auto" id="btningresar">
                        <span class="align-middle">Registrarse</span>
                    </button>
                </div>
            </div>
            <p style="text-align: center; margin: 0 auto;">¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../../public/js/auth/register.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
</body>

</html>