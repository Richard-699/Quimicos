$(document).ready(async function () {
    const id_celula = document.getElementById('id_celula_consumo_agua').value;
    
    // Si la celula es 3 (Recubrimiento), cargamos los tanques y escuchamos cambios
    if (id_celula == 3) {
        await cargarTanques();
        
        $('#id_tanque_abastecimiento_consumo_agua').on('change', async function() {
            await cargarConsumoInicial(id_celula, this.value);
        });
    } else {
        // Carga directa de consumo inicial (sin tanque)
        await cargarConsumoInicial(id_celula, null);
    }
    
    async function cargarTanques() {
        try {
            const res = await fetch('../../Handler/quimicos/consumosAguaHandler.php?action=onGet_tanques');
            const data = await res.json();
            
            const select = $('#id_tanque_abastecimiento_consumo_agua');
            select.empty();
            select.append('<option value="" selected disabled>Seleccione un tanque</option>');
            
            if(data && Array.isArray(data)) {
                data.forEach(t => {
                    select.append(`<option value="${t.id_tanque_abastecimiento_agua}">${t.tanque_abastecimiento_agua}</option>`);
                });
            }
        } catch(e) {
            console.error(e);
        }
    }
    
    async function cargarConsumoInicial(id_celula, id_tanque) {
        try {
            const res = await fetch(`../../Handler/quimicos/consumosAguaHandler.php?action=onGet_consumos_agua&id_celula=${id_celula}`);
            const data = await res.json();
            
            let consumo_inicial = 0;
            if(data && Array.isArray(data)) {
                // Buscamos el último consumo del tanque seleccionado (o sin tanque si es null)
                const lastRecord = data.find(c => {
                    if (id_tanque) {
                        return c.id_tanque_abastecimiento_consumo_agua == id_tanque;
                    }
                    return !c.id_tanque_abastecimiento_consumo_agua;
                });
                
                if (lastRecord) {
                    consumo_inicial = lastRecord.consumo_final_agua;
                }
            }
            
            let formatted_consumo_inicial = consumo_inicial.toString().replace('.', ',');
            document.getElementById('consumo_inicial_agua').value = formatted_consumo_inicial;
            actualizarCalculoConsumo();
            
        } catch (e) {
            console.error(e);
        }
    }

    function actualizarCalculoConsumo() {
        const initRaw = ($('#consumo_inicial_agua').val() || '').replace(/\./g, '').replace(',', '.').trim();
        const finalRaw = ($('#consumo_final_agua').val() || '').replace(/\./g, '').replace(',', '.').trim();
        
        const box = $('#box-consumo-calculado');
        const valorText = $('#calc-valor-text');

        if (!finalRaw || isNaN(parseFloat(finalRaw))) {
            box.hide();
            return;
        }

        const ini = parseFloat(initRaw) || 0;
        const fin = parseFloat(finalRaw);
        const diff = fin - ini;

        box.show();

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

    // Inicializar cálculo inicial
    actualizarCalculoConsumo();

    // Permitir solo números y coma, con hasta 4 decimales
    $(document).on('input keyup change', '#consumo_final_agua, .decimal-input', function() {
        let val = $(this).val();
        
        // Remover cualquier caracter que no sea número o coma
        val = val.replace(/[^0-9,]/g, '');
        
        // Evitar múltiples comas
        const parts = val.split(',');
        if (parts.length > 2) {
            val = parts[0] + ',' + parts.slice(1).join('').replace(/,/g, '');
        }
        
        // Limitar a 4 decimales
        if (parts.length === 2 && parts[1].length > 4) {
            val = parts[0] + ',' + parts[1].substring(0, 4);
        }
        
        $(this).val(val);
        actualizarCalculoConsumo();
    });

    document.getElementById('formRegistrarConsumo').addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const initVal = document.getElementById('consumo_inicial_agua').value.replace(',', '.');
        const finalVal = document.getElementById('consumo_final_agua').value.replace(',', '.');
        
        const fechaVal = document.getElementById('fecha_consumo_agua') ? document.getElementById('fecha_consumo_agua').value : '';
        if (!fechaVal) {
            notification('error', 'Por favor seleccione la fecha de consumo.', 3000);
            return;
        }

        const consumoInicial = parseFloat(initVal);
        const consumoFinal = parseFloat(finalVal);
        
        if (isNaN(consumoFinal)) {
            notification('error', 'El consumo final es inválido.', 3000);
            return;
        }

        if (consumoFinal <= consumoInicial) {
            notification('error', 'El consumo final debe ser mayor que el inicial.', 3000);
            return;
        }
        
        mostrarCarga();
        const formObj = {
            id_celula_consumo_agua: document.getElementById('id_celula_consumo_agua').value,
            fecha_consumo_agua: fechaVal,
            consumo_inicial_agua: consumoInicial,
            consumo_final_agua: consumoFinal
        };
        
        if (id_celula == 3) {
            formObj.id_tanque_abastecimiento_consumo_agua = document.getElementById('id_tanque_abastecimiento_consumo_agua').value;
        }
        
        try {
            const response = await fetch('../../Handler/quimicos/consumosAguaHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_consumo_agua',
                    form: formObj
                })
            });

            const resultado = await response.json();
            ocultarCarga();

            if (resultado.success) {
                notification('success', 'Consumo guardado correctamente.', 2000);
                setTimeout(function () {
                    Fancybox.close();
                }, 1000);
            } else {
                notification('error', resultado.message || 'Error al guardar consumo.', 3000);
            }
        } catch (error) {
            ocultarCarga();
            console.error(error);
            notification('error', 'Ocurrió un error inesperado.', 3000);
        }
    });
});
