function Inicio() {
    mostrarCarga();
    window.location.href = "index.php";
}

function Quimicos() {
    mostrarCarga();
    window.location.href = "quimicos.php";
}

function Solicitudes() {
    mostrarCarga();
    window.location.href = "solicitudes.php";
}

function Inventario() {
    mostrarCarga();
    window.location.href = "inventario.php";
}

function Administradores() {
    mostrarCarga();
    window.location.href = "administradores.php";
}

function Informe() {
    mostrarCarga();
    window.location.href = "informe.php";
}

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

