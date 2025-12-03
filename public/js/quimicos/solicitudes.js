$(document).ready(function () {
    $('#tabla-solicitudes').DataTable({
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
            "url": '../../Handler/quimicos/solicitudesHandler.php?action=onGet_solicitudes',
            "dataSrc": ""
        },
        "columns": [
            { "data": "fecha_solicitud_consumo", "className": "dt-center" },
            { "data": "celula_area", "className": "dt-center" },
            { "data": "descripcion_quimico", "className": "dt-center" },
            { "data": "umb_quimico", "className": "dt-center" },
            { "data": "cantidad_solicitud_consumo", "className": "dt-center" },
            {
                data: null,
                className: "dt-center",
                render: function (data, type, row) {
                    return `${row.nombres_solicitante_consumo} ${row.apellidos_solicitante_consumo}`;
                }
            },
            {
                "data": "id_solicitud_consumo",
                "className": "dt-center",
                "render": function (data, type, row) {
                    const cantidad = row.cantidad_solicitud_consumo ?? 0;
                    const id_quimico = row.id_quimico_solicitud_consumo ?? 0;

                    return `
                        <button class="btn btn-success btn-sm me-1" 
                            onclick="update_status(this, 'approve', '${data}', '${cantidad}', '${id_quimico}')">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="update_status(this, 'rechazar', '${data}')">
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

async function update_status(btn, action, id, cantidad = null, id_quimico = null) {
    let id_estado = null;
    let confirmado = null;

    if(action == "approve"){
        id_estado = 1;
        confirmado = await mostrarConfirmacion({
            titulo: '¿Deseas aprobar esta solicitud?',
            texto: 'Una vez aprobada, no se podrá revertir.',
            icono: 'warning',
            textoConfirmar: 'Sí, aprobar',
            textoCancelar: 'Cancelar'
        });
    }else{
        id_estado = 2;
        confirmado = await mostrarConfirmacion({
            titulo: '¿Deseas rechazar esta solicitud?',
            texto: 'Una vez rechazada, no se podrá revertir.',
            icono: 'warning',
            textoConfirmar: 'Sí, rechazar',
            textoCancelar: 'Cancelar'
        });
    }

    if (!confirmado) return;

    mostrarCarga();
    btn.disabled = true;
    try {
        const response = await fetch('../../Handler/quimicos/solicitudesHandler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_estado_solicitud',
                id: id,
                id_estado: id_estado,
                cantidad_solicitud: cantidad,
                id_quimico: id_quimico
            })
        });

        const data = await response.json();
        ocultarCarga();

        if (data.success) {
            if(action == "approve"){
                notification('success', 'Se aprobó la solicitud.', 2000);
            }else{
                notification('success', 'Se rechazó la solicitud.', 2000);
            }
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            btn.disabled = false;
            if(action == "approve"){
                notification('error', 'Falló al aprobar la solicitud, intenta nuevamente.', 2000);
            }else{
                notification('error', 'Falló al rechazar la solicitud, intenta nuevamente.', 2000);
            }
        }
    } catch (error) {
        ocultarCarga();
        console.error('Error al actualizar el estado:', error);
        btn.disabled = false;
    }
}