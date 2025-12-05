<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../../../public/css/quimicos/agregar_quimicos.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../public/img/LogoBlanco.png" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">

    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
</head>

<body class="p-4">

    <?php
    if (isset($_GET['celulasAreas'])) {
        $celulasAreasEncoded = $_GET['celulasAreas'];
        $celulasAreasJson = urldecode($celulasAreasEncoded);
        $celulasAreas = json_decode($celulasAreasJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error in agregar_quimico.php: ' . json_last_error_msg());
        }
    } else {
        $celulasAreas = [];
    }

    if (isset($_GET['umbs'])) {
        $umbsEncoded = $_GET['umbs'];
        $umbsJson = urldecode($umbsEncoded);
        $umbs = json_decode($umbsJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error in agregar_quimico.php: ' . json_last_error_msg());
        }
    } else {
        $umbs = [];
    }

    if (isset($_GET['peligrosidades'])) {
        $peligrosidadesEncoded = $_GET['peligrosidades'];
        $peligrosidadesJson = urldecode($peligrosidadesEncoded);
        $peligrosidades = json_decode($peligrosidadesJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error in agregar_quimico.php: ' . json_last_error_msg());
        }
    } else {
        $peligrosidades = [];
    }
    ?>

    <div class="contenido-agregar-quimico">
        <div class="p-3">
            <h5 class="mb-4"><i class="fa-solid fa-flask me-2 fs-4"></i>Nuevo Químico</h5>
            <form id="formAgregarQuimico">
                <div class="mb-3">
                    <label for="descripcion_quimico" class="form-label">Nombre Químico: *</label>
                    <input type="text" class="form-control" id="descripcion_quimico" name="descripcion_quimico">
                </div>
                <div class="mb-3">
                    <label for="fabricante_quimico" class="form-label">Fabricante: *</label>
                    <input type="text" class="form-control" id="fabricante_quimico" name="fabricante_quimico">
                </div>
                <div class="mb-3">
                    <label for="id_peligrosidad_quimico" class="form-label">Peligrosidad: *</label>
                    <select class="form-select" id="id_peligrosidad_quimico" name="id_peligrosidad_quimico">
                        <option value="" selected disabled>Seleccione un tipo</option>
                        <?php
                        if (!empty($peligrosidades)) {
                            foreach ($peligrosidades as $peligrosidad) {
                                $id = $peligrosidad->id_peligrosidad ?? '';
                                $descripcion = $peligrosidad->descripcion_peligrosidad ?? 'N/A';
                                echo "<option value='{$id}'>{$descripcion}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="uso_quimico" class="form-label">Uso: *</label>
                    <textarea type="text" class="form-control" id="uso_quimico" name="uso_quimico"></textarea>
                </div>
                <div class="mb-3">
                    <label for="id_umb_quimico" class="form-label">UMB: *</label>
                    <select class="form-select" id="id_umb_quimico" name="id_umb_quimico">
                        <option value="" selected disabled>Seleccione un tipo</option>
                        <?php
                        if (!empty($umbs)) {
                            foreach ($umbs as $umb) {
                                $id = $umb->id_umb ?? '';
                                $descripcion = $umb->descripcion_umb ?? 'N/A';
                                echo "<option value='{$id}'>{$descripcion}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="cantidad_disponible_quimico" class="form-label">Cantidad disponible: *</label>
                    <input type="text" class="form-control double-input" id="cantidad_disponible_quimico" name="cantidad_disponible_quimico">
                </div>
                <div class="mb-3">
                    <label for="cantidad_maxima_retiro_quimico" class="form-label">Cantidad Máxima Retiro: *</label>
                    <input type="text" class="form-control double-input" id="cantidad_maxima_retiro_quimico" name="cantidad_maxima_retiro_quimico">
                </div>
                <div class="mb-3">
                    <label for="tope_minimo_quimico" class="form-label">Tope Mínimo: *</label>
                    <input type="text" class="form-control double-input" id="tope_minimo_quimico" name="tope_minimo_quimico">
                </div>
                <div class="mb-3">
                    <label for="precio_quimico" class="form-label">Precio (und): *</label>
                    <input type="text" class="form-control double-input" id="precio_quimico" name="precio_quimico">
                </div>
                <div class="mb-3">
                    <label for="url_etiqueta_emergencia_quimico" class="form-label">Url Etiqueta Emergencia: *</label>
                    <input type="text" class="form-control" id="url_etiqueta_emergencia_quimico" name="url_etiqueta_emergencia_quimico">
                </div>
                <div class="mb-4">
                    <label for="celulas_areas_quimicos" class="form-label">
                        Células autorizadas: *
                    </label>

                    <select id="celulas_areas_quimicos"
                        class="form-control shadow-sm rounded"
                        multiple
                        name="celulas_areas_quimicos"
                        multiple>
                        <?php
                        foreach ($celulasAreas as $c):
                            $id_celulas_areas = (int)$c->id_celulas_areas;
                        ?>
                            <option value="<?= htmlspecialchars($id_celulas_areas) ?>">
                                <?= htmlspecialchars($c->nombre_celula) ?>
                            </option>
                        <?php endforeach;
                        ?>
                    </select>
                    <div class="form-text mt-1">
                        Puedes buscar y seleccionar múltiples opciones.
                    </div>
                </div>


                <div class="text-end">
                    <button type="submit" id="btn-save">
                        <i class="fa fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
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
    <script src="../../../../../public/js/quimicos/agregar_quimicos.js"></script>
</body>

</html>