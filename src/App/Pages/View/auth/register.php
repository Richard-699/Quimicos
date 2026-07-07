<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Químicos HWI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../../../../../public/css/auth/estilos_register.css"> </head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
<body>
    <div class="form-container">
        
        <div class="header-section">
            <img src="../../../../../public/img/LogoBlanco.png" alt="HWI Logo" class="logo">
            <h3 class="title">Registro de <strong>Administradores</strong></h3>
        </div>

        <form id="formRegistro">
            <div class="form-group mt-5">
                <input type="text" class="custom-input" id="cedula_administrador" placeholder=" " name="cedula_administrador">
                <label for="cedula_administrador" class="floating-label">Cédula: *</label>
            </div>
        
            <div class="form-group">
                <input type="text" class="custom-input" id="nombre_administrador" placeholder=" " name="nombre_administrador">
                <label for="nombre_administrador" class="floating-label">Nombre: *</label> 
            </div>
            
            <div class="form-group">
                <input type="text" class="custom-input" id="apellidos_administrador" placeholder=" " name="apellidos_administrador">
                <label for="apellidos_administrador" class="floating-label">Apellidos: *</label>
            </div>
            
            <div class="form-group">
                <input type="text" class="custom-input" id="correo_hwi_administrador" placeholder=" " name="correo_hwi_administrador">
                <label for="correo_hwi_administrador" class="floating-label">Correo Corporativo: *</label>
            </div>
            
            <div class="form-group">
                <input type="password" class="custom-input" id="inputPassword" name="password_administrador" placeholder=" ">
                <label for="inputPassword" class="floating-label">Crear contraseña: *</label>
                <button class="password-toggle" id="passwordToggle" type="button" onclick="togglePassword('inputPassword')">
                    <i class="material-icons" id="passwordIcon">visibility</i>
                </button>
            </div>
            
            <div class="d-grid mt-4 mb-3">
                <button type="submit" name="btningresar" class="btn btn-success" id="btningresar">
                    Registrarse
                </button>
            </div>
        </form>
        
        <p class="text-center mt-3 mb-0 text-muted">¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
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