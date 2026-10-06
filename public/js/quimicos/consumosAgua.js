$(document).ready(async function () {
    let tabla;
    const id_celula = document.getElementById('id_celula_hidden') ? document.getElementById('id_celula_hidden').value : null;

    // 1. CARGAR TÍTULO
    async function initTitle() {
        if (!id_celula) return;
        try {
            const res = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_celulasAreas');
            const data = await res.json();
            if (data && Array.isArray(data)) {
                const cel = data.find(c => c.id_celulas_areas == id_celula);
                if (cel) $('#titulo_consumo').text(`Consumos de Agua — ${cel.nombre_celula}`);
            }
        } catch (e) {
            console.error(e);
        }
    }

    // 2. CARGAR TABLA
    function initTable() {
        tabla = $('#tabla-consumos-agua').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            scrollCollapse: true,
            paging: true,
            pageLength: 10,
            ajax: {
                url: `../../Handler/quimicos/consumosAguaHandler.php?action=onGet_consumos_agua&id_celula=${id_celula}`,
                dataSrc: function (data) {
                    return Array.isArray(data) ? data : [];
                }
            },
            columns: [
                {
                    data: 'fecha_consumo_agua',
                    className: 'dt-center',
                    render: function (data) {
                        return data || '—';
                    }
                },
                {
                    data: 'nombre_tanque',
                    className: 'dt-center',
                    render: function (data) {
                        return (data && data !== 'No Aplica') ? data : 'No Aplica';
                    }
                },
                {
                    data: 'consumo_inicial_agua',
                    className: 'dt-center',
                    render: function (data) {
                        const n = parseFloat(data);
                        return isNaN(n) ? '—' : n.toLocaleString('es-CO', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                    }
                },
                {
                    data: 'consumo_final_agua',
                    className: 'dt-center',
                    render: function (data) {
                        const n = parseFloat(data);
                        return isNaN(n) ? '—' : n.toLocaleString('es-CO', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                    }
                }
            ]
        });
    }

    // 3. BOTÓN REGISTRAR CONSUMO (FANCYBOX)
    $('#btnRegistrarConsumo').on('click', function () {
        const url = `_registrarConsumoAgua.php?id_celula=${id_celula}&v=${Date.now()}`;
        Fancybox.show([{ src: url, type: 'ajax' }], {
            on: {
                destroy: () => {
                    if (tabla) tabla.ajax.reload(null, false);
                }
            },
            click: false,
            trapFocus: false,
            placeFocusBack: false
        });
    });

    // ARRANQUE
    await initTitle();
    initTable();
});
