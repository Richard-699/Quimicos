function Inicio() {
    mostrarCarga();
    window.location.href = "index.php";
}

function Almacen() {
    mostrarCarga();
    window.location.href = "almacenes.php";
}

function Localizaciones() {
    mostrarCarga();
    window.location.href = "localizaciones.php";
}

function Grupos() {
    mostrarCarga();
    window.location.href = "grupos.php";
}

function PartNumbers() {
    mostrarCarga();
    window.location.href = "partnumbers.php";
}

function GestionarAdministradores() {
    mostrarCarga();
    window.location.href = "administradores.php";
}

function Cronograma() {
    mostrarCarga();
    window.location.href = "cronograma.php";
}

function Aprobacion() {
    mostrarCarga();
    window.location.href = "aprobacionAjuste.php";
}

function Exactitud() {
    mostrarCarga();
    window.location.href = "exactitud.php";
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

