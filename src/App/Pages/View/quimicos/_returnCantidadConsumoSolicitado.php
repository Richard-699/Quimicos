<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../../../public/css/quimicos/returnCantidadConsumoSolicitado.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body class="p-4">
    <div class="contenido_return-cantidad-consumo">
        <h5 class="mb-4"><i class="fas fa-undo"></i>
            Retornar Cantidad de Consumo Solicitado
        </h5>
        
        <form id="formReturnCantidadConsumo"> 
            <input type="hidden" id="id_solicitud_consumo" name="id_solicitud_consumo">
            <input type="hidden" id="id_quimico" name="id_quimico">
            <input type="hidden" id="cantidad_solicitud_consumo" name="cantidad_solicitud_consumo">
            <input type="hidden" id="cantidad_disponible_quimico" name="cantidad_disponible_quimico">
            
            <div class="alert alert-info py-2 px-3 mb-3" role="alert">
                <i class="fa-solid fa-info-circle me-1"></i>
                Cantidad solicitada originalmente: 
                <strong id="lbl_cantidad_solicitada">0</strong>
            </div>

            <div class="mb-3 mt-3">
                <label for="cantidad_retorno_consumo" class="form-label">¿Cuál es la cantidad que deseas retornar?: *</label>
                <input type="text" class="form-control double-input" id="cantidad_retorno_consumo" name="cantidad_retorno_consumo">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary" id="btn-actualizar">
                    <i class="bi bi-check-circle me-1"></i>Retornar Cantidad
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/returnCantidadConsumoSolicitado.js"></script>
</body>

</html>