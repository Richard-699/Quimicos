$(document).ready(function () {
    
    function verificarPermisoConsumoAgua() {
        const selectPermisos = document.getElementById('permisos_administradores');
        const options = selectPermisos.options;
        let requiresCelula = false;
        
        for (let i = 0; i < options.length; i++) {
            if (options[i].selected && options[i].text.includes('Registrar Consumos Agua')) {
                requiresCelula = true;
                break;
            }
        }
        
        const contenedor = document.getElementById('contenedor_celula_agua');
        const selectCelula = document.getElementById('id_celula_consumo_agua');
        
        if (requiresCelula) {
            contenedor.style.display = 'block';
            selectCelula.setAttribute('required', 'required');
        } else {
            contenedor.style.display = 'none';
            selectCelula.removeAttribute('required');
            selectCelula.value = '';
        }
    }

    // Usar event listener de jQuery porque Choices.js dispara el evento 'change' original
    $('#permisos_administradores').on('change', verificarPermisoConsumoAgua);
    
    // Verificar al cargar
    setTimeout(verificarPermisoConsumoAgua, 300);

    document.getElementById('formUpdateAdministrador').addEventListener('submit', async function (e) {
        e.preventDefault();
        mostrarCarga();

        const permisos = $('#permisos_administradores').val();

        if (!permisos || permisos.length === 0) {
            notification('error', 'Debe seleccionar al menos un permiso', 2000);
            setTimeout(() => {
                ocultarCarga();
            }, 2500);
            return;
        }

        const form = document.getElementById('formUpdateAdministrador');
        const formData = new FormData(form);

        const formObj = {};
        formData.forEach((value, key) => {
            const cleanKey = key.includes('[') ? key.replace('[]', '') : key;
            if (formObj[cleanKey] === undefined) {
                formObj[cleanKey] = value;
            } else if (Array.isArray(formObj[cleanKey])) {
                formObj[cleanKey].push(value);
            } else {
                formObj[cleanKey] = [formObj[cleanKey], value];
            }
        });

        formObj['permisos_administradores'] = $('#permisos_administradores').val() || [];
        formObj['id_administrador'] = $('#id_administrador').val();

        const action = document.getElementById('action').value;
        const actionHandler = "updateAdministrador";

        try {
            const response = await fetch('../../Handler/quimicos/administradoresHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: actionHandler,
                    form: formObj
                })
            });

            const resultado = await response.json();

            ocultarCarga();

            if (resultado.success) {
                if (action == 'approve') {
                    notification('success', 'Se aprobó el administrador.', 2000);
                } else {
                    notification('success', 'Se actualizó el administrador.', 2000);
                }

                setTimeout(function () {
                    window.actualizoAdministrador = true;

                    if (Fancybox.getInstance()) {
                        Fancybox.getInstance().close();
                    }

                    location.reload();
                }, 2000);
            } else {
                notification('error', resultado.mensaje || 'Error en el servidor.', 4000);
            }
        } catch (error) {
            ocultarCarga();
            notification('error', 'Error de red o del servidor.', 3000);
        }
    });
});
