$(document).ready(function () {
    $('#tabla-administradores').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        },
        lengthMenu: [
            [10, 50, 100, 200, -1],
            [10, 50, 100, 200, "Todos"]
        ],
        "scrollCollapse": true,
        "paging": true,
        pageLength: 10,

        "ajax": {
            "url": '../../Handler/quimicos/administradoresHandler.php?action=onGet_administradores',
            "dataSrc": ""
        },
        "columns": [
            { "data": "cedula_administrador", "className": "dt-center" },
            {
                data: null,
                className: "dt-center",
                render: function (data, type, row) {
                    return `${row.nombre_administrador} ${row.apellidos_administrador}`;
                }
            },
            { "data": "correo_hwi_administrador", "className": "dt-center" },
            {
                "data": "id_administrador",
                "className": "dt-center",
                "render": function (data, type, row) {
                    if (row.estado_administrador == 1) {
                        return `
                            <button class="btn btn-primary btn-sm" onclick="update(this, '${data}', 'update', '${row.id_celula_consumo_agua || ''}')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        `;
                    }

                    return `
                        <button class="btn btn-success btn-sm me-1" onclick="update(this, '${data}', 'approve')">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="rechazar(this, '${data}')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;
                }
            }
        ],
        "responsive": true,
        "ordering": true,
        "info": true,
        "searching": true
    });
});

async function update(btn, id, action, id_celula_consumo_agua = '') {
    mostrarCarga();
    btn.disabled = true;

    try {
        const responsePermisos = await fetch('../../Handler/quimicos/administradoresHandler.php?action=obtenerPermisos', {
            method: 'GET'
        });
        const permisos = await responsePermisos.json();
        const permisosEncoded = encodeURIComponent(JSON.stringify(permisos));

        const responseCelulas = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_celulasAreas', {
            method: 'GET'
        });
        const celulas = await responseCelulas.json();
        const celulasEncoded = encodeURIComponent(JSON.stringify(celulas));

        var url = `_administradorPermisos.php?permisos=${permisosEncoded}&action=${action}&id_administrador=${id}&celulas=${celulasEncoded}&id_celula_consumo_agua=${id_celula_consumo_agua}`;

        if (action == 'update') {
            debugger;
            const responsePermisosSelected = await fetch(`../../Handler/quimicos/administradoresHandler.php?action=obtenerPermisosAdministrador&id=${id}`, {
                method: 'GET'
            });
            const permisosSelected = await responsePermisosSelected.json();
            const permisosSelectedEncoded = encodeURIComponent(JSON.stringify(permisosSelected));

            url = `_administradorPermisos.php?permisos=${permisosEncoded}&action=${action}&id_administrador=${id}&celulas=${celulasEncoded}&permisosSelected=${permisosSelectedEncoded}&id_celula_consumo_agua=${id_celula_consumo_agua}`;
        }

        Fancybox.show([{
            src: url,
            type: 'ajax'
        }], {
            on: {
                reveal: (fancybox, slide) => {
                    ocultarCarga();

                    const content = slide.$content;
                    const selects = content.querySelectorAll('select[multiple]');

                    selects.forEach(select => {

                        if (!select.classList.contains('choices-initialized')) {

                            new Choices(select, {
                                removeItemButton: true,
                                searchEnabled: true,
                                placeholder: true,
                                placeholderValue: 'Selecciona una o más opciones',
                                searchPlaceholderValue: 'Buscar...',
                                shouldSort: false,
                                itemSelectText: ''
                            });

                            select.classList.add('choices-initialized');
                        }
                    });
                },
                destroy: (fancybox) => {
                    const selects = document.querySelectorAll('.choices-initialized');

                    selects.forEach(select => {
                        if (select.choicesInstance) {
                            select.choicesInstance.destroy();
                            delete select.choicesInstance;
                        }
                    });

                    if (typeof tabla !== 'undefined' && tabla) {
                        tabla.ajax.reload(null, false);
                    }

                    document.body.classList.remove('fancybox-active');
                    document.body.style.cursor = "default";
                }
            },

            click: false,
            trapFocus: false,
            placeFocusBack: false
        });
    } catch (error) {
        console.error('Error al cargar la modal:', error);
    } finally {
        btn.disabled = false;
    }
}

async function rechazar(btn, id) {
    const confirmado = await mostrarConfirmacion({
        titulo: '¿Deseas rechazar este administrador?',
        texto: 'Una vez rechazado, no se podrá revertir.',
        icono: 'warning',
        textoConfirmar: 'Sí, rechazar',
        textoCancelar: 'Cancelar'
    });

    if (!confirmado) return;

    mostrarCarga();
    btn.disabled = true;
    try {
        const response = await fetch('../../Handler/quimicos/administradoresHandler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_administrador',
                id: id
            })
        });

        const data = await response.json();
        ocultarCarga();

        if (data.success) {
            notification('success', 'Se rechazó el administrador.', 2000);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            btn.disabled = false;
            notification('error', 'Falló al rechazar el administrador, intenta nuevamente.', 2000);
        }
    } catch (error) {
        ocultarCarga();
        console.error('Error al rechazar:', error);
        btn.disabled = false;
    }
}
