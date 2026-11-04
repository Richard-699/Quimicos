document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll("a.nav-link").forEach(link => {
        link.addEventListener("click", function () {
            if (this.hostname === window.location.hostname) {
                mostrarCarga();
            }
        });
    });

    // Acordeón para grupos del menú lateral
    document.querySelectorAll('.nav-group-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const group = this.closest('.nav-group');
            if (group) {
                group.classList.toggle('open');
            }
        });
    });

    document.querySelectorAll('#BtnCerrarSesion, #BtnCerrarSesionMenu').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const ejecutarCierre = () => {
                mostrarCarga();
                setTimeout(() => {
                    window.location.href = '../auth/log_out.php';
                }, 500);
            };

            const swalInstance = (typeof Swal !== 'undefined') ? Swal : (typeof Sweetalert2 !== 'undefined' ? Sweetalert2 : null);

            if (swalInstance) {
                swalInstance.fire({
                    title: '¿Desea cerrar la sesión?',
                    text: 'Se cerrará su sesión actual en el sistema.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#072B31',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    heightAuto: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarCierre();
                    }
                });
            } else {
                if (confirm('¿Desea cerrar la sesión?')) {
                    ejecutarCierre();
                }
            }
        });
    });

    // Eliminar atributos title de botones en todo el proyecto
    const limpiarTitles = () => {
        document.querySelectorAll('button[title], a.btn[title], [role="button"][title], .carousel__button[title], [data-fancybox-close][title]').forEach(el => {
            el.removeAttribute('title');
        });
    };
    limpiarTitles();

    document.addEventListener('mouseover', function (e) {
        const target = e.target.closest('button, .btn, [role="button"], .carousel__button, [data-fancybox-close]');
        if (target && target.hasAttribute('title')) {
            target.removeAttribute('title');
        }
    }, true);

    // Asegurar que la ventana principal nunca sufra desplazamiento vertical al abrir/cerrar modales
    window.addEventListener('scroll', function () {
        if (window.scrollY !== 0 || window.scrollX !== 0) {
            window.scrollTo(0, 0);
        }
    }, { passive: false });

});
