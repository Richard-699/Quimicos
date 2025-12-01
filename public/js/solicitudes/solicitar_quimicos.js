let quimicoSeleccionado = null;
$(document).ready(function () {
    document.getElementById("cedula_solicitante").addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
    });

    $.ajax({
        url: "../../Handler/solicitudes/solicitarQuimicosHandler.php?action=onGet_celulasAreas",
        method: "GET",
        success: function (response) {
            if (Array.isArray(response)) {

                $("#id_celula_area_solicitud_consumo").empty();
                $("#id_celula_area_solicitud_consumo").append(`<option value="" disabled selected>Seleccione una célula</option>`);

                response.forEach(function (item) {
                    $("#id_celula_area_solicitud_consumo").append(
                        `<option value="${item.id_celulas_areas}">${item.nombre_celula}</option>`
                    );
                });
            } else {
                notification('error', 'La respuesta del servidor no es válida.', 2000);
            }
        },
        error: function () {
            notification('error', 'Error en el servidor.', 2000);
        }
    });

    $("#id_celula_area_solicitud_consumo").on("change", function () {
        let idCelulaArea = $(this).val();

        $.ajax({
            url: `../../Handler/solicitudes/solicitarQuimicosHandler.php?action=onGet_quimicos&id=${idCelulaArea}`,
            method: "GET",
            success: function (response) {
                $("#id_quimico_solicitud_consumo").empty();
                $("#id_quimico_solicitud_consumo").append(`<option value="">Seleccione un químico</option>`);

                if (response.success === false) {
                    notification('error', response.message, 2000);
                    return;
                }
                if (Array.isArray(response)) {
                    window.listaQuimicos = {};
                    response.forEach(function (item) {
                        window.listaQuimicos[item.id_quimico] = item;
                        $("#id_quimico_solicitud_consumo").append(
                            `<option value="${item.id_quimico}">${item.descripcion_quimico}</option>`
                        );
                    });
                } else {
                    notification('error', 'La respuesta del servidor no es válida.', 2000);
                }
            },
            error: function () {
                notification('error', 'Error en el servidor.', 2000);
            }
        });
    });

    $("#id_quimico_solicitud_consumo").on("change", function () {
        let idQuimico = $(this).val();

        quimicoSeleccionado = window.listaQuimicos[idQuimico];
    });

    document.getElementById("cantidad_solicitud_consumo").addEventListener("input", function () {
        let valor = this.value;

        valor = valor.replace(/[^0-9.]/g, "");

        const partes = valor.split(".");
        if (partes.length > 2) {
            valor = partes[0] + "." + partes[1];
        }

        if (partes[1]?.length > 2) {
            valor = partes[0] + "." + partes[1].substring(0, 2);
        }

        this.value = valor;
    });

    $("#cantidad_solicitud_consumo").on("input", function () {
        if (!quimicoSeleccionado) return;

        let cantidad = parseFloat($(this).val());
        if (isNaN(cantidad)) return;

        let maxRetiro = parseFloat(quimicoSeleccionado.cantidad_maxima_retiro_quimico);
        let disponible = parseFloat(quimicoSeleccionado.cantidad_disponible_quimico);
        let umb = quimicoSeleccionado.umb_quimico;

        if (cantidad > maxRetiro) {
            notification('error', `La cantidad máxima permitida es de ${maxRetiro} ${umb}.`, 2000);
            $(this).val(maxRetiro);
            return;
        }

        if (cantidad > disponible) {
            notification('error', `Solo hay ${disponible} ${umb} disponibles.`, 2000);
            $(this).val(disponible);
            return;
        }
    });

    document.getElementById('formSolicitudQuimico').addEventListener('submit', async function (e) {
        e.preventDefault();
        mostrarCarga();

        const form = document.getElementById('formSolicitudQuimico');
        const formData = new FormData(form);

        const formObj = {};
        formData.forEach((value, key) => {
            if (formObj[key] === undefined) {
                formObj[key] = value;
            } else if (Array.isArray(formObj[key])) {
                formObj[key].push(value);
            } else {
                formObj[key] = [formObj[key], value];
            }
        });

        const action = 'save_solicitud';

        try {
            const response = await fetch('../../Handler/solicitudes/solicitarQuimicosHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: action,
                    form: formObj
                })
            });

            const resultado = await response.json();

            ocultarCarga();

            if (resultado.success) {
                notification('success', 'El químico se solicitó correctamente, espera la respuesta.', 2000);

                setTimeout(function () {

                    if (Fancybox.getInstance()) {
                        Fancybox.getInstance().close();
                    }

                    location.reload();
                }, 2000);
            } else {
                notification('error', resultado.message, 4000);
            }
        } catch (error) {
            ocultarCarga();
            notification('error', error.message, 3000);
        }
    });
})