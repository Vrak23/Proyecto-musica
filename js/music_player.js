document.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('bg-music');
    const icon = document.getElementById('music-icon');

    // Función para activar el sonido
    const startMusic = () => {
        audio.play().then(() => {
            if (icon) icon.className = 'fa-solid fa-pause';
            // Una vez que suena, quitamos los disparadores para no reiniciar la canción
            ['click', 'scroll', 'keydown'].forEach(event => 
                document.removeEventListener(event, startMusic)
            );
        }).catch(err => console.log("Esperando interacción..."));
    };

    // Escuchamos cualquier acción del usuario para "desbloquear" el audio
    document.addEventListener('click', startMusic);
    document.addEventListener('scroll', startMusic);
    document.addEventListener('keydown', startMusic);
});