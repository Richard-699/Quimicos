<?php
// *****************************************************************
// INICIALIZACIÓN DE VISTA PROTEGIDA
// Este archivo carga Composer, Inicia la Sesión, Valida la Sesión 
// y define las variables de administrador requeridas por el header.
// *****************************************************************
include '../../Handler/auth/session_init.php'; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Informe y Dashboard de Consumo de Químicos - Gestión Ambiental HWI">
    <title>Informe y Dashboard de Químicos</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">

    <!-- CSS Base y Utilidades -->
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="../../../../../public/css/quimicos/informeQuimicos.css?v=<?= time() ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>

<body>
    <?php include '../shared/header.php'; ?>

    <div class="informe-dashboard-wrapper">

        <!-- HEADER BANNER -->
        <div class="dash-header-banner">
            <div class="dash-header-left">
                <div class="dash-header-icon">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
                <div class="dash-header-text">
                    <h1>Informe y Dashboard de Consumo de Químicos</h1>
                    <p>Métricas de consumo &middot; Costo acumulado &middot; Distribución por célula y tendencias</p>
                </div>
            </div>
            <div class="dash-live-badge">
                <span class="dash-live-dot"></span>
                <span>Datos Actualizados</span>
            </div>
        </div>

        <!-- BARRA DE FILTROS -->
        <div class="dash-filter-card">
            <div class="dash-filters-grid">
                <div class="dash-filter-group dash-filter-group-anio">
                    <label class="dash-filter-label" for="filtro-anio">
                        <i class="fa-solid fa-calendar"></i> Año
                    </label>
                    <select id="filtro-anio" class="dash-select">
                        <option value="">Todos los años</option>
                    </select>
                </div>

                <div class="dash-filter-group dash-filter-group-mes">
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

                <div class="dash-filter-group dash-filter-group-celula">
                    <label class="dash-filter-label" for="filtro-celula">
                        <i class="fa-solid fa-industry"></i> Área / Célula
                    </label>
                    <select id="filtro-celula" class="dash-select">
                        <option value="">Todas las áreas / células</option>
                    </select>
                </div>

                <div class="dash-filter-group dash-filter-group-estado">
                    <label class="dash-filter-label" for="filtro-estado">
                        <i class="fa-solid fa-list-check"></i> Estado
                    </label>
                    <select id="filtro-estado" class="dash-select">
                        <option value="">Todos los estados</option>
                        <option value="1">Aprobado</option>
                        <option value="2">Rechazado</option>
                        <option value="3">Pendiente</option>
                    </select>
                </div>

                <div class="dash-filter-group dash-filter-group-search">
                    <label class="dash-filter-label" for="filtro-quimico">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Químico
                    </label>
                    <select id="filtro-quimico" class="dash-select">
                        <option value="">Todos los químicos</option>
                    </select>
                </div>

                <div class="dash-filter-actions">
                    <button class="dash-btn dash-btn-secondary" id="btn-limpiar-filtros" title="Restablecer filtros">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI SUMMARY CARDS EN 2 FILAS DE 3 COLUMNAS -->
        <div class="dash-kpi-grid">
            <!-- FILA 1: IMPACTO FINANCIERO Y VOLUMEN -->
            <div class="dash-kpi-card kpi-costo">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Costo Total Consumido</span>
                    <span class="kpi-value" id="kpi-val-costo">—</span>
                    <span class="kpi-sub" id="kpi-sub-costo">Inversión acumulada</span>
                </div>
            </div>

            <div class="dash-kpi-card kpi-evitado">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Costos Evitados (Rechazos)</span>
                    <span class="kpi-value" id="kpi-val-evitado">—</span>
                    <span class="kpi-sub" id="kpi-sub-evitado">Ahorro en solicitudes rechazadas</span>
                </div>
            </div>

            <div class="dash-kpi-card kpi-cantidad">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Cantidad Consumida</span>
                    <span class="kpi-value" id="kpi-val-cantidad">—</span>
                    <span class="kpi-sub" id="kpi-sub-cantidad">Despachos realizados</span>
                </div>
            </div>

            <!-- FILA 2: ALCANCE OPERATIVO Y CATÁLOGO -->
            <div class="dash-kpi-card kpi-quimicos">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Químicos Filtrados</span>
                    <span class="kpi-value" id="kpi-val-quimicos">—</span>
                    <span class="kpi-sub" id="kpi-sub-quimicos">En solicitudes activas</span>
                </div>
            </div>

            <div class="dash-kpi-card kpi-registrados">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Químicos Registrados</span>
                    <span class="kpi-value" id="kpi-val-quimicos-registrados">—</span>
                    <span class="kpi-sub" id="kpi-sub-quimicos-registrados">En catálogo activo</span>
                </div>
            </div>

            <div class="dash-kpi-card kpi-celulas">
                <div class="kpi-icon-wrap">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div class="kpi-body">
                    <span class="kpi-label">Células Solicitantes</span>
                    <span class="kpi-value" id="kpi-val-celulas">—</span>
                    <span class="kpi-sub" id="kpi-sub-celulas">Áreas con consumo</span>
                </div>
            </div>
        </div>

        <!-- FILA 1: TOP IMPACTO FINANCIERO + IMPACTO POR CÉLULA -->
        <div class="dash-charts-row-split">
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-ranking-star"></i>
                        <span>Top 10 Químicos x Impacto Financiero</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-corp-green">Mayor Inversión</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-costo-quimico"></canvas>
                </div>
            </div>

            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Impacto y Participación por Célula / Área</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-corp-dark">% del Gasto Total</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-por-celula"></canvas>
                </div>
            </div>
        </div>

        <!-- FILA 2: TOP MÁS CONSUMIDOS (MAYOR MOVIMIENTO) + EVOLUCIÓN MENSUAL -->
        <div class="dash-charts-row-split">
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Top Químicos Más Consumidos (Mayor Movimiento)</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-corp-teal">Mayor Volumen y Frecuencia</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-consumo-quimico"></canvas>
                </div>
            </div>

            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <div class="dash-chart-title">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Evolución Mensual del Gasto y Consumo</span>
                    </div>
                    <div class="dash-chart-badges">
                        <span class="dash-badge badge-corp-green">Costo ($ COP)</span>
                        <span class="dash-badge badge-corp-teal">Despachos (u.)</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="chart-tendencia-mensual"></canvas>
                </div>
            </div>
        </div>

        <!-- TABLA RESUMEN DETALLADO -->
        <div class="dash-table-card">
            <div class="dash-chart-header">
                <div class="dash-chart-title">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Historial Completo de Solicitudes y Consumos</span>
                </div>
                <div class="dash-table-search-wrap">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="tabla-search" class="dash-table-search" placeholder="Buscar químico, área, solicitante, estado...">
                </div>
            </div>
            <div class="dash-table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Químico y Fabricante</th>
                            <th>Célula / Área</th>
                            <th>Solicitante</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Costo Solicitud</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalle-body">
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Cargando datos...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="dash-table-footer">
                <span id="tabla-info">Cargando registros...</span>
                <div class="dash-table-pagination" id="tabla-paginacion"></div>
            </div>
        </div>

    </div>

    <?php include '../shared/footer.php'; ?>

    <!-- Scripts en orden -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    <script src="../../../../../public/js/quimicos/informeQuimicos.js?v=<?= time() ?>"></script>
</body>

</html>