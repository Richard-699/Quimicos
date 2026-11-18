$(document).ready(function () {
    const id_consumo = $('#id_consumo_agua').val();

    function actualizarCalculoConsumo() {
        const initRaw = ($('#consumo_inicial_agua').val() || '').replace(/\./g, '').replace(',', '.').trim();
        const finalRaw = ($('#consumo_final_agua').val() || '').replace(/\./g, '').replace(',', '.').trim();

        const box = $('#box-consumo-calculado');
        const valorText = $('#calc-valor-text');

        if (!finalRaw || isNaN(parseFloat(finalRaw))) {
            box.fadeOut(150);
            return;
        }

        const ini = parseFloat(initRaw) || 0;
        const fin = parseFloat(finalRaw);
        const diff = fin - ini;

        box.fadeIn(150);

        if (diff >= 0) {
            box.removeClass('invalido');
            valorText.removeClass('invalido');
            valorText.html(`<i class="fa-solid fa-droplet me-1"></i> +${diff.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} m³`);
        } else {
            box.addClass('invalido');
            valorText.addClass('invalido');
            valorText.html(`<i class="fa-solid fa-triangle-exclamation me-1"></i> Inválido: Final &lt; Inicial`);
        }
    }

    // Inicializar cálculo inmediato con los valores cargados
    actualizarCalculoConsumo();

    // Filtro de input para números y hasta 4 decimales
    $('.decimal-input').on('input', function () {
        let val = $(this).val();
        val = val.replace(/[^0-9,]/g, '');
        const parts = val.split(',');
        if (parts.length > 2) {
            val = parts[0] + ',' + parts.slice(1).join('').replace(/,/g, '');
        }
        if (parts.length === 2 && parts[1].length > 4) {
            val = parts[0] + ',' + parts[1].substring(0, 4);
        }
        $(this).val(val);
        actualizarCalculoConsumo();
    });

    $(document).on('input keyup', '#observaciones_novedades_consumo_agua', function() {
        const len = $(this).val().length;
        $('#char-count-obs').text(len);
    });

    $('#formEditarConsumo').on('submit', async function (e) {
        e.preventDefault();

        const initVal = $('#consumo_inicial_agua').val().replace(',', '.');
        const finalVal = $('#consumo_final_agua').val().replace(',', '.');
        const fechaVal = ($('#fecha_consumo_agua').val() || '').trim();
        const obsVal = ($('#observaciones_novedades_consumo_agua').val() || '').trim();

        const consumoInicial = parseFloat(initVal) || 0;
        const consumoFinal = parseFloat(finalVal);

        if (!fechaVal) {
            if (typeof notification === 'function') {
                notification('error', 'La fecha de consumo es obligatoria.', 3000);
            } else {
                alert('La fecha de consumo es obligatoria.');
            }
            return;
        }

        if (isNaN(consumoFinal)) {
            if (typeof notification === 'function') {
                notification('error', 'El consumo final es inválido.', 3000);
            } else {
                alert('El consumo final es inválido.');
            }
            return;
        }

        if (consumoFinal < consumoInicial) {
            if (typeof notification === 'function') {
                notification('error', 'El consumo final no puede ser menor al inicial.', 3000);
            } else {
                alert('El consumo final no puede ser menor al inicial.');
            }
            return;
        }

        if (typeof mostrarCarga === 'function') mostrarCarga();

        try {
            const response = await fetch('../../Handler/quimicos/consumosAguaHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_consumo_agua',
                    form: {
                        id_consumo_agua: parseInt(id_consumo),
                        fecha_consumo_agua: fechaVal,
                        consumo_final_agua: consumoFinal,
                        observaciones_novedades_consumo_agua: obsVal.length > 0 ? obsVal : null
                    }
                })
            });

            const resultado = await response.json();

            if (resultado.success) {
                if (typeof notification === 'function') {
                    notification('success', resultado.message || 'Consumo actualizado correctamente.', 2500);
                }
                setTimeout(() => {
                    if (typeof Fancybox !== 'undefined') {
                        Fancybox.close();
                    } else if (parent && parent.Fancybox) {
                        parent.Fancybox.close();
                    }
                }, 1000);
            } else {
                if (typeof notification === 'function') {
                    notification('error', resultado.message || 'Error al actualizar el consumo.', 3500);
                } else {
                    alert(resultado.message || 'Error al actualizar el consumo.');
                }
            }
        } catch (error) {
            console.error('Error al actualizar consumo:', error);
            if (typeof notification === 'function') {
                notification('error', 'Error de conexión con el servidor.', 3000);
            } else {
                alert('Error de conexión con el servidor.');
            }
        } finally {
            if (typeof ocultarCarga === 'function') ocultarCarga();
        }
    });
});
