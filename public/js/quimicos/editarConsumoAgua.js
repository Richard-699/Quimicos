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

    $('#formEditarConsumo').on('submit', async function (e) {
        e.preventDefault();

        const initVal = $('#consumo_inicial_agua').val().replace(',', '.');
        const finalVal = $('#consumo_final_agua').val().replace(',', '.');

        const consumoInicial = parseFloat(initVal) || 0;
        const consumoFinal = parseFloat(finalVal);

        if (isNaN(consumoFinal)) {
            if (typeof notification === 'function') {
                notification('error', 'El consumo final es inválido.', 3000);
            } else {
                alert('El consumo final es inválido.');
            }
            return;
        }

        if (consumoFinal <= consumoInicial) {
            if (typeof notification === 'function') {
                notification('error', 'El consumo final debe ser mayor que el inicial.', 3000);
            } else {
                alert('El consumo final debe ser mayor que el inicial.');
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
                        consumo_final_agua: consumoFinal
                    }
                })
            });

            const resultado = await response.json();
            if (typeof ocultarCarga === 'function') ocultarCarga();

            if (resultado.success) {
                if (typeof notification === 'function') {
                    notification('success', 'Consumo actualizado correctamente.', 2000);
                }
                setTimeout(function () {
                    if (typeof Fancybox !== 'undefined' && Fancybox.close) {
                        Fancybox.close();
                    }
                }, 800);
            } else {
                if (typeof notification === 'function') {
                    notification('error', resultado.message || 'Error al actualizar consumo.', 3000);
                } else {
                    alert(resultado.message || 'Error al actualizar consumo.');
                }
            }
        } catch (error) {
            if (typeof ocultarCarga === 'function') ocultarCarga();
            console.error(error);
            if (typeof notification === 'function') {
                notification('error', 'Ocurrió un error inesperado al actualizar.', 3000);
            } else {
                alert('Ocurrió un error inesperado al actualizar.');
            }
        }
    });
});
