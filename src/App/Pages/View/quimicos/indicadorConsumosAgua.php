<?php
include '../../Handler/auth/session_init.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard de Indicador de Consumos de Agua - Gestión Ambiental HWI">
    <title>Indicador Consumos de Agua</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="../../../../../public/css/quimicos/indicadorConsumosAgua.css?v=<?= time() ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>

    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>

<body>
    <?php include '../shared/header.php' ?>

    <div class="indicador-dashboard-wrapper">

        <!-- HEADER BANNER -->
        <div class="dash-header-banner">
            <div class="dash-header-left">
                <div class="dash-header-icon">
                    <i class="fa-solid fa-droplet"></i>
                </div>
                <div class="dash-header-text">
                    <h1>Indicador de Consumos de Agua</h1>
                    <p>Monitoreo en tiempo real &middot; Análisis por célula y tanque &middot; Proyecciones ML</p>
                </div>
            </div>
            <div class="dash-header-right">
                <div class="dash-live-badge">
                    <span class="dash-live-dot"></span>
                    <span id="dash-last-update">Cargando...</span>
                </div>
            </div>
        </div>

        <!-- PANEL DE FILTROS -->
        <div class="dash-filters-panel">
            <div class="dash-filters-title">
                <i class="fa-solid fa-sliders"></i>
                <span>Filtros del Dashboard</span>
            </div>
            <div class="dash-filters-grid">
                <div class="dash-filter-group">
                    <label class="dash-filter-label" for="filtro-celula">
                        <i class="fa-solid fa-industry"></i> Célula / Área
                    </label>
                    <select id="filtro-celula" class="dash-select">
                        <option value="">Todas las células</option>
                    </select>
                </div>
                <div class="dash-filter-group" id="grupo-tanque">
                    <label class="dash-filter-label" for="filtro-tanque">
                        <i class="fa-solid fa-circle-nodes"></i> Tanque
                    </label>
                    <select id="filtro-tanque" class="dash-select">
                        <option value="">Todos los tanques</option>
                    </select>
                </div>
                <div class="dash-filter-group">
                    <label class="dash-filter-label" for="filtro-anio">
                        <i class="fa-solid fa-calendar"></i> Año
                    </label>
                    <select id="filtro-anio" class="dash-select"></select>
                </div>
                <div class="dash-filter-group">
                    <label class="dash-filter-label" for="filtro-mes">
                        <i class="fa-solid fa-calendar-days"></i> Mes
                    </label>
                    <select id="filtro-mes" class="dash-select">
                        <option value="">Todos los meses</option>
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="dash-filter-group">
                    <label class="dash-filter-label" for="filtro-fecha">
                        <i class="fa-solid fa-magnifying-glass-chart"></i> Consultar Día
                    </label>
                    <input type="date" id="filtro-fecha" class="dash-select">
                </div>
                <div class="dash-filter-group dash-filter-actions">
                    <button id="btn-aplicar-filtros" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-chart-bar"></i> Aplicar
                    </button>
                    <button id="btn-limpiar-filtros" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </button>
                </div>
            </div>
            <div id="filtros-activos-container" class="dash-active-filters" style="display:none;">
                <span class="dash-active-filters-label">Filtros activos:</span>
                <div id="filtros-activos-tags"></div>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="dash-kpi-grid">
            <div class="dash-kpi-card kpi-primary">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-gauge-high"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Consumo Total (m³)</span>
                    <span class="kpi-value" id="kpi-val-total">—</span>
                    <span class="kpi-sub" id="kpi-sub-total">del período seleccionado</span>
                </div>
            </div>
            <div class="dash-kpi-card kpi-info">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Promedio Diario (m³)</span>
                    <span class="kpi-value" id="kpi-val-prom">—</span>
                    <span class="kpi-sub" id="kpi-sub-prom">promedio del período</span>
                </div>
            </div>
            <div class="dash-kpi-card kpi-success">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Pico Máximo (m³)</span>
                    <span class="kpi-value" id="kpi-val-pico">—</span>
                    <span class="kpi-sub" id="kpi-sub-pico">mayor consumo diario</span>
                </div>
            </div>
            <div class="dash-kpi-card kpi-danger">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Alertas ML Activas</span>
                    <span class="kpi-value" id="kpi-val-alertas">—</span>
                    <span class="kpi-sub" id="kpi-sub-alertas">superan tope predictivo</span>
                </div>
            </div>
            <div class="dash-kpi-card kpi-purple">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Tope Promedio ML</span>
                    <span class="kpi-value" id="kpi-val-tope">—</span>
                    <span class="kpi-sub" id="kpi-sub-tope">umbral predictivo ML</span>
                </div>
            </div>
            <div class="dash-kpi-card kpi-teal">
                <div class="kpi-icon-wrap"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="kpi-body">
                    <span class="kpi-label">Total Registros</span>
                    <span class="kpi-value" id="kpi-val-registros">—</span>
                    <span class="kpi-sub" id="kpi-sub-registros">en el período</span>
                </div>
            </div>
        </div>

        <!-- FILA 1: TENDENCIA EN EL TIEMPO -->
        <div class="dash-charts-row">
            <div class="dash-chart-card dash-chart-wide">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Tendencia de Consumo en el Tiempo</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-historico">Histórico</span>
                        <span class="dash-badge badge-tope">Tope ML</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-tendencia"></canvas>
                </div>
            </div>
        </div>

        <!-- FILA 2: MENSUAL + CÉLULAS -->
        <div class="dash-charts-row dash-charts-row-split">
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Consumo por Mes</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-historico">m³ consumidos</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-mensual"></canvas>
                </div>
            </div>
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-industry"></i>
                        <span>Consumo por Célula / Área</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-historico">Total m³</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-celulas"></canvas>
                </div>
            </div>
        </div>

        <!-- FILA 3: TANQUES + ALERTAS ML -->
        <div class="dash-charts-row dash-charts-row-split">
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-circle-nodes"></i>
                        <span>Consumo por Tanque (Recubrimiento)</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-historico">m³</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-tanques"></canvas>
                </div>
            </div>
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-triangle-exclamation dash-alerta-icon-danger"></i>
                        <span>Alertas y Anomalías ML</span>
                    </div>
                    <span id="badge-total-alertas" class="dash-badge badge-alerta-count">0 alertas</span>
                </div>
                <div class="dash-chart-body">
                    <div id="alertas-lista" class="dash-alertas-lista">
                        <div class="dash-empty-state">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <p>Cargando alertas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 4: PROYECCIONES ML -->
        <div class="dash-charts-row">
            <div class="dash-chart-card dash-chart-wide">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-brain"></i>
                        <span>Proyección ML — Consumo Estimado Próximos Meses</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-historico">Histórico</span>
                        <span class="dash-badge badge-proyectado">Proyectado ML</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-proyeccion"></canvas>
                </div>
            </div>
        </div>

        <!-- TABLA RESUMEN -->
        <div class="dash-table-card">
            <div class="dash-chart-header">
                <div class="dash-chart-title">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Registros Detallados</span>
                </div>
                <div class="dash-table-search-wrap">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="dash-table-search" class="dash-table-search" placeholder="Buscar en tabla...">
                </div>
            </div>
            <div class="dash-table-responsive">
                <table id="tabla-indicador" class="dash-table">
                    <thead>
                        <tr>
                            <th data-sort="fecha">Fecha <i class="fa-solid fa-sort"></i></th>
                            <th data-sort="celula">Célula <i class="fa-solid fa-sort"></i></th>
                            <th data-sort="tanque">Tanque <i class="fa-solid fa-sort"></i></th>
                            <th data-sort="inicial">C. Inicial <i class="fa-solid fa-sort"></i></th>
                            <th data-sort="final">C. Final <i class="fa-solid fa-sort"></i></th>
                            <th data-sort="neto">Consumo Neto (m³) <i class="fa-solid fa-sort"></i></th>
                            <th>Estado ML</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-indicador-body">
                        <tr>
                            <td colspan="8" class="dash-table-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Cargando datos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="dash-table-footer">
                <span id="dash-table-info">—</span>
                <div class="dash-table-pagination" id="dash-pagination"></div>
            </div>
        </div>

    </div>

    <?php include '../shared/footer.php'; ?>

    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/fancybox.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/indicadorConsumosAgua.js?v=<?= time() ?>"></script>
</body>

</html>
