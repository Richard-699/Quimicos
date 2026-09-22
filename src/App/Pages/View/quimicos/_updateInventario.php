<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../../../public/css/quimicos/updateInventario.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body class="p-4">
    <div class="contenido_update_inventario">
        <h5 class="mb-4"><i class="fa-solid fa-warehouse"></i>
            Actualizar Inventario de Químico
        </h5>
        
        <form id="formUpdateInventario">
            <input type="hidden" id="id_quimico_ingreso_inventario" name="id_quimico_ingreso_inventario">
            <input type="hidden" id="cantidad_actual_inventario" name="cantidad_actual_inventario">

            <div class="alert alert-info py-2 px-3 mb-3" role="alert">
                <i class="fa-solid fa-info-circle me-1"></i>
                Cantidad actual: 
                <strong id="lbl_cantidad_actual">0</strong>
            </div>

            <div class="mb-3 mt-3">
                <label for="cantidad_ingreso_inventario" class="form-label">¿Cuánto deseas ingresar?: *</label>
                <input type="text" class="form-control double-input" id="cantidad_ingreso_inventario" name="cantidad_ingreso_inventario">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary" id="btn-actualizar">
                    <i class="bi bi-check-circle me-1"></i>Ingresar
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
    <script src="../../../../../public/js/quimicos/updateInventario.js"></script>
</body>

</html>