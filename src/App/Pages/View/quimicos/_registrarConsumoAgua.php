<?php
date_default_timezone_set('America/Bogota');
$id_celula = $_GET['id_celula'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../../../../../public/css/utils/libs/libs.css" rel="stylesheet">
    <link href="../../../../../public/css/quimicos/_registrarConsumoAgua.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body class="p-3">
    <div class="contenido-agregar-quimico">
        <div class="p-3">
            <h5 class="mb-3 d-flex align-items-center gap-2 fancy-titulo">
                <i class="fa-solid fa-droplet fs-4 fancy-titulo-icon"></i> Registrar Consumo de Agua
            </h5>
            <form id="formRegistrarConsumo">
                <input type="hidden" name="id_celula_consumo_agua" id="id_celula_consumo_agua" value="<?= htmlspecialchars($id_celula) ?>">
                
                <div class="row g-3 mb-3 mt-1">
                <?php if ($id_celula == 3) { ?>
                    <div class="col-md-6 mb-2">
                        <label for="fecha_consumo_agua" class="form-label fw-semibold fancy-label">Fecha de Consumo: *</label>
                        <input type="date" class="form-control shadow-sm input-editable" id="fecha_consumo_agua" name="fecha_consumo_agua" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="id_tanque_abastecimiento_consumo_agua" class="form-label fw-semibold fancy-label">Tanque: *</label>
                        <select class="form-select shadow-sm input-select" id="id_tanque_abastecimiento_consumo_agua" name="id_tanque_abastecimiento_consumo_agua">
                            <option value="">Cargando tanques...</option>
                        </select>
                    </div>
                <?php } else { ?>
                    <div class="col-md-12 mb-2">
                        <label for="fecha_consumo_agua" class="form-label fw-semibold fancy-label">Fecha de Consumo: *</label>
                        <input type="date" class="form-control shadow-sm input-editable" id="fecha_consumo_agua" name="fecha_consumo_agua" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                    </div>
                <?php } ?>

                <div class="col-md-6 mb-2">
                    <label for="consumo_inicial_agua" class="form-label fw-semibold fancy-label">Consumo Inicial: *</label>
                    <input type="text" class="form-control decimal-input shadow-sm input-readonly" id="consumo_inicial_agua" name="consumo_inicial_agua" readonly>
                </div>
                
                <div class="col-md-6 mb-2">
                    <label for="consumo_final_agua" class="form-label fw-semibold fancy-label">Consumo Final: *</label>
                    <input type="text" class="form-control decimal-input shadow-sm input-editable" id="consumo_final_agua" name="consumo_final_agua">
                </div>

                <!-- Tarjeta Visual de Consumo Calculado en Tiempo Real -->
                <div class="col-md-12">
                    <div id="box-consumo-calculado" class="consumo-calc-box">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="calc-label" id="calc-label-text">
                                <i class="fa-solid fa-calculator calc-icon"></i> Consumo del Día:
                            </span>
                            <span class="calc-valor" id="calc-valor-text">
                                0,0000 m³
                            </span>
                        </div>
                    </div>
                </div>
            </div>

                <div class="text-end mt-4">
                    <button type="submit" id="btn-save">
                        <i class="fa fa-save me-1"></i> Guardar Consumo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../../public/js/quimicos/registrarConsumoAgua.js?v=<?= time() ?>"></script>
</body>
</html>
