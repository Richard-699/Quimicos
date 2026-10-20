/**
 * indicadorConsumosAgua.js
 * Dashboard completo de consumos de agua — HWI Gestión Ambiental
 */

$(document).ready(function () {

    // ─────────────────────────────────────────────
    // ESTADO GLOBAL
    // ─────────────────────────────────────────────
    const STATE = {
        filtros: {
            id_celula: '',
            id_tanque: '',
            anio: '',
            mes: '',
            fecha: ''
        },
        datos: null,
        tablaData: [],
        tablaFiltrada: [],
        tablaPagina: 1,
        tablaPageSize: 15,
        tablaSortCol: 'fecha',
        tablaSortDir: 'desc',
        charts: {}
    };

    const HANDLER_URL = '../../Handler/quimicos/indicadorConsumosAguaHandler.php';

    const MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const PALETA_CELULAS = [
        '#003D4B', // HWI Principal: Azul Petróleo Oscuro
        '#079ABD', // HWI Principal: Azul Cyan / Teal Brillante
        '#2DB992', // HWI Secundario: Verde Esmeralda / Mint
        '#3A6872', // HWI Principal: Teal Medio
        '#FBB93F', // HWI Secundario: Amarillo / Ámbar Cálido
        '#36CFF2', // HWI Secundario: Celeste Vibrante / Aqua
        '#8BCCDD', // HWI Principal: Azul Pastel Suave
        '#1E40AF', // Azul Real
        '#059669', // Verde Bosque
        '#D97706', // Ámbar Intenso
        '#0891B2', // Cyan Profundo
        '#0F766E'  // Teal Oscuro
    ];

    // ─────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────
    async function init() {
        await Promise.all([cargarCelulas(), cargarTanques(), cargarAnios()]);
        setAnioActual();
        aplicarFiltroCelula($('#filtro-celula').val() || '');
        await cargarDashboard();
        await cargarProyeccionML();
        actualizarTimestamp();
    }

    // ─────────────────────────────────────────────
    // CARGAR SELECTS
    // ─────────────────────────────────────────────
    async function cargarCelulas() {
        try {
            const res = await fetch(`${HANDLER_URL}?action=onGet_celulas`);
            const data = await res.json();
            const sel = $('#filtro-celula');
            if (Array.isArray(data)) {
                data.forEach(c => {
                    sel.append(`<option value="${c.id_celulas_areas}">${c.nombre_celula}</option>`);
                });
            }
        } catch (e) { console.error('Error cargando células:', e); }
    }

    async function cargarTanques() {
        try {
            const res = await fetch(`${HANDLER_URL}?action=onGet_tanques`);
            const data = await res.json();
            const sel = $('#filtro-tanque');
            if (Array.isArray(data)) {
                data.forEach(t => {
                    sel.append(`<option value="${t.id_tanque_abastecimiento_agua}">${t.tanque_abastecimiento_agua}</option>`);
                });
            }
        } catch (e) { console.error('Error cargando tanques:', e); }
    }

    async function cargarAnios() {
        try {
            const res = await fetch(`${HANDLER_URL}?action=onGet_anios`);
            const data = await res.json();
            const sel = $('#filtro-anio');
            sel.append('<option value="">Todos los años</option>');
            if (Array.isArray(data)) {
                data.forEach(a => sel.append(`<option value="${a}">${a}</option>`));
            }
        } catch (e) {
            const sel = $('#filtro-anio');
            const y = new Date().getFullYear();
            sel.append(`<option value="">Todos los años</option><option value="${y}">${y}</option>`);
        }
    }

    function setAnioActual() {
        const y = new Date().getFullYear().toString();
        const opt = $(`#filtro-anio option[value="${y}"]`);
        if (opt.length) {
            $('#filtro-anio').val(y);
            STATE.filtros.anio = y;
        }
    }

    // ─────────────────────────────────────────────
    // CARGAR DATOS DEL DASHBOARD
    // ─────────────────────────────────────────────
    async function cargarDashboard() {
        mostrarCarga && mostrarCarga();

        const params = new URLSearchParams({
            action: 'onGet_dashboard_data',
            ...filterNonEmpty(STATE.filtros)
        });

        try {
            const res = await fetch(`${HANDLER_URL}?${params}`);
            const data = await res.json();

            if (!data.success) {
                console.error('Error del servidor:', data.message);
                mostrarNotificacion('Error al cargar datos: ' + data.message, 'error');
                return;
            }

            STATE.datos = data;
            STATE.tablaData = data.registros || [];
            STATE.tablaFiltrada = [...STATE.tablaData];
            STATE.tablaPagina = 1;

            renderKPIs(data.kpi);
            renderChartTendencia(data.serie_temporal || []);
            renderChartMensual(data.por_mes || []);
            renderChartCelulas(data.por_celula || []);
            renderChartTanques(data.por_tanque || []);
            renderAlertas(data.alertas || []);
            renderTabla();

        } catch (e) {
            console.error('Error fetch dashboard:', e);
            mostrarNotificacion('Error de conexión al cargar el dashboard.', 'error');
        } finally {
            ocultarCarga && ocultarCarga();
        }
    }

    // ─────────────────────────────────────────────
    // KPIs
    // ─────────────────────────────────────────────
    function renderKPIs(kpi) {
        if (!kpi) return;

        const total    = parseFloat(kpi.total_consumo || 0);
        const prom     = parseFloat(kpi.promedio_diario || 0);
        const pico     = parseFloat(kpi.pico_maximo || 0);
        const alertas  = parseInt(kpi.total_alertas || 0);
        const tope     = kpi.tope_promedio ? parseFloat(kpi.tope_promedio) : null;
        const registros = parseInt(kpi.total_registros || 0);

        animateCounter('kpi-val-total',    total,    2, ' m³');
        animateCounter('kpi-val-prom',     prom,     2, ' m³');
        animateCounter('kpi-val-pico',     pico,     2, ' m³');
        animateCounter('kpi-val-alertas',  alertas,  0, '');
        animateCounter('kpi-val-registros',registros,0, '');

        if (tope !== null) {
            animateCounter('kpi-val-tope', tope, 2, ' m³');
        } else {
            $('#kpi-val-tope').text('Sin datos ML');
        }

        // Subtextos
        const periodo = getPeriodoLabel();
        const dias = parseInt(kpi.total_dias || registros);
        $('#kpi-sub-total').text(periodo || 'del período seleccionado');
        $('#kpi-sub-prom').text(`${dias} día(s) con registros`);
        $('#kpi-sub-pico').text('mayor consumo diario registrado');
        $('#kpi-sub-alertas').text(alertas > 0 ? '⚠ Revisar anomalías' : '✓ Sin alertas críticas');
        $('#kpi-sub-tope').text(tope ? 'umbral predictivo ML' : 'tabla ML no disponible');
        $('#kpi-sub-registros').text(periodo || 'en el período');
    }

    function animateCounter(id, targetVal, decimals, suffix) {
        const el = document.getElementById(id);
        if (!el) return;
        const start = 0;
        const duration = 800;
        const startTime = performance.now();

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = start + (targetVal - start) * eased;
            el.textContent = formatNum(current, decimals) + suffix;
            if (progress < 1) requestAnimationFrame(update);
            else {
                el.textContent = formatNum(targetVal, decimals) + suffix;
                el.classList.add('counting');
                setTimeout(() => el.classList.remove('counting'), 400);
            }
        }
        requestAnimationFrame(update);
    }

    // ─────────────────────────────────────────────
    // GRÁFICOS
    // ─────────────────────────────────────────────
    function destroyChart(key) {
        if (STATE.charts[key]) {
            STATE.charts[key].destroy();
            delete STATE.charts[key];
        }
    }

    // 1. TENDENCIA EN EL TIEMPO
    function renderChartTendencia(serie) {
        destroyChart('tendencia');
        const ctx = document.getElementById('chart-tendencia');
        if (!ctx) return;

        if (!serie || serie.length === 0) {
            renderEmptyChart(ctx, 'Sin datos para el período seleccionado');
            return;
        }

        const labels   = serie.map(d => d.fecha);
        const consumos = serie.map(d => parseFloat(d.consumo_total || 0));
        const topes    = serie.map(d => d.tope_ml ? parseFloat(d.tope_ml) : null);
        const hayTopes = topes.some(v => v !== null);

        const datasets = [{
            label: 'Consumo Diario (m³)',
            data: consumos,
            borderColor: '#003D4B',
            backgroundColor: 'rgba(7, 154, 189, 0.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#003D4B',
            pointRadius: serie.length > 60 ? 2 : 4,
            pointHoverRadius: 7,
        }];

        if (hayTopes) {
            datasets.push({
                label: 'Tope ML',
                data: topes,
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.05)',
                borderWidth: 2,
                borderDash: [6, 4],
                fill: false,
                tension: 0.3,
                pointRadius: 0,
                pointHoverRadius: 5,
            });
        }

        // Marcar puntos que superan tope en rojo
        const puntosAlerta = serie.map((d, i) => {
            const c = parseFloat(d.consumo_total || 0);
            const t = d.tope_ml ? parseFloat(d.tope_ml) : null;
            return t !== null && c > t ? c : null;
        });

        datasets.push({
            label: '⚠ Tope Superado',
            data: puntosAlerta,
            borderColor: 'transparent',
            backgroundColor: '#dc2626',
            pointRadius: 6,
            pointHoverRadius: 9,
            showLine: false,
            type: 'scatter',
        });

        STATE.charts['tendencia'] = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { font: { family: 'Inter', size: 11 }, boxWidth: 14 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                if (ctx.raw === null) return null;
                                return ` ${ctx.dataset.label}: ${formatNum(ctx.raw, 2)} m³`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: serie.length > 90 ? 'month' : 'day', displayFormats: { day: 'dd MMM', month: 'MMM yyyy' } },
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 10 }, maxTicksLimit: 12 }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 10 }, callback: v => formatNum(v, 0) + ' m³' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // 2. CONSUMO MENSUAL
    function renderChartMensual(porMes) {
        destroyChart('mensual');
        const ctx = document.getElementById('chart-mensual');
        if (!ctx) return;

        if (!porMes || porMes.length === 0) {
            renderEmptyChart(ctx, 'Sin datos mensuales');
            return;
        }

        const labels   = porMes.map(d => `${MESES[parseInt(d.mes)].substring(0,3)} ${d.anio}`);
        const consumos = porMes.map(d => parseFloat(d.consumo_total || 0));

        STATE.charts['mensual'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Consumo (m³)',
                    data: consumos,
                    backgroundColor: '#079ABD',
                    hoverBackgroundColor: '#003D4B',
                    borderColor: '#003D4B',
                    borderWidth: 1,
                    borderRadius: 5,
                    borderSkipped: false,
                    maxBarThickness: 34,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${formatNum(ctx.raw, 2)} m³`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 9 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter', size: 10 }, callback: v => formatNum(v, 0) }, beginAtZero: true }
                }
            }
        });
    }

    // 3. CONSUMO POR CÉLULA
    function renderChartCelulas(porCelula) {
        destroyChart('celulas');
        const ctx = document.getElementById('chart-celulas');
        if (!ctx) return;

        if (!porCelula || porCelula.length === 0) {
            renderEmptyChart(ctx, 'Sin datos por célula');
            return;
        }

        // Tomar top 10
        const top = porCelula.slice(0, 10);
        const labels   = top.map(d => d.nombre_celula || 'Sin nombre');
        const consumos = top.map(d => parseFloat(d.consumo_total || 0));
        const colors   = labels.map((_, i) => PALETA_CELULAS[i % PALETA_CELULAS.length]);

        STATE.charts['celulas'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Consumo (m³)',
                    data: consumos,
                    backgroundColor: colors,
                    borderRadius: 5,
                    borderSkipped: false,
                    maxBarThickness: 24,
                    barThickness: labels.length === 1 ? 22 : undefined,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${formatNum(ctx.raw, 2)} m³`
                        }
                    }
                },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter', size: 9 }, callback: v => formatNum(v, 0) }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 9 }, maxTicksLimit: 12 } }
                }
            }
        });
    }

    // 4. DISTRIBUCIÓN POR TANQUE (dona)
    function renderChartTanques(porTanque) {
        destroyChart('tanques');
        const ctx = document.getElementById('chart-tanques');
        if (!ctx) return;

        const tanquesFiltrados = porTanque.filter(t => t.nombre_tanque !== 'Sin tanque' && parseFloat(t.consumo_total) > 0);

        if (!tanquesFiltrados || tanquesFiltrados.length === 0) {
            renderEmptyChart(ctx, 'Sin registros con tanques asignados');
            return;
        }

        const labels   = tanquesFiltrados.map(d => d.nombre_tanque);
        const consumos = tanquesFiltrados.map(d => parseFloat(d.consumo_total || 0));
        const colors   = labels.map((_, i) => PALETA_CELULAS[i % PALETA_CELULAS.length]);

        STATE.charts['tanques'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: consumos,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 10 }, boxWidth: 12, padding: 10 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ` ${formatNum(ctx.raw, 2)} m³ (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 5. PROYECCIÓN ML
    async function cargarProyeccionML() {
        const params = new URLSearchParams({
            action: 'onGet_proyeccion_ml',
            ...filterNonEmpty({ id_celula: STATE.filtros.id_celula, id_tanque: STATE.filtros.id_tanque })
        });

        try {
            const res = await fetch(`${HANDLER_URL}?${params}`);
            const data = await res.json();
            if (data.success) renderChartProyeccion(data.historico, data.proyeccion);
        } catch (e) {
            console.error('Error proyección ML:', e);
        }
    }

    function renderChartProyeccion(historico, proyeccion) {
        destroyChart('proyeccion');
        const ctx = document.getElementById('chart-proyeccion');
        if (!ctx) return;

        if (!historico || historico.length === 0) {
            renderEmptyChart(ctx, 'Sin datos históricos para proyectar');
            return;
        }

        const labelsHist = historico.map(d => d.fecha_mes);
        const consHist   = historico.map(d => parseFloat(d.consumo_mensual || 0));

        const labelsProj = (proyeccion || []).map(d => d.fecha_mes);
        const consProj   = (proyeccion || []).map(d => parseFloat(d.consumo_proyectado || 0));

        const allLabels = [...labelsHist, ...labelsProj];

        // Datos históricos posicionados en todas las posiciones
        const dataHistFull = allLabels.map((l, i) => i < labelsHist.length ? consHist[i] : null);
        const dataProjFull = allLabels.map((l, i) => i >= labelsHist.length ? consProj[i - labelsHist.length] : null);
        // Conexión suave entre histórico y proyectado
        if (labelsHist.length > 0 && labelsProj.length > 0) {
            dataProjFull[labelsHist.length - 1] = consHist[labelsHist.length - 1];
        }

        const datasets = [
            {
                label: 'Consumo Histórico (m³)',
                data: dataHistFull,
                borderColor: '#003D4B',
                backgroundColor: 'rgba(0, 61, 75, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#003D4B',
            },
            {
                label: 'Proyección ML (m³)',
                data: dataProjFull,
                borderColor: '#2DB992',
                backgroundColor: 'rgba(45, 185, 146, 0.08)',
                borderWidth: 2.5,
                borderDash: [8, 4],
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#2DB992',
            }
        ];

        STATE.charts['proyeccion'] = new Chart(ctx, {
            type: 'line',
            data: { labels: allLabels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { font: { family: 'Inter', size: 11 }, boxWidth: 14 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                if (ctx.raw === null) return null;
                                return ` ${ctx.dataset.label}: ${formatNum(ctx.raw, 2)} m³`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'month', displayFormats: { month: 'MMM yyyy' } },
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 10 }, maxTicksLimit: 14 }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 10 }, callback: v => formatNum(v, 0) + ' m³' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // ─────────────────────────────────────────────
    // ALERTAS ML
    // ─────────────────────────────────────────────
    function renderAlertas(alertas) {
        const container = $('#alertas-lista');
        container.empty();

        if (!alertas || alertas.length === 0) {
            container.html(`
                <div class="dash-empty-state">
                    <i class="fa-solid fa-shield-check" style="color:#10b981;"></i>
                    <p>Sin alertas ML detectadas<br><small>No se han superado topes en el período.</small></p>
                </div>`);
            return;
        }

        alertas.forEach((a, idx) => {
            const neto    = parseFloat(a.consumo_neto || 0);
            const tope    = a.tope_calculado ? parseFloat(a.tope_calculado) : null;
            const exceso  = tope ? ((neto - tope) / tope * 100).toFixed(1) : null;
            const score   = a.score_anomalia ? parseFloat(a.score_anomalia).toFixed(3) : 'N/A';

            container.append(`
                <div class="dash-alerta-item" style="animation-delay:${idx * 0.04}s">
                    <div class="alerta-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="alerta-body">
                        <div class="alerta-title">
                            ${a.nombre_celula || 'Célula'}
                            ${a.nombre_tanque !== 'N/A' ? ' · ' + a.nombre_tanque : ''}
                        </div>
                        <div class="alerta-meta">
                            <span><i class="fa-regular fa-calendar"></i> ${formatFecha(a.fecha_consumo_agua)}</span>
                            <span><i class="fa-solid fa-droplet"></i> ${formatNum(neto, 2)} m³</span>
                            ${tope ? `<span><i class="fa-solid fa-shield"></i> Tope: ${formatNum(tope, 2)} m³</span>` : ''}
                        </div>
                        ${exceso ? `<div class="alerta-badge">+${exceso}% sobre el tope · Score: ${score}</div>` : ''}
                    </div>
                </div>`);
        });
    }

    // ─────────────────────────────────────────────
    // TABLA INTERACTIVA
    // ─────────────────────────────────────────────
    function renderTabla() {
        // Aplicar búsqueda
        const query = $('#dash-table-search').val().toLowerCase().trim();
        let filtered = [...STATE.tablaData];

        if (query) {
            filtered = filtered.filter(r =>
                (r.nombre_celula || '').toLowerCase().includes(query) ||
                (r.nombre_tanque || '').toLowerCase().includes(query) ||
                (r.fecha_consumo_agua || '').toLowerCase().includes(query)
            );
        }

        // Aplicar sort
        filtered.sort((a, b) => {
            let va, vb;
            switch (STATE.tablaSortCol) {
                case 'fecha':   va = a.fecha_consumo_agua;     vb = b.fecha_consumo_agua; break;
                case 'celula':  va = a.nombre_celula || '';    vb = b.nombre_celula || ''; break;
                case 'tanque':  va = a.nombre_tanque || '';    vb = b.nombre_tanque || ''; break;
                case 'inicial': va = parseFloat(a.consumo_inicial_agua); vb = parseFloat(b.consumo_inicial_agua); break;
                case 'final':   va = parseFloat(a.consumo_final_agua);   vb = parseFloat(b.consumo_final_agua);   break;
                case 'neto':    va = parseFloat(a.consumo_neto);         vb = parseFloat(b.consumo_neto);         break;
                default:        va = a.fecha_consumo_agua;     vb = b.fecha_consumo_agua;
            }
            const cmp = va < vb ? -1 : va > vb ? 1 : 0;
            return STATE.tablaSortDir === 'asc' ? cmp : -cmp;
        });

        STATE.tablaFiltrada = filtered;

        // Paginación
        const total    = filtered.length;
        const pageSize = STATE.tablaPageSize;
        const page     = STATE.tablaPagina;
        const pages    = Math.max(1, Math.ceil(total / pageSize));
        STATE.tablaPagina = Math.min(page, pages);
        const start = (STATE.tablaPagina - 1) * pageSize;
        const slice = filtered.slice(start, start + pageSize);

        const tbody = $('#tabla-indicador-body');
        tbody.empty();

        if (slice.length === 0) {
            tbody.append(`<tr><td colspan="8" class="dash-table-loading">
                <i class="fa-solid fa-magnifying-glass"></i> Sin resultados para la búsqueda.
            </td></tr>`);
        } else {
            slice.forEach(r => {
                const neto = parseFloat(r.consumo_neto || (r.consumo_final_agua - r.consumo_inicial_agua) || 0);
                let estadoBadge;
                if (r.supera_tope === null || r.supera_tope === undefined) {
                    estadoBadge = `<span class="estado-sin-ml"><i class="fa-solid fa-minus"></i> Sin ML</span>`;
                } else if (r.supera_tope == 1) {
                    estadoBadge = `<span class="estado-alerta"><i class="fa-solid fa-triangle-exclamation"></i> Tope Superado</span>`;
                } else {
                    estadoBadge = `<span class="estado-ok"><i class="fa-solid fa-check"></i> Normal</span>`;
                }

                tbody.append(`
                    <tr>
                        <td>${formatFechaHora(r.fecha_consumo_agua)}</td>
                        <td>${r.nombre_celula || '—'}</td>
                        <td>${r.nombre_tanque || 'Sin tanque'}</td>
                        <td class="text-right">${formatNum(r.consumo_inicial_agua, 2)}</td>
                        <td class="text-right">${formatNum(r.consumo_final_agua, 2)}</td>
                        <td class="text-right fw-600">${formatNum(neto, 2)} m³</td>
                        <td>${estadoBadge}</td>
                        <td>
                            ${!String(r.id_consumo_agua).startsWith('humano') ? `
                            <button class="btn-edit-consumo" 
                                data-id="${r.id_consumo_agua}"
                                data-celula="${(r.nombre_celula || '').replace(/"/g, '&quot;')}"
                                data-tanque="${(r.nombre_tanque || 'Sin tanque').replace(/"/g, '&quot;')}"
                                data-inicial="${r.consumo_inicial_agua}"
                                data-final="${r.consumo_final_agua}"
                                title="Editar consumo final">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>` : '—'}
                        </td>
                    </tr>`);
            });
        }

        // Info
        $('#dash-table-info').text(`Mostrando ${start + 1}–${Math.min(start + pageSize, total)} de ${total} registros`);
        renderPaginacion(pages);
    }

    function renderPaginacion(pages) {
        const container = $('#dash-pagination');
        container.empty();
        if (pages <= 1) return;

        const cur = STATE.tablaPagina;

        // Prev
        container.append(`<button class="page-btn" ${cur === 1 ? 'disabled' : ''} data-page="${cur - 1}">
            <i class="fa-solid fa-chevron-left"></i></button>`);

        // Páginas
        const range = getPageRange(cur, pages);
        range.forEach(p => {
            if (p === '...') {
                container.append(`<button class="page-btn" disabled>…</button>`);
            } else {
                container.append(`<button class="page-btn ${p === cur ? 'active' : ''}" data-page="${p}">${p}</button>`);
            }
        });

        // Next
        container.append(`<button class="page-btn" ${cur === pages ? 'disabled' : ''} data-page="${cur + 1}">
            <i class="fa-solid fa-chevron-right"></i></button>`);
    }

    function getPageRange(cur, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (cur <= 4) return [1, 2, 3, 4, 5, '...', total];
        if (cur >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        return [1, '...', cur - 1, cur, cur + 1, '...', total];
    }

    // ─────────────────────────────────────────────
    // FILTROS ACTIVOS (TAGS)
    // ─────────────────────────────────────────────
    function actualizarTagsFiltros() {
        const container = $('#filtros-activos-tags');
        container.empty();

        const labels = {
            id_celula: nombre => `Célula: ${nombre || STATE.filtros.id_celula}`,
            id_tanque: nombre => `Tanque: ${nombre || STATE.filtros.id_tanque}`,
            anio:  v => `Año: ${v}`,
            mes:   v => `Mes: ${MESES[parseInt(v)]}`,
            fecha: v => `Día: ${formatFecha(v)}`,
        };

        let hayFiltros = false;

        Object.entries(STATE.filtros).forEach(([key, val]) => {
            if (!val) return;
            hayFiltros = true;
            let texto;

            if (key === 'id_celula') {
                const opt = $(`#filtro-celula option[value="${val}"]`);
                texto = labels.id_celula(opt.length ? opt.text() : val);
            } else if (key === 'id_tanque') {
                const opt = $(`#filtro-tanque option[value="${val}"]`);
                texto = labels.id_tanque(opt.length ? opt.text() : val);
            } else {
                texto = labels[key](val);
            }

            container.append(`
                <span class="dash-filter-tag" data-key="${key}">
                    ${texto}
                    <span class="tag-remove" data-key="${key}">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                </span>`);
        });

        $('#filtros-activos-container').toggle(hayFiltros);
    }

    // Eliminar un filtro al hacer clic en la X del tag
    $(document).on('click', '.tag-remove', function () {
        const key = $(this).data('key');
        STATE.filtros[key] = '';
        // Reset el select/input correspondiente
        const elMap = {
            id_celula: '#filtro-celula',
            id_tanque: '#filtro-tanque',
            anio: '#filtro-anio',
            mes: '#filtro-mes',
            fecha: '#filtro-fecha',
        };
        $(elMap[key]).val('');

        if (key === 'id_celula') {
            aplicarFiltroCelula('');
        }

        actualizarTagsFiltros();
        cargarDashboard();
        if (['id_celula', 'id_tanque'].includes(key)) cargarProyeccionML();
    });

    // ─────────────────────────────────────────────
    // GESTIÓN DE FILTROS Y EVENTOS
    // ─────────────────────────────────────────────

    function aplicarFiltroCelula(val) {
        STATE.filtros.id_celula = val;

        // Si tenía un tanque seleccionado y cambio de célula, se quita ese filtro
        $('#filtro-tanque').val('');
        STATE.filtros.id_tanque = '';

        // Si selecciono en área una diferente a "Recubrimiento" (id = 3), se bloquea el filtro de tanques
        if (val !== '3') {
            $('#filtro-tanque').prop('disabled', true);
            $('#grupo-tanque').addClass('dash-filter-disabled');
        } else {
            $('#filtro-tanque').prop('disabled', false);
            $('#grupo-tanque').removeClass('dash-filter-disabled');
        }
    }

    // Cambio de célula
    $('#filtro-celula').on('change', function () {
        aplicarFiltroCelula($(this).val());
        actualizarTagsFiltros();
    });

    // Cambio de tanque
    $('#filtro-tanque').on('change', function () {
        STATE.filtros.id_tanque = $(this).val();
        actualizarTagsFiltros();
    });

    // Cambio de año
    $('#filtro-anio').on('change', function () {
        STATE.filtros.anio = $(this).val();
        actualizarTagsFiltros();
    });

    // Cambio de mes (si seleccioné mes, se quita la fecha seleccionada)
    $('#filtro-mes').on('change', function () {
        const mesVal = $(this).val();
        STATE.filtros.mes = mesVal;
        if (mesVal) {
            $('#filtro-fecha').val('');
            STATE.filtros.fecha = '';
        }
        actualizarTagsFiltros();
    });

    // Cambio de fecha / día (si seleccioné fecha, se quita el mes seleccionado)
    $('#filtro-fecha').on('change', function () {
        const fechaVal = $(this).val();
        STATE.filtros.fecha = fechaVal;
        if (fechaVal) {
            $('#filtro-mes').val('');
            STATE.filtros.mes = '';
        }
        actualizarTagsFiltros();
    });

    // Aplicar filtros
    $('#btn-aplicar-filtros').on('click', function () {
        STATE.filtros.id_celula = $('#filtro-celula').val();
        STATE.filtros.id_tanque = $('#filtro-tanque').val();
        STATE.filtros.anio      = $('#filtro-anio').val();
        STATE.filtros.mes       = $('#filtro-mes').val();
        STATE.filtros.fecha     = $('#filtro-fecha').val();
        STATE.tablaPagina = 1;
        actualizarTagsFiltros();
        cargarDashboard();
        cargarProyeccionML();
    });

    // Limpiar filtros
    $('#btn-limpiar-filtros').on('click', function () {
        STATE.filtros = { id_celula: '', id_tanque: '', anio: '', mes: '', fecha: '' };
        $('#filtro-celula, #filtro-tanque, #filtro-mes, #filtro-fecha').val('');
        aplicarFiltroCelula('');
        setAnioActual();
        STATE.tablaPagina = 1;
        actualizarTagsFiltros();
        cargarDashboard();
        cargarProyeccionML();
    });

    // Búsqueda en tabla
    let searchTimeout;
    $('#dash-table-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            STATE.tablaPagina = 1;
            renderTabla();
        }, 280);
    });

    // ─────────────────────────────────────────────
    // EDITAR CONSUMO (FANCYBOX)
    // ─────────────────────────────────────────────
    $(document).on('click', '.btn-edit-consumo', function () {
        const id      = $(this).data('id');
        const celula  = $(this).data('celula') || '';
        const tanque  = $(this).data('tanque') || '';
        const inicial = $(this).data('inicial') || 0;
        const final   = $(this).data('final') || 0;

        const url = `_editarConsumoAgua.php?id=${id}&celula=${encodeURIComponent(celula)}&tanque=${encodeURIComponent(tanque)}&inicial=${inicial}&final=${final}&v=${Date.now()}`;

        if (typeof Fancybox !== 'undefined') {
            Fancybox.show([{ src: url, type: 'ajax' }], {
                on: {
                    destroy: () => {
                        cargarDashboard();
                        cargarProyeccionML();
                        actualizarTimestamp();
                    }
                },
                click: false,
                trapFocus: false,
                placeFocusBack: false
            });
        } else {
            console.error('Fancybox no está disponible');
        }
    });

    // Paginación
    $(document).on('click', '.page-btn:not([disabled])', function () {
        const p = parseInt($(this).data('page'));
        if (!isNaN(p)) {
            STATE.tablaPagina = p;
            renderTabla();
        }
    });

    // Sort por columna
    $(document).on('click', '#tabla-indicador thead th[data-sort]', function () {
        const col = $(this).data('sort');
        if (STATE.tablaSortCol === col) {
            STATE.tablaSortDir = STATE.tablaSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            STATE.tablaSortCol = col;
            STATE.tablaSortDir = 'asc';
        }
        renderTabla();
    });

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    function filterNonEmpty(obj) {
        return Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v !== null && v !== undefined));
    }

    function formatNum(val, dec) {
        const n = parseFloat(val);
        if (isNaN(n)) return '—';
        return n.toLocaleString('es-CO', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }

    function formatFecha(str) {
        if (!str) return '—';
        const d = str.toString().substring(0, 10);
        const parts = d.split('-');
        if (parts.length !== 3) return d;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function formatFechaHora(str) {
        if (!str) return '—';
        const fecha = str.substring(0, 10);
        const hora = str.length > 10 ? str.substring(11, 16) : '';
        return hora ? `${formatFecha(fecha)} ${hora}` : formatFecha(fecha);
    }

    function getPeriodoLabel() {
        const { id_celula, anio, mes, fecha } = STATE.filtros;
        const parts = [];
        if (fecha) return `Día ${formatFecha(fecha)}`;
        if (anio) parts.push(anio);
        if (mes) parts.push(MESES[parseInt(mes)]);
        if (id_celula) {
            const opt = $(`#filtro-celula option[value="${id_celula}"]`);
            if (opt.length) parts.push(opt.text());
        }
        return parts.join(' · ') || 'todo el historial';
    }

    function actualizarTimestamp() {
        const now = new Date();
        const hora = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
        $('#dash-last-update').text(`Actualizado a las ${hora}`);
    }

    function renderEmptyChart(ctx, msg) {
        // Dibujar texto centrado en canvas
        const chart2d = ctx.getContext('2d');
        ctx.width = ctx.offsetWidth;
        chart2d.clearRect(0, 0, ctx.width, ctx.height);
        chart2d.fillStyle = '#94a3b8';
        chart2d.font = "13px Inter, sans-serif";
        chart2d.textAlign = "center";
        chart2d.fillText(msg, ctx.width / 2, 100);
    }

    // ─────────────────────────────────────────────
    // ARRANQUE
    // ─────────────────────────────────────────────
    init();
});
