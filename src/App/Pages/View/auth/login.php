<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Químicos HWI</title>

    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.6/dist/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_login.css">

    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>

<body>

    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 login-card p-0">
                <div class="row g-0">
                    <div class="col-md-6 bg d-none d-md-block"></div>
                    <div class="col-12 col-md-6 p-4 p-md-5">

                        <div class="d-block d-md-none text-center mb-3">
                            <img src="../../../../../public/img/LogoBlanco.png" style="height: 40px;">
                        </div>

                        <h3 class="fw-bold text-center mb-4">Bienvenido</h3>

                        <form id="formLogin">

                            <div class="form-group">
                                <input type="text" class="custom-input" placeholder=" " name="correo_hwi_administrador" id="correo_hwi_administrador">
                                <label class="floating-label">Correo Corporativo</label>
                            </div>

                            <div class="form-group">
                                <input type="password" class="custom-input" placeholder=" " name="password_administrador" id="password_administrador">
                                <label class="floating-label">Contraseña</label>

                                <button type="button" class="password-toggle" onclick="togglePassword('password_administrador')">
                                    <i class="material-icons">visibility</i>
                                </button>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" id="btningresar">
                                    Ingresar
                                </button>
                            </div>

                            <p class="text-center mb-1">
                                <a href="validate_email.php">¿Olvidaste tu contraseña?</a>
                            </p>

                            <p class="text-center">
                                ¿No tienes cuenta? <a href="register.php">Regístrate</a>
                            </p>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/auth/login.js"></script>

</body>
</html>