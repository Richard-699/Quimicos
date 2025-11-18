<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Inventario HWI</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.6/dist/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_login.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <?php
    include('../../../Shared/Util/spinner.php');
    ?>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-12 bg-white rounded shadow p-0">
                <div class="row g-0">
                    <div class="col-12 col-md-6 bg d-none d-md-block rounded-start" style="min-height: 300px;">
                    </div>
                    <div class="col-12 col-md-6 p-4 p-md-5 rounded-end">
                        <!-- Solo sale en pantallas pequeñas: -->
                        <div class="d-block d-md-none text-start mb-3">
                            <img src="../../../../../public/img/LogoBlanco.png" alt="Logo HWI" style="height: 35px;">
                        </div>
                        <h2 class="fw-bold text-center py-4">Iniciar Sesión</h2>
                        <form id="formLogin">
                            <div class="mb-4 form-group mt-4">
                                <input autocomplete="off" type="text" class="custom-input" id="correo_hwi_administrador" name="correo_hwi_administrador" placeholder=" ">
                                <label for="correo_hwi_administrador" class="floating-label">Correo Corporativo</label>
                            </div>
                            <div class="mb-2 form-group position-relative">
                                <input autocomplete="off" type="password" class="custom-input" id="password_administrador" name="password_administrador" placeholder=" ">
                                <label for="password_administrador" class="floating-label">Contraseña</label>
                                <button class="password-toggle" id="passwordToggle" type="button" onclick="togglePassword('password_administrador')">
                                    <i class="material-icons" id="passwordIcon">visibility</i>
                                </button>
                            </div>
                            <div class="d-grid mb-4">
                                <button type="submit" value="Ingresar" name="btningresar" class="btn btn-success w-100" id="btningresar">
                                    <i class="material-icons align-middle" style="font-size: 24px;"></i>
                                    <span class="align-middle">Ingresar</span>
                                </button>
                            </div>
                            <p class="text-center mb-1">
                                <a href="validate_email.php">¿Haz olvidado la contraseña?</a>
                            </p>
                            <p class="text-center">¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/auth/login.js"></script>
</body>

</html>