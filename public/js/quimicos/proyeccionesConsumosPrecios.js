/**
 * ============================================================================
 * PROYECCIONES DE CONSUMO Y PRECIOS - CONTROLADOR CLIENTE
 * ============================================================================
 */

(function ($) {
    'use strict';

    const HANDLER_URL = '../../Handler/quimicos/proyeccionesConsumosPreciosHandler.php';

    const STATE = {
        quimico: '',
        celula: 'Todas',
        verAnioSiguiente: false,
        umb: '',
        kpis: null,
        chartConsumo: null,
        chartGasto: null,
        choicesQuimico: null,
        historialChat: [],
        isLoading: false
    };

    // ------------------------------------------------------------------------
    // UTILIDADES DE FORMATO
    // ------------------------------------------------------------------------
    function formatCOP(valor) {
        if (valor === null || valor === undefined || isNaN(valor)) return '$0';
        const num = Math.round(Number(valor));
        return '$' + num.toLocaleString('es-CO');
    }

    function formatNumber(valor, decimals = 1) {
        if (valor === null || valor === undefined || isNaN(valor)) return '0';
        return Number(valor).toLocaleString('es-CO', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function formatFechaMes(fechaStr) {
        if (!fechaStr) return '';
        const partes = fechaStr.split('-');
        if (partes.length < 2) return fechaStr;
        const anio = partes[0];
        const mesIdx = parseInt(partes[1], 10) - 1;
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return `${meses[mesIdx] || ''} ${anio}`;
    }

    function parseMarkdown(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Negrita **texto**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Cursiva *texto*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Saltos de línea
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }

    // ------------------------------------------------------------------------
    // INICIALIZACIÓN
    // ------------------------------------------------------------------------
    $(document).ready(function () {
        initChoices();
        bindEvents();
        cargarFiltros();
        posicionarChatFijo();
    });

    function initChoices() {
        const el = document.getElementById('select-quimico');
        if (el) {
            STATE.choicesQuimico = new Choices(el, {
                searchEnabled: true,
                searchPlaceholderValue: 'Buscar químico...',
                itemSelectText: '',
                noResultsText: 'No se encontraron químicos',
                noChoicesText: 'Sin opciones disponibles',
                shouldSort: false
            });
        }
    }

    function bindEvents() {
        // Redimensionamiento de ventana, scroll y menú lateral
        $(window).on('resize', posicionarChatFijo);
        $('.content').on('scroll', posicionarChatFijo);
        $(window).on('scroll', posicionarChatFijo);
        $('#menuToggle').on('click', function () {
            setTimeout(posicionarChatFijo, 350);
        });

        // Cambio de químico
        $('#select-quimico').on('change', function () {
            STATE.quimico = $(this).val();
            resetChatSession();
            cargarProyeccion();
        });

        // Cambio de célula
        $('#select-celula').on('change', function () {
            STATE.celula = $(this).val();
            cargarProyeccion();
        });

        // Checkbox año siguiente
        $('#chk-anio-siguiente').on('change', function () {
            STATE.verAnioSiguiente = $(this).is(':checked');
            cargarProyeccion();
        });

        // Acordeón de ingresos
        $('#btn-toggle-ingresos').on('click', function () {
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
            $('#body-accordion-ingresos').toggleClass('open');
        });

        // Formulario de chat
        $('#chat-form').on('submit', function (e) {
            e.preventDefault();
            enviarMensajeChat();
        });

        // Reiniciar chat
        $('#btn-clean-chat').on('click', function () {
            resetChatSession();
        });
    }

    // Posicionamiento dinámico del panel de chat flotante fijo
    function posicionarChatFijo() {
        const $card = $('.proy-chat-card');
        const $col = $('.proy-col-chat');
        if (!$col.length || !$card.length) return;

        if (window.innerWidth <= 1100) {
            $card.removeClass('chat-fixed').css({
                position: 'relative',
                top: '',
                bottom: '',
                left: '',
                width: '',
                height: '580px',
                maxHeight: 'none',
                zIndex: ''
            });
            return;
        }

        const colRect = $col[0].getBoundingClientRect();
        if (colRect.width === 0) return;

        // Top mínimo del chat en el viewport al hacer scroll (hasta arriba sin espacio en blanco)
        const topMinimo = 12;
        const bottomMargen = 16;

        // Inicio natural de la columna en el viewport actual
        const colTop = colRect.top;

        // Si el banner está visible arriba (colTop > topMinimo), el chat empieza en colTop
        // para NO tapar el banner superior. Al scrollear, sube hasta arriba (topMinimo).
        const topActual = Math.max(topMinimo, colTop);
        const alturaDisponible = window.innerHeight - topActual - bottomMargen;

        $card.addClass('chat-fixed').css({
            position: 'fixed',
            top: topActual + 'px',
            bottom: bottomMargen + 'px',
            left: colRect.left + 'px',
            width: colRect.width + 'px',
            height: alturaDisponible + 'px',
            maxHeight: alturaDisponible + 'px',
            zIndex: 1050
        });
    }

    // ------------------------------------------------------------------------
    // CARGAR FILTROS DESDE EL SERVICIO
    // ------------------------------------------------------------------------
    function cargarFiltros() {
        if (typeof mostrarCarga === 'function') mostrarCarga();

        $.ajax({
            url: HANDLER_URL,
            type: 'GET',
            data: { action: 'onGet_filtros' },
            dataType: 'json'
        }).done(function (res) {
            if (!res.quimicos || res.quimicos.length === 0) {
                mostrarEmptyState('No se encontraron químicos en el catálogo de proyecciones.');
                return;
            }

            // Llenar select químicos con Choices.js
            const choicesList = res.quimicos.map(function (q, idx) {
                return {
                    value: q,
                    label: q,
                    selected: idx === 0
                };
            });

            if (STATE.choicesQuimico) {
                STATE.choicesQuimico.setChoices(choicesList, 'value', 'label', true);
            }

            STATE.quimico = res.quimicos[0];

            // Llenar select de células
            const $cel = $('#select-celula');
            $cel.empty();
            (res.celulas || ['Todas']).forEach(function (c) {
                $cel.append($('<option>', { value: c, text: c }));
            });
            STATE.celula = 'Todas';

            resetChatSession();
            cargarProyeccion();
        }).fail(function (err) {
            console.error('Error cargando filtros:', err);
            mostrarEmptyState('Error de comunicación con el motor de proyecciones.');
        }).always(function () {
            if (typeof ocultarCarga === 'function') ocultarCarga();
            setTimeout(posicionarChatFijo, 50);
        });
    }

    // ------------------------------------------------------------------------
    // CARGAR PROYECCIÓN DE DEMANDA Y PRECIOS
    // ------------------------------------------------------------------------
    function cargarProyeccion() {
        if (!STATE.quimico) return;

        if (typeof mostrarCarga === 'function') mostrarCarga();
        STATE.isLoading = true;

        $.ajax({
            url: HANDLER_URL,
            type: 'GET',
            data: {
                action: 'onGet_proyeccion',
                quimico: STATE.quimico,
                celula: STATE.celula,
                ver_anio_siguiente: STATE.verAnioSiguiente ? 1 : 0
            },
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || res.sin_datos) {
                mostrarEmptyState(res.mensaje || `No hay registros de consumo para ${STATE.quimico} en la célula ${STATE.celula}.`);
                return;
            }

            mostrarResultadosState();
            STATE.umb = res.umb || 'Unidad';
            STATE.kpis = res.kpis || {};

            renderEncabezadoYKPIs(res);
            renderLogistica(res.kpis);
            renderGraficoConsumo(res.historico || [], res.proyectado || [], STATE.umb);
            renderGraficoGasto(res.historico || [], res.proyectado || []);
            renderTablaIngresos(res.ingresos || [], STATE.umb);

            actualizarSaludoChat(res.kpis, STATE.umb);
        }).fail(function (err) {
            console.error('Error obteniendo proyección:', err);
            mostrarEmptyState('Ocurrió un error al procesar el pronóstico de demanda con Machine Learning.');
        }).always(function () {
            STATE.isLoading = false;
            if (typeof ocultarCarga === 'function') ocultarCarga();
            setTimeout(posicionarChatFijo, 50);
        });
    }

    // ------------------------------------------------------------------------
    // RENDERIZADO VISUAL DEL DASHBOARD
    // ------------------------------------------------------------------------
    function renderEncabezadoYKPIs(data) {
        const k = data.kpis || {};
        const umb = data.umb || 'u.';

        $('#proy-nombre-quimico').text(data.quimico);
        $('#proy-badge-umb').text(umb);

        // KPI 1: Precio Actual
        $('#kpi-precio').text(formatCOP(k.precio));
        $('#kpi-precio-sub').text(`Por ${umb}`);

        // KPI 2: Promedio Histórico
        $('#kpi-promedio').text(`${formatNumber(k.c_prom, 1)} ${umb}`);

        // KPI 3: Estimado Próximo Mes
        $('#kpi-estimado').text(`${formatNumber(k.c_pred, 1)} ${umb}`);
        const delta = Number(k.var) || 0;
        const $badgeDelta = $('#kpi-delta');
        const sign = delta > 0 ? '+' : '';
        $badgeDelta.text(`${sign}${delta.toFixed(1)}% vs Prom`);
        $badgeDelta.removeClass('delta-up delta-down');
        if (delta > 0) {
            $badgeDelta.addClass('delta-up');
        } else if (delta < 0) {
            $badgeDelta.addClass('delta-down');
        }

        // KPI 4: Stock Disponible
        $('#kpi-stock').text(`${formatNumber(k.stock_act, 1)} ${umb}`);
        $('#kpi-stock-limites').text(`Mín: ${formatNumber(k.stock_min, 0)} | Máx: ${formatNumber(k.stock_max, 0)} ${umb}`);

        // Título del acordeón
        $('#lbl-accordion-ingresos').text(`Ingresos a Inventario de ${data.quimico}`);
    }

    function renderLogistica(k) {
        if (!k) return;
        $('#logistics-lead-time').text(`${k.lt_min || 0} a ${k.lt_max || 0} días hábiles`);
        $('#logistics-estado').text(k.momento_reorden || 'En nivel óptimo');
        $('#logistics-sugerido').text(`${formatNumber(k.sug_pedir, 1)} ${STATE.umb}`);
    }

    // ------------------------------------------------------------------------
    // GRÁFICO 1: TENDENCIA DE CONSUMO (LINE CHART)
    // ------------------------------------------------------------------------
    function renderGraficoConsumo(historico, proyectado, umb) {
        const ctx = document.getElementById('chart-consumo');
        if (!ctx) return;

        $('#title-chart-consumo').html(
            `<i class="fa-solid fa-chart-area" aria-hidden="true"></i> Tendencia de Consumo (${umb}) - Histórico vs Pronóstico ML`
        );

        if (STATE.chartConsumo) {
            STATE.chartConsumo.destroy();
        }

        // Consolidar fechas ordenadas únicas
        const todasFechas = [];
        historico.forEach(h => { if (!todasFechas.includes(h.fecha)) todasFechas.push(h.fecha); });
        proyectado.forEach(p => { if (!todasFechas.includes(p.fecha)) todasFechas.push(p.fecha); });
        todasFechas.sort();

        // Mapear series
        const histMap = new Map(historico.map(h => [h.fecha, h.consumo_kg]));
        const proyMap = new Map(proyectado.map(p => [p.fecha, p.consumo_kg]));

        // Para continuidad visual, el último punto histórico puede anclar el inicio del proyectado
        const dataHist = todasFechas.map(f => histMap.has(f) ? histMap.get(f) : null);
        const dataProy = todasFechas.map(f => proyMap.has(f) ? proyMap.get(f) : null);

        const labels = todasFechas.map(formatFechaMes);

        STATE.chartConsumo = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Histórico',
                        data: dataHist,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 6,
                        pointRadius: 4,
                        borderWidth: 2.5,
                        tension: 0.25,
                        fill: false
                    },
                    {
                        label: 'Pronóstico ML',
                        data: dataProy,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        pointBackgroundColor: '#f97316',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 6,
                        pointRadius: 4,
                        borderWidth: 2.5,
                        borderDash: [5, 5],
                        tension: 0.25,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 61, 75, 0.95)',
                        padding: 10,
                        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                if (context.parsed.y === null || context.parsed.y === undefined) return null;
                                return ` ${context.dataset.label}: ${formatNumber(context.parsed.y, 1)} ${umb}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b',
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b',
                            callback: function (val) {
                                return formatNumber(val, 0);
                            }
                        }
                    }
                }
            }
        });
    }

    // ------------------------------------------------------------------------
    // GRÁFICO 2: PROYECCIÓN DE GASTO TOTAL (BAR CHART)
    // ------------------------------------------------------------------------
    function renderGraficoGasto(historico, proyectado) {
        const ctx = document.getElementById('chart-gasto');
        if (!ctx) return;

        if (STATE.chartGasto) {
            STATE.chartGasto.destroy();
        }

        const todasFechas = [];
        historico.forEach(h => { if (!todasFechas.includes(h.fecha)) todasFechas.push(h.fecha); });
        proyectado.forEach(p => { if (!todasFechas.includes(p.fecha)) todasFechas.push(p.fecha); });
        todasFechas.sort();

        const histMap = new Map(historico.map(h => [h.fecha, h.gasto_total]));
        const proyMap = new Map(proyectado.map(p => [p.fecha, p.gasto_total]));

        const dataHist = todasFechas.map(f => histMap.has(f) ? histMap.get(f) : 0);
        const dataProy = todasFechas.map(f => proyMap.has(f) ? proyMap.get(f) : 0);

        const labels = todasFechas.map(formatFechaMes);

        STATE.chartGasto = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gasto Histórico',
                        data: dataHist,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        maxBarThickness: 32
                    },
                    {
                        label: 'Gasto Proyectado',
                        data: dataProy,
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                        maxBarThickness: 32
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 61, 75, 0.95)',
                        padding: 10,
                        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                if (!context.parsed.y) return null;
                                return ` ${context.dataset.label}: ${formatCOP(context.parsed.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b',
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b',
                            callback: function (val) {
                                return formatCOP(val);
                            }
                        }
                    }
                }
            }
        });
    }

    // ------------------------------------------------------------------------
    // TABLA DE INGRESOS A INVENTARIO
    // ------------------------------------------------------------------------
    function renderTablaIngresos(ingresos, umb) {
        const $tbody = $('#tbody-ingresos');
        $tbody.empty();

        if (!ingresos || ingresos.length === 0) {
            $tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">No hay registros de ingresos a inventario para este producto.</td></tr>');
            return;
        }

        ingresos.forEach(function (ing) {
            const tr = `
                <tr>
                    <td><i class="fa-regular fa-calendar-check text-muted me-2"></i><strong>${ing.fecha}</strong></td>
                    <td><span class="badge bg-light text-dark border">${formatNumber(ing.cantidad, 1)} ${umb}</span></td>
                </tr>
            `;
            $tbody.append(tr);
        });
    }

    // ------------------------------------------------------------------------
    // ESTADOS DE VISTA (RESULTADOS VS VACÍO)
    // ------------------------------------------------------------------------
    function mostrarResultadosState() {
        $('#proy-results-container').removeClass('d-none');
        $('#proy-empty-state').addClass('d-none');
    }

    function mostrarEmptyState(mensaje) {
        $('#proy-results-container').addClass('d-none');
        $('#proy-empty-state').removeClass('d-none');
        $('#proy-empty-message').text(mensaje);

        actualizarSaludoChat(null, '');
    }

    // ------------------------------------------------------------------------
    // CHATBOT DE ASISTENCIA ANALÍTICA
    // ------------------------------------------------------------------------
    function resetChatSession() {
        STATE.historialChat = [];
        $('#chat-messages-container').empty();
        actualizarSaludoChat(STATE.kpis, STATE.umb);
    }

    function actualizarSaludoChat(k, umb) {
        const $container = $('#chat-messages-container');
        // Solo sobreescribir el saludo si el usuario aún no ha iniciado conversación
        if (STATE.historialChat.length > 0) return;

        let saludo = '';
        if (!k || k.stock_act === undefined || k.stock_act === null) {
            saludo = `¡Hola! 👋 Soy tu asistente de inventario HWI.<br><br>No tengo registros suficientes de inventario para <strong>${STATE.quimico || 'este químico'}</strong> en la célula <strong>${STATE.celula}</strong>, pero puedo responder tus dudas generales.`;
        } else {
            saludo = `¡Hola! 👋 Soy tu asistente de inventario HWI.<br><br>Actualmente contamos con <strong>${formatNumber(k.stock_act, 1)} ${umb}</strong> disponibles de <strong>${STATE.quimico}</strong>.<br><br>¿En qué puedo ayudarte?`;
        }

        $container.empty();
        appendBubble('bot', saludo, false);
    }

    function appendBubble(role, htmlText, scroll = true) {
        const isBot = role === 'bot';
        const avatarIcon = isBot ? 'fa-robot' : 'fa-user';
        const rowClass = isBot ? 'proy-msg-bot' : 'proy-msg-user';

        const bubbleHtml = `
            <div class="proy-msg-row ${rowClass}">
                <div class="proy-msg-avatar">
                    <i class="fa-solid ${avatarIcon}" aria-hidden="true"></i>
                </div>
                <div class="proy-msg-bubble">
                    ${htmlText}
                </div>
            </div>
        `;

        const $container = $('#chat-messages-container');
        $container.append(bubbleHtml);

        if (scroll) {
            $container.stop().animate({ scrollTop: $container[0].scrollHeight }, 300);
        }
    }

    function enviarMensajeChat() {
        const $input = $('#chat-input');
        const prompt = $input.val().trim();
        if (!prompt) return;

        // Renderizar mensaje del usuario
        appendBubble('user', parseMarkdown(prompt), true);
        $input.val('');

        // Registrar en historial local
        STATE.historialChat.push({ role: 'user', content: prompt });

        // Mostrar indicador de tipeo y deshabilitar botón
        $('#chat-typing-indicator').removeClass('d-none');
        $('#btn-chat-send').prop('disabled', true);

        // Auto scroll
        const $container = $('#chat-messages-container');
        $container.stop().animate({ scrollTop: $container[0].scrollHeight }, 200);

        $.ajax({
            url: HANDLER_URL,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'onPost_chat',
                prompt: prompt,
                quimico: STATE.quimico,
                celula: STATE.celula,
                historial: STATE.historialChat
            }),
            dataType: 'json'
        }).done(function (res) {
            const respuesta = res.respuesta || res.message || 'No pude obtener una respuesta en este momento.';
            appendBubble('bot', parseMarkdown(respuesta), true);
            STATE.historialChat.push({ role: 'assistant', content: respuesta });
        }).fail(function (err) {
            console.error('Error enviando mensaje al chatbot:', err);
            appendBubble('bot', 'Disculpa, hubo una interrupción momentánea al procesar tu consulta. Intenta nuevamente.', true);
        }).always(function () {
            $('#chat-typing-indicator').addClass('d-none');
            $('#btn-chat-send').prop('disabled', false);
            $input.focus();
        });
    }

})(jQuery);
