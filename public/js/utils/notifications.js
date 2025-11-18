function notification(tipo, mensaje, duracion = 3000) {
    const notyf = new Notyf({
        duration: duracion,
        position: { x: 'right', y: 'top' }
    });

    if (tipo === 'success') {
        notyf.success(mensaje);
    } else if (tipo === 'alert') {
        notyf.open({
            type: 'info',
            message: mensaje,
            background: '#17a2b8',
            icon: {
                className: 'custom-alert-icon',
                tagName: 'span',
                text: '⚠️'
            }
        });
    } else if (tipo === 'error') {
        notyf.error(mensaje);
    }
}

async function mostrarConfirmacion({
    titulo = '¿Estás seguro?',
    texto = 'Esta acción no se puede deshacer',
    icono = 'warning',
    textoConfirmar = 'Sí',
    textoCancelar = 'No',
    colorConfirmar = '#072B31',
    html = null, // permite insertar HTML (como el select)
    preConfirm = null, // función opcional para validación
    mostrarCancel = true
} = {}) {
    const resultado = await Swal.fire({
        title: titulo,
        text: html ? null : texto, // solo muestra texto si no hay html
        html: html,
        icon: icono,
        showCancelButton: mostrarCancel,
        confirmButtonText: textoConfirmar,
        cancelButtonText: textoCancelar,
        confirmButtonColor: colorConfirmar,
        preConfirm: preConfirm,
        scrollbarPadding: false 
    });

    return resultado.isConfirmed ? resultado : false;
}

