<?php
$id      = $_GET['id'] ?? '';
$celula  = $_GET['celula'] ?? '';
$tanque  = $_GET['tanque'] ?? '';
$inicial = isset($_GET['inicial']) && is_numeric($_GET['inicial']) ? (float)$_GET['inicial'] : 0;
$final   = isset($_GET['final']) && is_numeric($_GET['final']) ? (float)$_GET['final'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../../../../../public/css/utils/libs/libs.css" rel="stylesheet">
    <link href="../../../../../public/css/quimicos/_editarConsumoAgua.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body class="p-3">
    <div class="contenido-editar-consumo">
        <div class="p-3">
            <h5 class="mb-3 d-flex align-items-center gap-2 fancy-titulo">
                <i class="fa-solid fa-pen-to-square fs-4 fancy-titulo-icon"></i> Editar Consumo de Agua
            </h5>
            <form id="formEditarConsumo">
                <input type="hidden" name="id_consumo_agua" id="id_consumo_agua" value="<?= htmlspecialchars((string)$id) ?>">

                <div class="row g-3 mb-3 mt-1">
                    <div class="col-md-6 mb-2">
                        <label class="form-label fw-semibold fancy-label">Célula / Área:</label>
                        <input type="text" class="form-control shadow-sm input-readonly" value="<?= htmlspecialchars($celula) ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="form-label fw-semibold fancy-label">Tanque:</label>
                        <input type="text" class="form-control shadow-sm input-readonly" value="<?= htmlspecialchars(!empty($tanque) && $tanque !== 'null' ? $tanque : 'No Aplica') ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="consumo_inicial_agua" class="form-label fw-semibold fancy-label">Consumo Inicial: *</label>
                        <input type="text" class="form-control decimal-input shadow-sm input-readonly" id="consumo_inicial_agua" name="consumo_inicial_agua" value="<?= str_replace('.', ',', (string)$inicial) ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label for="consumo_final_agua" class="form-label fw-semibold fancy-label">Consumo Final: *</label>
                        <input type="text" class="form-control decimal-input shadow-sm input-editable" id="consumo_final_agua" name="consumo_final_agua" value="<?= str_replace('.', ',', (string)$final) ?>" placeholder="Ingrese nuevo consumo final">
                    </div>

                    <!-- Tarjeta Visual de Consumo Calculado en Tiempo Real -->
                    <div class="col-md-12">
                        <div id="box-consumo-calculado" class="consumo-calc-box">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="calc-label" id="calc-label-text">
                                    <i class="fa-solid fa-calculator calc-icon"></i> Consumo Calculado:
                                </span>
                                <span class="calc-valor" id="calc-valor-text">
                                    0,0000 m³
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" id="btn-actualizar">
                        <i class="fa-solid fa-check me-1"></i> Actualizar Consumo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../../public/js/quimicos/editarConsumoAgua.js?v=<?= time() ?>"></script>
</body>
</html>
