function mostrarCarga() {
    const spinner = document.getElementById('spinner');
    if (spinner) {
        spinner.style.display = 'flex';
    }
}

function ocultarCarga() {
    const spinner = document.getElementById('spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}
