<?php
// *****************************************************************
// INICIALIZACIÓN DE VISTA PROTEGIDA
// *****************************************************************
include '../../Handler/auth/session_init.php'; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Proyecciones de Consumo y Precios con Machine Learning - Gestión Ambiental HWI">
    <title>Proyecciones de Consumo y Precios</title>
    <link rel="shortcut icon" href="../../../../../public/img/LogoBlanco.png" type="image/x-icon">

    <!-- CSS Base y Librerías -->
    <link rel="stylesheet" href="../../../../../public/css/utils/libs/libs.css">
    <link rel="stylesheet" href="../../../../../public/css/utils/estilos_spinner.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="../../../../../public/css/quimicos/proyeccionesConsumosPrecios.css?v=<?= time() ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js 4.4.0 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <?php include('../../../Shared/Util/spinner.php'); ?>
</head>

<body>
    <?php include '../shared/header.php'; ?>

    <main class="proyecciones-main-container">
        <!-- Banner Superior Corporativo -->
        <header class="proy-header-banner">
            <div class="proy-title-wrap">
                <i class="fa-solid fa-chart-line proy-title-icon" aria-hidden="true"></i>
                <div>
                    <h1 class="proy-page-title">Proyecciones de Consumo y Precios</h1>
                    <p class="proy-page-subtitle">Modelos de Machine Learning para estimación de demanda, inventario y presupuesto</p>
                </div>
            </div>
            <div class="proy-header-badge">
                <i class="fa-solid fa-brain" aria-hidden="true"></i> Motor Analítico Activo
            </div>
        </header>

        <!-- Layout Dual: Dashboard a la izquierda, Chatbot a la derecha -->
        <div class="proy-layout-grid">
            
            <!-- SECCIÓN IZQUIERDA: CONTROLES, KPIS, GRÁFICOS E INVENTARIO -->
            <section class="proy-col-dashboard" aria-label="Dashboard de Proyecciones">
                
                <!-- Barra de Filtros -->
                <div class="proy-card proy-filter-card">
                    <div class="proy-filter-grid">
                        <div class="proy-filter-item proy-filter-quimico">
                            <label for="select-quimico" class="proy-filter-label">
                                <i class="fa-solid fa-flask" aria-hidden="true"></i> Seleccionar Químico
                            </label>
                            <select id="select-quimico" class="form-select"></select>
                        </div>

                        <div class="proy-filter-item proy-filter-celula">
                            <label for="select-celula" class="proy-filter-label">
                                <i class="fa-solid fa-sitemap" aria-hidden="true"></i> Célula
                            </label>
                            <select id="select-celula" class="form-select">
                                <option value="Todas">Todas</option>
                            </select>
                        </div>

                        <div class="proy-filter-item proy-filter-checkbox">
                            <label class="proy-checkbox-container">
                                <input type="checkbox" id="chk-anio-siguiente">
                                <span class="proy-checkbox-checkmark"></span>
                                <span class="proy-checkbox-text" id="lbl-anio-siguiente">Proyección <?= date('Y') + 1 ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Contenedor Dinámico de Resultados -->
                <div id="proy-results-container">
                    <!-- Cabecera de Químico Seleccionado -->
                    <div class="proy-product-header">
                        <h2 id="proy-nombre-quimico" class="proy-product-title">Cargando producto...</h2>
                        <span id="proy-badge-umb" class="proy-badge-umb">Unidad</span>
                    </div>

                    <!-- Grid de Tarjetas KPI -->
                    <div class="proy-kpi-grid">
                        <!-- KPI 1: Precio Actual -->
                        <div class="proy-kpi-card">
                            <div class="proy-kpi-top">
                                <span class="proy-kpi-label">Precio Actual</span>
                                <div class="proy-kpi-icon-wrap kpi-blue">
                                    <i class="fa-solid fa-tag" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="proy-kpi-value" id="kpi-precio">$0</div>
                            <div class="proy-kpi-sub" id="kpi-precio-sub">Por unidad de medida</div>
                        </div>

                        <!-- KPI 2: Promedio Histórico -->
                        <div class="proy-kpi-card">
                            <div class="proy-kpi-top">
                                <span class="proy-kpi-label">Prom. Histórico</span>
                                <div class="proy-kpi-icon-wrap kpi-teal">
                                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="proy-kpi-value" id="kpi-promedio">0</div>
                            <div class="proy-kpi-sub" id="kpi-promedio-sub">Consumo medio mensual</div>
                        </div>

                        <!-- KPI 3: Estimado Próximo Mes (Con Delta) -->
                        <div class="proy-kpi-card">
                            <div class="proy-kpi-top">
                                <span class="proy-kpi-label">Est. Próx. Mes</span>
                                <div class="proy-kpi-icon-wrap kpi-amber">
                                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="proy-kpi-value-row">
                                <span class="proy-kpi-value" id="kpi-estimado">0</span>
                                <span class="proy-delta-badge" id="kpi-delta">+0%</span>
                            </div>
                            <div class="proy-kpi-sub">Pronóstico generado por ML</div>
                        </div>

                        <!-- KPI 4: Stock Disponible -->
                        <div class="proy-kpi-card">
                            <div class="proy-kpi-top">
                                <span class="proy-kpi-label">Stock Disponible</span>
                                <div class="proy-kpi-icon-wrap kpi-emerald">
                                    <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="proy-kpi-value" id="kpi-stock">0</div>
                            <div class="proy-kpi-sub" id="kpi-stock-limites">Mín: 0 | Máx: 0</div>
                        </div>
                    </div>

                    <!-- Insignia Logística y Alerta de Reorden -->
                    <div class="proy-logistics-badge" id="proy-logistics-bar">
                        <div class="proy-logistics-item">
                            <i class="fa-solid fa-truck-ramp-box proy-icon-cyan" aria-hidden="true"></i>
                            <span><strong>Lead Time:</strong> <span id="logistics-lead-time">-- a -- días</span></span>
                        </div>
                        <div class="proy-logistics-divider"></div>
                        <div class="proy-logistics-item">
                            <i class="fa-solid fa-circle-exclamation proy-icon-amber" aria-hidden="true"></i>
                            <span><strong>Estado Logístico:</strong> <span id="logistics-estado">Consultando...</span></span>
                        </div>
                        <div class="proy-logistics-divider"></div>
                        <div class="proy-logistics-item">
                            <i class="fa-solid fa-cart-flatbed proy-icon-green" aria-hidden="true"></i>
                            <span><strong>Sugerido a pedir:</strong> <span id="logistics-sugerido">0</span></span>
                        </div>
                    </div>

                    <!-- Gráfico 1: Tendencia de Consumo -->
                    <div class="proy-card proy-chart-card">
                        <div class="proy-chart-header">
                            <h3 class="proy-chart-title" id="title-chart-consumo">
                                <i class="fa-solid fa-chart-area" aria-hidden="true"></i> Tendencia de Consumo - Histórico vs Pronóstico ML
                            </h3>
                            <div class="proy-chart-legend">
                                <span class="legend-dot dot-historico"></span> Histórico
                                <span class="legend-dot dot-proyectado"></span> Pronóstico ML
                            </div>
                        </div>
                        <div class="proy-chart-canvas-wrapper">
                            <canvas id="chart-consumo"></canvas>
                        </div>
                    </div>

                    <!-- Gráfico 2: Proyección de Gasto Total -->
                    <div class="proy-card proy-chart-card">
                        <div class="proy-chart-header">
                            <h3 class="proy-chart-title">
                                <i class="fa-solid fa-coins" aria-hidden="true"></i> Proyección de Gasto Total ($) [Consumo × Precio]
                            </h3>
                            <div class="proy-chart-legend">
                                <span class="legend-dot dot-gasto-hist"></span> Histórico
                                <span class="legend-dot dot-gasto-proy"></span> Proyectado
                            </div>
                        </div>
                        <div class="proy-chart-canvas-wrapper">
                            <canvas id="chart-gasto"></canvas>
                        </div>
                    </div>

                    <!-- Acordeón de Ingresos a Inventario -->
                    <div class="proy-card proy-accordion-card">
                        <button type="button" class="proy-accordion-header" id="btn-toggle-ingresos" aria-expanded="false">
                            <span class="proy-accordion-title">
                                <i class="fa-solid fa-dolly" aria-hidden="true"></i> 
                                <span id="lbl-accordion-ingresos">Ingresos a Inventario del Producto</span>
                            </span>
                            <i class="fa-solid fa-chevron-down proy-accordion-arrow" id="icon-accordion-arrow" aria-hidden="true"></i>
                        </button>
                        <div class="proy-accordion-body" id="body-accordion-ingresos">
                            <div class="table-responsive">
                                <table class="table proy-table" id="tabla-ingresos">
                                    <thead>
                                        <tr>
                                            <th>Fecha de Ingreso</th>
                                            <th>Cantidad Registrada</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ingresos">
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay registros de ingresos.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado Sin Datos -->
                <div id="proy-empty-state" class="proy-card proy-empty-card d-none">
                    <div class="proy-empty-icon">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    </div>
                    <h3 class="proy-empty-title">Sin registros para esta selección</h3>
                    <p class="proy-empty-desc" id="proy-empty-message">No se encontraron datos históricos ni proyecciones de consumo para la combinación seleccionada.</p>
                </div>

            </section>

            <!-- SECCIÓN DERECHA: ASISTENTE DE ANÁLISIS (CHATBOT) -->
            <aside class="proy-col-chat" aria-label="Asistente de Análisis de Inventario">
                <div class="proy-chat-card">
                    <!-- Cabecera del Chat -->
                    <div class="proy-chat-header">
                        <div class="proy-chat-bot-info">
                            <div class="proy-chat-avatar-bot">
                                <i class="fa-solid fa-robot" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3 class="proy-chat-title">Asistente de Análisis</h3>
                                <div class="proy-chat-status">
                                    <span class="proy-status-dot"></span>
                                    <span>En línea • Asistente HWI</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="proy-btn-clean-chat" id="btn-clean-chat" title="Reiniciar conversación">
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Mensajes del Chat -->
                    <div class="proy-chat-messages" id="chat-messages-container">
                        <!-- Mensaje de bienvenida inicial -->
                        <div class="proy-msg-row proy-msg-bot">
                            <div class="proy-msg-avatar">
                                <i class="fa-solid fa-robot" aria-hidden="true"></i>
                            </div>
                            <div class="proy-msg-bubble" id="chat-initial-greeting">
                                ¡Hola! 👋 Soy tu asistente de inventario HWI.<br><br>
                                Selecciona un químico para ver su disponibilidad, proyecciones de demanda y recomendaciones de compra.
                            </div>
                        </div>
                    </div>

                    <!-- Indicador de tipeo -->
                    <div class="proy-typing-indicator d-none" id="chat-typing-indicator">
                        <div class="proy-typing-dot"></div>
                        <div class="proy-typing-dot"></div>
                        <div class="proy-typing-dot"></div>
                        <span class="proy-typing-label">El asistente está analizando los datos...</span>
                    </div>

                    <!-- Barra de Entrada del Chat -->
                    <form class="proy-chat-input-bar" id="chat-form">
                        <input 
                            type="text" 
                            id="chat-input" 
                            class="proy-chat-input" 
                            placeholder="Pregunta sobre stock, compras, consumos..." 
                            autocomplete="off"
                            required
                        >
                        <button type="submit" class="proy-chat-send-btn" id="btn-chat-send" title="Enviar mensaje">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </aside>

        </div>
    </main>

    <?php include '../shared/footer.php'; ?>

    <!-- Scripts Base -->
    <script src="../../../../../public/js/utils/libs/jquery.js"></script>
    <script src="../../../../../public/js/utils/libs/bootstrap.js"></script>
    <script src="../../../../../public/js/utils/libs/notification.js"></script>
    <script src="../../../../../public/js/utils/spinner.js"></script>
    <script src="../../../../../public/js/utils/notifications.js"></script>
    
    <!-- Choices JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Script Controlador Proyecciones -->
    <script src="../../../../../public/js/quimicos/proyeccionesConsumosPrecios.js?v=<?= time() ?>"></script>
</body>

</html>