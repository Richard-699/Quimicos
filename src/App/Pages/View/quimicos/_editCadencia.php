<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../../../public/css/quimicos/editCadencia.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body class="p-4">
    <?php

        $cadencias = [];

        if (isset($_GET['cadencias'])) {
            $cadenciasJson = $_GET['cadencias'];
            $cadenciasDecoded = json_decode($cadenciasJson);

            if (json_last_error() === JSON_ERROR_NONE && is_array($cadenciasDecoded)) {
                $cadencias = $cadenciasDecoded;
            } else {
                error_log('Error decodificando cadencias');
            }
        }

        $cadenciaActualJson = $_GET['cadenciaActual'] ?? null;
        $cadenciaActual = [];

        if ($cadenciaActualJson) {
            $cadenciaActualDecoded = json_decode($cadenciaActualJson);

            if (json_last_error() === JSON_ERROR_NONE && is_object($cadenciaActualDecoded)) {
                $cadenciaActual = $cadenciaActualDecoded;
            } else {
                error_log('Error decodificando cadenciaActual');
            }
        }

    ?>
    <div class="contenido_edit_cadencia">
        <h5 class="mb-4"><i class="fa-regular fa-clock me-1"></i>
            Actualizar Cadencia
        </h5>
        <form id="formUpdateCadenciaActual">
            <div class="mb-3">
                <label for="id_cadencias_cadencia_actual" class="form-label">Cadencia Actual: *</label>
                <select class="form-select" id="id_cadencias_cadencia_actual" name="id_cadencias_cadencia_actual">
                    <option value="" selected disabled>Seleccione una cadencia</option>
                    <?php
                    if (!empty($cadencias)) {
                        $selectedId = $cadenciaActual->id_cadencias_cadencia_actual ?? '';

                        foreach ($cadencias as $cadencia) {
                            $id = $cadencia->id_cadencia ?? '';
                            $descripcion = $cadencia->cadencia ?? 'N/A';
                            $selected = ($selectedId == $id) ? 'selected' : '';
                            echo "<option value='{$id}' {$selected}>{$descripcion}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" id="btn-actualizar">
                    <i class="bi bi-check-circle me-1"></i>Actualizar
                </button>
            </div>
        </form>
    </div>

    <?php '../shared/footer.php'; ?>
    <!-- Scripts en orden -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>


    <!-- Scripts funcionalidades -->
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/editCadencia.js"></script>
</body>

</html>