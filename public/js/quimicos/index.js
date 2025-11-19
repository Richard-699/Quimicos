const video = document.getElementById('backgroundVideo');

// Muestra la pantalla de carga
mostrarCarga();

// Asegúrate que no se reproduzca automáticamente hasta que esté listo
video.pause();
video.preload = 'auto';

// Escucha cuando los metadatos están listos (duración disponible)
video.addEventListener('loadedmetadata', () => {
    if (video.duration > 26) {
        video.currentTime = 26;
    }
});

// Escucha cuando está listo para reproducirse sin interrupciones
video.addEventListener('canplaythrough', () => {
    // Reproduce manualmente cuando ya puede hacerlo fluidamente
    video.play();

    // Oculta carga suavemente
    setTimeout(() => {
        if (typeof ocultarCarga === 'function') {
            ocultarCarga();
        }
        video.classList.add('loaded'); // por si tienes una animación
    }, 500);
});
