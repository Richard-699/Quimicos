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
                        return '-';
                    }

                    return `
                        <button class="btn btn-success btn-sm me-1" onclick="update(this, '${data}')">
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

async function update(btn, id) {
    const confirmado = await mostrarConfirmacion({
        titulo: '¿Deseas aprobar este administrador?',
        texto: 'Una vez aprobado, no se podrá revertir.',
        icono: 'warning',
        textoConfirmar: 'Sí, aprobar',
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
                action: 'approve',
                id: id
            })
        });

        const data = await response.json();
        ocultarCarga();

        if (data.success) {
            notification('success', 'Se aprobó el administrador.', 2000);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            btn.disabled = false;
            notification('error', 'Falló al aprobar el administrador, intenta nuevamente.', 2000);
        }
    } catch (error) {
        ocultarCarga();
        console.error('Error al rechazar:', error);
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
