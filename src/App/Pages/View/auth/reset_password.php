<?php
require_once __DIR__ . '/../../../../../vendor/autoload.php';

session_start();
$email = $_SESSION['administrador']->correo_hwi_administrador;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.6/dist/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_reset_password.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <?php
        include('../../../Shared/Util/spinner.php');
    ?>
</head>

<body>
    <div class="containerChangePassword">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="material-icons" style="margin-top:20px;">vpn_key</i>
                        <p class="titleContraseña">Cambiar Contraseña</p>
                        <p class="subtitleContraseña">Se recomienda usar una contraseña segura que no uses para ningún otro sitio.</p>
                    </div>
                    <div class="card-body">
                        <form id="formResetPassword">
                            <div class="row g-3 mt-4">
                                <input type="hidden" value="<?php echo $email; ?>" name="correo_hwi_administrador">
                                <div class="col-12 form-group">
                                    <input type="password" class="custom-input" id="inputPassword" placeholder=" ">
                                    <label for="inputUsername" class="floating-label">Nueva Contraseña</label>
                                    <button class="password-toggle" id="passwordToggle" type="button" onclick="togglePassword('inputPassword')">
                                        <i class="material-icons" id="passwordIcon">visibility</i>
                                    </button>
                                </div>
                                <div class="col-12 form-group">
                                    <input type="password" class="custom-input" id="confirmPassword" placeholder=" " name="password_administrador">
                                    <label for="inputUsername" class="floating-label">Confirmar Contraseña</label>
                                    <button class="password-toggle" id="confirmPasswordToggle" type="button" onclick="togglePassword('confirmPassword')">
                                        <i class="material-icons" id="confirmPasswordIcon">visibility</i>
                                    </button>
                                </div>
                                <span id="errorConfirmPassword" style="color: red; margin-top:-30px; margin-left: 60px"></span>
                                <div class="col-12 form-group">
                                    <button disabled type="submit" class="btn btn-success">Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../../public/js/auth/reset_password.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
</body>

</html>