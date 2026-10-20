/**
 * INFORME Y DASHBOARD DE CONSUMO DE QUÍMICOS
 * Gráficos interactivos, análisis de impacto por célula, filtros con búsqueda en select y tabla detallada
 */

$(document).ready(function () {
    const HANDLER_URL = '../../Handler/quimicos/informeQuimicosHandler.php';

    const MESES = {
        1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril',
        5: 'Mayo', 6: 'Junio', 7: 'Julio', 8: 'Agosto',
        9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
    };

    const PALETA_COLORES = [
        '#003D4B', '#079ABD', '#2DB992', '#36CFF2', '#FBB93F',
        '#3A6872', '#8BCCDD', '#0D7256', '#D97706', '#0284C7',
        '#6366F1', '#EC4899', '#14B8A6', '#F59E0B', '#8B5CF6'
    ];

    let choicesQuimico = null;
    let choicesCelula = null;

    const STATE = {
        filtros: {
            anio: '',
            mes: '',
            id_celula: '',
            id_quimico: '',
            id_estado: ''
        },
        charts: {},
        tablaData: [],
        tablaFiltrada: [],
        paginaActual: 1,
        porPagina: 10
    };

    // ── HELPERS DE FORMATO ──
    function formatCOP(valor) {
        const num = parseFloat(valor) || 0;
        return '$ ' + num.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' COP';
    }

    function formatNum(valor, decimales = 2) {
        const num = parseFloat(valor) || 0;
        return num.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: decimales });
    }

    function formatCompactCOP(valor) {
        const num = parseFloat(valor) || 0;
        if (num >= 1000000) {
            return '$ ' + (num / 1000000).toLocaleString('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 2 }) + ' mill.';
        }
        if (num >= 1000) {
            return '$ ' + (num / 1000).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 1 }) + ' mil';
        }
        return '$ ' + num.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function destroyChart(key) {
        if (STATE.charts[key]) {
            STATE.charts[key].destroy();
            delete STATE.charts[key];
        }
    }

    // ── 1. CARGAR SELECTS DE FILTROS (CON CHOICES.JS SEARCHABLE) ──
    async function cargarFiltros() {
        try {
            const res = await fetch(`${HANDLER_URL}?action=onGet_filtros`);
            const data = await res.json();
            if (!data.success) return;

            // 1.1 Llenar Años
            const $selAnio = $('#filtro-anio');
            $selAnio.empty().append('<option value="">Todos los años</option>');
            (data.anios || []).forEach(a => {
                $selAnio.append(`<option value="${a}">${a}</option>`);
            });

            // 1.2 Llenar Células / Áreas
            const $selCel = $('#filtro-celula');
            $selCel.empty().append('<option value="">Todas las áreas / células</option>');
            (data.celulas || []).forEach(c => {
                $selCel.append(`<option value="${c.id_celulas_areas}">${c.nombre_celula}</option>`);
            });

            if (choicesCelula) {
                choicesCelula.destroy();
            }
            choicesCelula = new Choices('#filtro-celula', {
                searchEnabled: true,
                searchPlaceholderValue: 'Buscar área / célula...',
                itemSelectText: '',
                noResultsText: 'No se encontraron áreas',
                noChoicesText: 'Sin opciones disponibles',
                shouldSort: false
            });

            // 1.3 Llenar Químicos (Buscador escribiendo)
            const $selQuim = $('#filtro-quimico');
            $selQuim.empty().append('<option value="">Todos los químicos</option>');
            (data.quimicos || []).forEach(q => {
                $selQuim.append(`<option value="${q.id_quimico}">${q.nombre_completo}</option>`);
            });

            if (choicesQuimico) {
                choicesQuimico.destroy();
            }
            choicesQuimico = new Choices('#filtro-quimico', {
                searchEnabled: true,
                searchPlaceholderValue: 'Escriba para buscar químico...',
                itemSelectText: '',
                noResultsText: 'No se encontraron químicos',
                noChoicesText: 'Sin opciones disponibles',
                shouldSort: false
            });

        } catch (e) {
            console.error('Error cargando filtros:', e);
        }
    }

    // ── 2. CARGAR DASHBOARD COMPLETO ──
    async function cargarDashboard() {
        const params = new URLSearchParams({
            action: 'onGet_dashboard_data'
        });

        if (STATE.filtros.anio) params.append('anio', STATE.filtros.anio);
        if (STATE.filtros.mes) params.append('mes', STATE.filtros.mes);
        if (STATE.filtros.id_celula) params.append('id_celula', STATE.filtros.id_celula);
        if (STATE.filtros.id_quimico) params.append('id_quimico', STATE.filtros.id_quimico);
        if (STATE.filtros.id_estado) params.append('id_estado', STATE.filtros.id_estado);

        try {
            const res = await fetch(`${HANDLER_URL}?${params.toString()}`);
            const data = await res.json();

            if (!data.success) {
                console.error(data.message);
                return;
            }

            actualizarKPIs(data.kpis);
            renderChartCostoQuimico(data.top_financiero);
            renderChartPorCelula(data.por_celula);
            renderChartRotacionQuimico(data.mayor_movimiento);
            renderChartTendenciaMensual(data.mensual);
            actualizarTablaDetalle(data.detalle);

        } catch (e) {
            console.error('Error cargando datos del dashboard:', e);
        }
    }

    // ── 3. ACTUALIZAR KPIS ──
    function actualizarKPIs(kpis) {
        if (!kpis) return;
        const estadoSel = STATE.filtros.id_estado; // '' (Todos), '1' (Aprobado), '2' (Rechazado), '3' (Pendiente)

        // 1. Costo Consumido
        $('#kpi-val-costo').text(formatCompactCOP(kpis.costo_total));
        if (estadoSel === '2') {
            $('#kpi-sub-costo').text('Filtrado por rechazados ($0 consumido)');
        } else if (estadoSel === '3') {
            $('#kpi-sub-costo').text('Filtrado por pendientes ($0 consumido)');
        } else {
            $('#kpi-sub-costo').text(formatCOP(kpis.costo_total));
        }

        // 2. Costos Evitados
        if (estadoSel === '1') {
            $('#kpi-val-evitado').text('$ 0');
            $('#kpi-sub-evitado').text('0 rechazos en filtro Aprobado');
        } else if (estadoSel === '2') {
            $('#kpi-val-evitado').text(formatCompactCOP(kpis.costo_evitado));
            $('#kpi-sub-evitado').text(`${kpis.total_rechazadas} solicitudes rechazadas`);
        } else if (estadoSel === '3') {
            $('#kpi-val-evitado').text(formatCompactCOP(kpis.costo_pendiente));
            $('#kpi-sub-evitado').text(`${kpis.total_pendientes} solicitudes en evaluación`);
        } else {
            $('#kpi-val-evitado').text(formatCompactCOP(kpis.costo_evitado));
            $('#kpi-sub-evitado').text(`${kpis.total_rechazadas} rechazos · ${formatCompactCOP(kpis.costo_pendiente)} pend.`);
        }

        // 3. Cantidad Consumida
        $('#kpi-val-cantidad').text(formatNum(kpis.cantidad_total) + ' u.');
        if (estadoSel === '2') {
            $('#kpi-sub-cantidad').text('0 despachos (filtrado por rechazos)');
        } else if (estadoSel === '3') {
            $('#kpi-sub-cantidad').text('0 despachos (filtrado por pendientes)');
        } else {
            $('#kpi-sub-cantidad').text(`${kpis.total_solicitudes} despachos aprobados`);
        }

        // 4. Químicos Filtrados
        $('#kpi-val-quimicos').text(kpis.total_quimicos);
        $('#kpi-sub-quimicos').text('químicos con consumos registrados');

        // 5. Químicos Registrados en Catálogo Maestro
        $('#kpi-val-quimicos-registrados').text(kpis.total_registrados || '80');
        $('#kpi-sub-quimicos-registrados').text('activos');

        // 6. Células Solicitantes
        $('#kpi-val-celulas').text(kpis.total_celulas);
        $('#kpi-sub-celulas').text('áreas con solicitudes');
    }

    // ── 4. GRÁFICO 1: TOP QUÍMICOS POR INVERSIÓN (Horizontal Bar) ──
    function renderChartCostoQuimico(items) {
        destroyChart('costoQuimico');
        const ctx = document.getElementById('chart-costo-quimico');
        if (!ctx) return;

        if (!items || items.length === 0) {
            return;
        }

        const labels = items.map(d => d.nombre_quimico);
        const costos = items.map(d => parseFloat(d.costo_total || 0));

        STATE.charts['costoQuimico'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Inversión Total',
                    data: costos,
                    backgroundColor: '#2DB992',
                    hoverBackgroundColor: '#0D7256',
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 24,
                    barThickness: labels.length === 1 ? 22 : undefined
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const row = items[ctx.dataIndex];
                                return [
                                    ` Inversión Total: ${formatCOP(row.costo_total)}`,
                                    ` Impacto en Gasto: ${row.porcentaje_impacto}%`,
                                    ` Cantidad Consumida: ${formatNum(row.cantidad_total)} ${row.umb}`,
                                    ` Precio Unitario: ${formatCOP(row.precio_unitario)} / ${row.umb}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 9 },
                            callback: v => formatCompactCOP(v)
                        },
                        beginAtZero: true
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 9 },
                            callback: function (val) {
                                const l = this.getLabelForValue(val);
                                return l.length > 36 ? l.substring(0, 34) + '...' : l;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 5. GRÁFICO 2: IMPACTO Y PARTICIPACIÓN POR CÉLULA / ÁREA (Doughnut) ──
    function renderChartPorCelula(items) {
        destroyChart('porCelula');
        const ctx = document.getElementById('chart-por-celula');
        if (!ctx) return;

        if (!items || items.length === 0) {
            return;
        }

        const labels = items.map(d => `${d.nombre_celula} (${d.porcentaje_impacto}%)`);
        const costos = items.map(d => parseFloat(d.costo_total || 0));
        const colors = labels.map((_, i) => PALETA_COLORES[i % PALETA_COLORES.length]);

        STATE.charts['porCelula'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: costos,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { family: 'Inter', size: 10 },
                            boxWidth: 12,
                            padding: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const row = items[ctx.dataIndex];
                                return [
                                    ` Célula: ${row.nombre_celula}`,
                                    ` Inversión: ${formatCOP(row.costo_total)}`,
                                    ` Impacto: ${row.porcentaje_impacto}% del total`,
                                    ` Despachos: ${row.total_solicitudes} solicitudes`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 6. GRÁFICO 3: ROTACIÓN Y DESPACHO FÍSICO DE QUÍMICOS (Horizontal Bar) ──
    function renderChartRotacionQuimico(items) {
        destroyChart('rotacionQuimico');
        const ctx = document.getElementById('chart-consumo-quimico');
        if (!ctx) return;

        if (!items || items.length === 0) {
            return;
        }

        const labels = items.map(d => d.nombre_quimico);
        const consumos = items.map(d => parseFloat(d.cantidad_total || 0));

        STATE.charts['rotacionQuimico'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Consumo Total (Volumen)',
                    data: consumos,
                    backgroundColor: '#079ABD',
                    hoverBackgroundColor: '#003D4B',
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 24,
                    barThickness: labels.length === 1 ? 22 : undefined
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const row = items[ctx.dataIndex];
                                return [
                                    ` Despachos: ${formatNum(row.cantidad_total)} ${row.umb}`,
                                    ` Frecuencia: ${row.total_despachos} solicitudes aprobadas`,
                                    ` Costo Asociado: ${formatCOP(row.costo_total)}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 9 }, callback: v => formatNum(v, 0) },
                        beginAtZero: true
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 9 },
                            callback: function (val) {
                                const l = this.getLabelForValue(val);
                                return l.length > 36 ? l.substring(0, 34) + '...' : l;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 7. GRÁFICO 4: TENDENCIA Y EVOLUCIÓN MENSUAL DEL GASTO (Mixed Chart) ──
    function renderChartTendenciaMensual(items) {
        destroyChart('tendenciaMensual');
        const ctx = document.getElementById('chart-tendencia-mensual');
        if (!ctx) return;

        if (!items || items.length === 0) {
            return;
        }

        const labels = items.map(d => `${MESES[parseInt(d.mes)].substring(0, 3)} ${d.anio}`);
        const costos = items.map(d => parseFloat(d.costo_total || 0));
        const cant = items.map(d => parseFloat(d.cantidad_total || 0));

        STATE.charts['tendenciaMensual'] = new Chart(ctx, {
            data: {
                labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Inversión en Químicos (COP)',
                        data: costos,
                        backgroundColor: 'rgba(45, 185, 146, 0.45)',
                        borderColor: '#2DB992',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        maxBarThickness: 34,
                        yAxisID: 'yCosto'
                    },
                    {
                        type: 'line',
                        label: 'Despachos Aprobados (u.)',
                        data: cant,
                        borderColor: '#079ABD',
                        backgroundColor: '#079ABD',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#079ABD',
                        yAxisID: 'yCant'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { font: { family: 'Inter', size: 10 }, boxWidth: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.yAxisID === 'yCosto') {
                                    return ` Inversión: ${formatCOP(ctx.raw)}`;
                                }
                                return ` Despachos: ${formatNum(ctx.raw)} unidades`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 9 } }
                    },
                    yCosto: {
                        type: 'linear',
                        position: 'left',
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 9 }, callback: v => formatCompactCOP(v) },
                        beginAtZero: true
                    },
                    yCant: {
                        type: 'linear',
                        position: 'right',
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 9 }, callback: v => formatNum(v, 0) + ' u.' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // ── 8. TABLA DETALLADA RESUMEN Y PAGINACIÓN ──
    function actualizarTablaDetalle(rows) {
        STATE.tablaData = rows || [];
        filtrarTabla();
    }

    function filtrarTabla() {
        const query = ($('#tabla-search').val() || '').toLowerCase().trim();
        if (!query) {
            STATE.tablaFiltrada = [...STATE.tablaData];
        } else {
            STATE.tablaFiltrada = STATE.tablaData.filter(r => {
                const text = `${r.nombre_quimico || ''} ${r.nombre_celula || ''} ${r.solicitante || ''} ${r.estado || ''} ${r.fecha_solicitud_consumo || ''}`.toLowerCase();
                return text.includes(query);
            });
        }
        STATE.paginaActual = 1;
        renderPaginaTabla();
    }

    function renderPaginaTabla() {
        const $tbody = $('#tabla-detalle-body');
        $tbody.empty();

        const total = STATE.tablaFiltrada.length;
        if (total === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fa-solid fa-flask"></i>
                        <p>No se encontraron solicitudes con los filtros seleccionados.</p>
                    </td>
                </tr>
            `);
            $('#tabla-info').text('Mostrando 0 de 0 solicitudes');
            $('#tabla-paginacion').empty();
            return;
        }

        const start = (STATE.paginaActual - 1) * STATE.porPagina;
        const end = Math.min(start + STATE.porPagina, total);
        const pageItems = STATE.tablaFiltrada.slice(start, end);

        pageItems.forEach(r => {
            let badgeEstado = '';
            const idEstado = parseInt(r.id_estado_solicitud_quimico);
            if (idEstado === 1) {
                badgeEstado = '<span class="badge-estado badge-estado-aprobado"><i class="fa-solid fa-check"></i> Aprobado</span>';
            } else if (idEstado === 2) {
                badgeEstado = '<span class="badge-estado badge-estado-rechazado"><i class="fa-solid fa-xmark"></i> Rechazado</span>';
            } else {
                badgeEstado = `<span class="badge-estado badge-estado-pendiente"><i class="fa-solid fa-clock"></i> ${r.estado || 'Pendiente'}</span>`;
            }

            const fechaFmt = r.fecha_solicitud_consumo ? r.fecha_solicitud_consumo.substring(0, 10) : '—';
            const solicitanteStr = r.solicitante ? r.solicitante : 'Sin registrar';

            $tbody.append(`
                <tr>
                    <td><span class="fecha-text">${fechaFmt}</span></td>
                    <td><strong>${r.nombre_quimico}</strong></td>
                    <td><span class="cell-badge">${r.nombre_celula}</span></td>
                    <td><span class="solicitante-text"><i class="fa-solid fa-user"></i> ${solicitanteStr}</span></td>
                    <td><strong>${formatNum(r.cantidad_solicitada)}</strong> <small class="text-muted">${r.umb}</small></td>
                    <td>${formatCOP(r.precio_unitario)}</td>
                    <td class="costo-badge">${formatCOP(r.costo_total)}</td>
                    <td>${badgeEstado}</td>
                </tr>
            `);
        });

        $('#tabla-info').text(`Mostrando ${start + 1} - ${end} de ${total} solicitudes`);

        // Paginación
        const totalPages = Math.ceil(total / STATE.porPagina);
        const $pag = $('#tabla-paginacion');
        $pag.empty();

        if (totalPages > 1) {
            $pag.append(`
                <button class="page-btn" data-page="${STATE.paginaActual - 1}" ${STATE.paginaActual === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            `);

            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= STATE.paginaActual - 1 && p <= STATE.paginaActual + 1)) {
                    $pag.append(`
                        <button class="page-btn ${p === STATE.paginaActual ? 'active' : ''}" data-page="${p}">${p}</button>
                    `);
                } else if (p === STATE.paginaActual - 2 || p === STATE.paginaActual + 2) {
                    $pag.append(`<span class="page-dots">...</span>`);
                }
            }

            $pag.append(`
                <button class="page-btn" data-page="${STATE.paginaActual + 1}" ${STATE.paginaActual === totalPages ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            `);
        }
    }

    // ── 9. EVENTOS ──
    $('#filtro-anio').on('change', function () {
        STATE.filtros.anio = $(this).val();
        cargarDashboard();
    });

    $('#filtro-mes').on('change', function () {
        STATE.filtros.mes = $(this).val();
        cargarDashboard();
    });

    $('#filtro-celula').on('change', function () {
        STATE.filtros.id_celula = $(this).val();
        cargarDashboard();
    });

    $('#filtro-quimico').on('change', function () {
        STATE.filtros.id_quimico = $(this).val();
        cargarDashboard();
    });

    $('#filtro-estado').on('change', function () {
        STATE.filtros.id_estado = $(this).val();
        cargarDashboard();
    });

    $('#btn-limpiar-filtros').on('click', function () {
        $('#filtro-anio').val('');
        $('#filtro-mes').val('');
        $('#filtro-estado').val('');
        $('#tabla-search').val('');

        if (choicesCelula) {
            choicesCelula.setChoiceByValue('');
        }
        if (choicesQuimico) {
            choicesQuimico.setChoiceByValue('');
        }

        STATE.filtros = { anio: '', mes: '', id_celula: '', id_quimico: '', id_estado: '' };
        cargarDashboard();
    });

    $('#tabla-search').on('input', function () {
        filtrarTabla();
    });

    $(document).on('click', '.page-btn:not([disabled])', function () {
        const page = parseInt($(this).data('page'));
        if (page && page !== STATE.paginaActual) {
            STATE.paginaActual = page;
            renderPaginaTabla();
        }
    });

    // ── INICIALIZACIÓN ──
    cargarFiltros().then(() => {
        cargarDashboard();
    });
});
