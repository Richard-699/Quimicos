document.querySelectorAll('#BtnCerrarSesion, #BtnCerrarSesionMenu').forEach(btn => {
    btn.addEventListener('click', function (e) {
        debugger;
        e.preventDefault();
        mostrarCarga();

        setTimeout(() => {
            window.location.href = '../auth/log_out.php';
        }, 1200);
    });
});

