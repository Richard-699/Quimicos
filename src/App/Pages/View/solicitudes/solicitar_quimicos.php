<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Químicos</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../../../../../public/css/solicitudes/solicitar_quimicos.css">
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
        <h2 class="title">Solicitar Químicos</h2><br>
        <form id="formSolicitudQuimico">
            <div class="row">
                <div class="col-12 form-group mt-5">
                    <input type="text" class="custom-input" id="cedula_solicitante" placeholder=" " name="cedula_solicitante">
                    <label for="cedula_solicitante" class="floating-label">Cédula: *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="nombres_solicitante_consumo" placeholder=" " name="nombres_solicitante_consumo">
                    <label for="inputUsername" class="floating-label">Nombre(s): *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="apellidos_solicitante_consumo" placeholder=" " name="apellidos_solicitante_consumo">
                    <label for="apellidos_solicitante_consumo" class="floating-label">Apellido(s): *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <select class="custom-input" id="id_celula_area_solicitud_consumo" name="id_celula_area_solicitud_consumo">
                        <option value="" disabled>Seleccione una célula</option>
                    </select>
                    <label for="id_celula_area_solicitud_consumo" class="floating-label">Células: *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <select class="custom-input" id="id_quimico_solicitud_consumo" name="id_quimico_solicitud_consumo">
                    </select>
                    <label for="id_quimico_solicitud_consumo" class="floating-label">Químicos: *</label>
                </div>
                <div class="col-12 form-group mt-3">
                    <input type="text" class="custom-input" id="cantidad_solicitud_consumo" placeholder=" " name="cantidad_solicitud_consumo">
                    <label for="cantidad_solicitud_consumo" class="floating-label">Cantidad: *</label>
                </div>
                <div class="d-grid mb-4 justify-content-center">
                    <button type="submit" value="Ingresar" name="btningresar" class="btn btn-success align-items-center w-100 mx-auto" id="btningresar">
                        <span class="align-middle">Solicitar</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/datatables.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <!-- JS Choices -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Scripts funcionalidades -->
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/solicitudes/solicitar_quimicos.js"></script>
</body>

</html>