$(document).ready(function () {
    $('#tabla-quimicos').DataTable({
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
            "url": '../../Handler/quimicos/quimicosHandler.php?action=onGet_quimicos',
            "dataSrc": ""
        },
        "columns": [
            { "data": "descripcion_quimico", "className": "dt-center" },
            { "data": "peligrosidad_quimico", "className": "dt-center" },
            { "data": "umb_quimico", "className": "dt-center" },
            { "data": "precio_quimico", "className": "dt-center" },
            { "data": "cantidad_disponible_quimico", "className": "dt-center" },
            {
                "data": "id_quimico",
                "className": "dt-center",
                "render": function (data, type, row) {
                    let url = row.url_etiqueta_emergencia_quimico ?? '';
                    return `
                        <button class="btn btn-primary btn-sm me-1" onclick="update(this, '${data}')">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                         <button class="btn btn-warning btn-sm me-1" 
                            onclick="verEtiqueta('${url}')">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="delete_quimico(this, '${data}')">
                            <i class="fa-solid fa-trash-can"></i>
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

    document.getElementById('btnAgregarQuimico').addEventListener('click', async function () {
        mostrarCarga();

        let btn = this;
        btn.disabled = true;

        try {

            const responseUMBS = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_umbs', {
                method: 'GET'
            });
            const umbs = await responseUMBS.json();
            const umbsEncoded = encodeURIComponent(JSON.stringify(umbs));

            const responsePeligrosidades = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_peligrosidades', {
                method: 'GET'
            });
            const peligrosidades = await responsePeligrosidades.json();
            const peligrosidadesEncoded = encodeURIComponent(JSON.stringify(peligrosidades));

            const responseCelulasAreas = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_celulasAreas', {
                method: 'GET'
            });
            const celulasAreas = await responseCelulasAreas.json();
            const celulasAreasEncoded = encodeURIComponent(JSON.stringify(celulasAreas));

            var url = `_agregar_quimicos.php?celulasAreas=${celulasAreasEncoded}&umbs=${umbsEncoded}&peligrosidades=${peligrosidadesEncoded}`;

            Fancybox.show([{
                src: url,
                type: 'ajax'
            }]);

            setTimeout(() => {
                ocultarCarga();

                const celulas_areas_quimicos = document.getElementById('celulas_areas_quimicos');
                if (celulas_areas_quimicos && !celulas_areas_quimicos.classList.contains('choices-initialized')) {
                    const choicesInstance = new Choices(celulas_areas_quimicos, {
                        removeItemButton: true,
                        searchEnabled: true,
                        placeholder: true,
                        placeholderValue: 'Selecciona una o más células',
                        searchPlaceholderValue: 'Buscar células...',
                        shouldSort: false
                    });

                    document.addEventListener('click', function (e) {
                        const opcion = e.target.closest('.choices__item--selectable');
                        const contenedor = e.target.closest('.choices__list--dropdown');

                        if (opcion && contenedor) {
                            e.preventDefault();
                            e.stopPropagation();

                            const value = opcion.getAttribute('data-value');
                            if (!value) return;

                            const selectedValues = choicesInstance.getValue(true);
                            const isSelected = selectedValues.includes(value);

                            if (isSelected) {
                                choicesInstance.removeActiveItemsByValue(value);
                            } else {
                                choicesInstance.setChoiceByValue(value);
                            }
                        }
                    });

                    celulas_areas_quimicos.classList.add('choices-initialized');
                }

                Fancybox.getInstance().options = {
                    ...Fancybox.getInstance().options,
                    click: false,
                    trapFocus: false,
                    placeFocusBack: false
                };
            }, 100);

        } catch (error) {
            console.error('Error al cargar la modal:', error);
        } finally {
            ocultarCarga();
            btn.disabled = false;
        }
    });
});

function verEtiqueta(url) {
    if (!url || url.trim() === "") {
        notification('alert', 'Este químico no tiene etiqueta de emergencia registrada.', 2000);
        return;
    }

    window.open(url, '_blank');
}

async function update(btn, id) {
    mostrarCarga();
    btn.disabled = true;

    try {
        const responseUMBS = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_umbs', {
            method: 'GET'
        });
        const umbs = await responseUMBS.json();
        const umbsEncoded = encodeURIComponent(JSON.stringify(umbs));

        const responseCelulasAreas = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_celulasAreas', {
            method: 'GET'
        });
        const celulasAreas = await responseCelulasAreas.json();
        const celulasAreasEncoded = encodeURIComponent(JSON.stringify(celulasAreas));

        const responseCelulasAreasSelected = await fetch(`../../Handler/quimicos/quimicosHandler.php?action=onGet_celulasAreasSelected&id=${id}`, {
            method: 'GET'
        });
        const celulasAreasSelected = await responseCelulasAreasSelected.json();
        const celulasAreasSelectedEncoded = encodeURIComponent(JSON.stringify(celulasAreasSelected));

        const responsePeligrosidades = await fetch('../../Handler/quimicos/quimicosHandler.php?action=onGet_peligrosidades', {
                method: 'GET'
            });
        const peligrosidades = await responsePeligrosidades.json();
        const peligrosidadesEncoded = encodeURIComponent(JSON.stringify(peligrosidades));

        const responseQuimicoSelected = await fetch(`../../Handler/quimicos/quimicosHandler.php?action=onGet_quimicoSelected&id=${id}`, {
            method: 'GET'
        });
        const quimicoSelected = await responseQuimicoSelected.json();
        const quimicoSelectedEncoded = encodeURIComponent(JSON.stringify(quimicoSelected));

        var url = `_edit_quimicos.php?quimicoSelected=${quimicoSelectedEncoded}&umbs=${umbsEncoded}&celulasAreas=${celulasAreasEncoded}&celulasAreasSelected=${celulasAreasSelectedEncoded}&peligrosidades=${peligrosidadesEncoded}&id=${id}`;

        Fancybox.show([{
            src: url,
            type: 'ajax'
        }]);

        setTimeout(() => {
            ocultarCarga();

            const celulas_areas_quimicos = document.getElementById('celulas_areas_quimicos');
            if (celulas_areas_quimicos && !celulas_areas_quimicos.classList.contains('choices-initialized')) {
                const choicesInstance = new Choices(celulas_areas_quimicos, {
                    removeItemButton: true,
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Selecciona una o más células',
                    searchPlaceholderValue: 'Buscar células...',
                    shouldSort: false
                });

                document.addEventListener('click', function (e) {
                    const opcion = e.target.closest('.choices__item--selectable');
                    const contenedor = e.target.closest('.choices__list--dropdown');

                    if (opcion && contenedor) {
                        e.preventDefault();
                        e.stopPropagation();

                        const value = opcion.getAttribute('data-value');
                        if (!value) return;

                        const selectedValues = choicesInstance.getValue(true);
                        const isSelected = selectedValues.includes(value);

                        if (isSelected) {
                            choicesInstance.removeActiveItemsByValue(value);
                        } else {
                            choicesInstance.setChoiceByValue(value);
                        }
                    }
                });

                celulas_areas_quimicos.classList.add('choices-initialized');
            }

            Fancybox.getInstance().options = {
                ...Fancybox.getInstance().options,
                click: false,
                trapFocus: false,
                placeFocusBack: false
            };
        }, 100);

    } catch (error) {
        console.error('Error al cargar la modal:', error);
    } finally {
        btn.disabled = false;
    }
}

async function delete_quimico(btn, id) {
    const confirmado = await mostrarConfirmacion({
        titulo: '¿Deseas eliminar este químico?',
        texto: 'Una vez eliminado, no se podrá revertir.',
        icono: 'warning',
        textoConfirmar: 'Sí, eliminar',
        textoCancelar: 'Cancelar'
    });

    if (!confirmado) return;

    mostrarCarga();
    btn.disabled = true;
    try {
        const response = await fetch('../../Handler/quimicos/quimicosHandler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_quimico',
                id: id
            })
        });

        const data = await response.json();
        ocultarCarga();

        if (data.success) {
            notification('success', 'Se eliminó el químico.', 2000);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            btn.disabled = false;
            notification('error', 'Falló al eliminar el químico, intenta nuevamente.', 2000);
        }
    } catch (error) {
        ocultarCarga();
        console.error('Error al rechazar:', error);
        btn.disabled = false;
    }
}