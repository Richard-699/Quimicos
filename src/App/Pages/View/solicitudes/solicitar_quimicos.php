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

    <div class="main-card">
        <div class="header-section">
            <img src="../../../../../public/img/LogoBlanco.png" alt="HWI Logo">
            <h2 class="fw-bold">Solicitar Químicos</h2>
        </div>

        <form id="formSolicitudQuimico">
            <div class="row g-3"> <div class="col-md-6">
                    <label class="form-label-custom">Cédula: *</label>
                    <input type="text" class="form-control custom-input" id="cedula_solicitante" name="cedula_solicitante">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label-custom">Nombre(s): *</label>
                    <input type="text" class="form-control custom-input" id="nombres_solicitante_consumo" name="nombres_solicitante_consumo">
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Apellido(s): *</label>
                    <input type="text" class="form-control custom-input" id="apellidos_solicitante_consumo" name="apellidos_solicitante_consumo">
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Células: *</label>
                    <select class="form-select custom-input" id="id_celula_area_solicitud_consumo" name="id_celula_area_solicitud_consumo">
                        <option value="" selected disabled>Seleccione una célula</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Químicos: *</label>
                    <select class="form-select custom-input" id="id_quimico_solicitud_consumo" name="id_quimico_solicitud_consumo">
                        </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label-custom">Cantidad: *</label>
                    <input type="number" class="form-control custom-input" id="cantidad_solicitud_consumo" name="cantidad_solicitud_consumo">
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-success btn-request" id="btningresar">
                        <i class="material-icons align-middle">send</i>
                        <span class="align-middle">Solicitar</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="../../../../../public/js/solicitudes/solicitar_quimicos.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
</body>

</html>