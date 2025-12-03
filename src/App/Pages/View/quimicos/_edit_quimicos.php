<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../../../public/css/quimicos/edit_quimicos.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body class="p-4">
    <?php

    $umbs = [];

    if (isset($_GET['umbs'])) {
        $umbsJson = $_GET['umbs'];
        $umbsDecoded = json_decode($umbsJson);

        if (json_last_error() === JSON_ERROR_NONE && is_array($umbsDecoded)) {
            $umbs = $umbsDecoded;
        } else {
            error_log('Error decodificando umbs');
        }
    }

    $celulasAreas = [];

    if (isset($_GET['celulasAreas'])) {
        $celulasAreasJson = $_GET['celulasAreas'];
        $celulasAreasDecoded = json_decode($celulasAreasJson);

        if (json_last_error() === JSON_ERROR_NONE && is_array($celulasAreasDecoded)) {
            $celulasAreas = $celulasAreasDecoded;
        } else {
            error_log('Error decodificando celulasAreas');
        }
    }

    $celulasAreasSelectedJson = $_GET['celulasAreasSelected'] ?? null;
    $celulasAreasSelected = [];

    if ($celulasAreasSelectedJson) {
        $celulasAreasSelectedDecoded = json_decode($celulasAreasSelectedJson, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($celulasAreasSelectedDecoded)) {
            $celulasAreasSelected = $celulasAreasSelectedDecoded;
        } else {
            error_log('Error decodificando celulasAreasSelected');
        }
    }

    $idscelulasAreasSelected = [];
    foreach ($celulasAreasSelected as $ca_selected) {
        if (isset($ca_selected['id_celulas_areas_quimicos'])) {
            $idscelulasAreasSelected[] = (int)$ca_selected['id_celulas_areas_quimicos'];
        }
    }

    $quimicoSelectedJson = $_GET['quimicoSelected'] ?? null;
    $quimicoSelected = [];

    if ($quimicoSelectedJson) {
        $quimicoSelectedDecoded = json_decode($quimicoSelectedJson);

        if (json_last_error() === JSON_ERROR_NONE && is_object($quimicoSelectedDecoded)) {
            $quimicoSelected = $quimicoSelectedDecoded;
        } else {
            error_log('Error decodificando quimicoSelected');
        }
    }

    $id_quimico = $_GET['id'] ?? null;

    ?>
    <div class="contenido_edit_quimicos">
        <h5 class="mb-4"><i class="fa-solid fa-flask"></i>
            Editar Químico
        </h5>
        <form id="formUpdateQuimicos">
            <input type="hidden" name="id_quimico" id="id_quimico" value="<?= htmlspecialchars($id_quimico) ?>">

            <div class="mb-3">
                <label for="descripcion_quimico" class="form-label">Nombre Químico: *</label>
                <input type="text" class="form-control" id="descripcion_quimico" name="descripcion_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->descripcion_quimico ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="id_umb_quimico" class="form-label">UMB: *</label>
                <select class="form-select" id="id_umb_quimico" name="id_umb_quimico">
                    <option value="" selected disabled>Seleccione un tipo</option>
                    <?php
                    if (!empty($umbs)) {
                        $selectedId = $quimicoSelected->id_umb_quimico ?? '';

                        foreach ($umbs as $umb) {
                            $id = $umb->id_umb ?? '';
                            $descripcion = $umb->descripcion_umb ?? 'N/A';
                            $selected = ($selectedId == $id) ? 'selected' : '';
                            echo "<option value='{$id}' {$selected}>{$descripcion}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="cantidad_disponible_quimico" class="form-label">Cantidad Disponible: *</label>
                <input type="text" class="form-control double-input" id="cantidad_disponible_quimico" name="cantidad_disponible_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->cantidad_disponible_quimico ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="cantidad_maxima_retiro_quimico" class="form-label">Cantidad Máxima Retiro: *</label>
                <input type="text" class="form-control double-input" id="cantidad_maxima_retiro_quimico" name="cantidad_maxima_retiro_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->cantidad_maxima_retiro_quimico ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="tope_minimo_quimico" class="form-label">Tope Mínimo: *</label>
                <input type="text" class="form-control double-input" id="tope_minimo_quimico" name="tope_minimo_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->tope_minimo_quimico ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="precio_quimico" class="form-label">Precio (und): *</label>
                <input type="text" class="form-control double-input" id="precio_quimico" name="precio_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->precio_quimico ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="url_etiqueta_emergencia_quimico" class="form-label">Url Etiqueta Emergencia: *</label>
                <input type="text" class="form-control double-input" id="url_etiqueta_emergencia_quimico" name="url_etiqueta_emergencia_quimico"
                    value="<?= htmlspecialchars($quimicoSelected->url_etiqueta_emergencia_quimico ?? '') ?>">
            </div>
            <div class="mb-4">
                <label for="celulas_areas_quimicos" class="form-label">
                    Células autorizadas: *
                </label>

                <select id="celulas_areas_quimicos"
                    class="form-control shadow-sm rounded"
                    multiple
                    name="celulas_areas_quimicos">
                    <?php foreach ($celulasAreas as $c):
                        $id_celulas_areas = (int)$c->id_celulas_areas;
                        $isSelected = in_array($id_celulas_areas, $idscelulasAreasSelected);
                    ?>
                        <option value="<?= $id_celulas_areas ?>" <?= $isSelected ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->nombre_celula) ?>
                        </option>
                    <?php endforeach; ?>
                </select>


                <div class="form-text mt-1">
                    Puedes buscar y seleccionar múltiples opciones.
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" id="btn-editar">
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
    <script src="../../../../../public/js/utils/libs/select2.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/edit_quimicos.js"></script>
</body>

</html>