<!-- app/views/inc/script.php -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('user-menu-btn');
        const dropdown = document.getElementById('user-dropdown');

        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', () => {
                if (!dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
    // Generador de sonido nativo sin archivos externos
    function reproducirSonidoNotificacion() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            
            // Primera nota (tono base)
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, audioCtx.currentTime); // Nota D5
            gain1.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start();
            osc1.stop(audioCtx.currentTime + 0.2);

            // Segunda nota (tono agudo)
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // Nota A5
            gain2.gain.setValueAtTime(0.2, audioCtx.currentTime + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(audioCtx.currentTime + 0.1);
            osc2.stop(audioCtx.currentTime + 0.35);
        } catch (e) {
            console.warn("El navegador bloqueó el audio previo a la interacción del usuario.");
        }
    }
    function marcarComoLeida(notificacionId, elementoDOM) {
    // 1. Reproducir el sonido inmediatamente al hacer clic / leer
    reproducirSonidoNotificacion();

    // 2. Notificar al servidor para pasar leido a 1
    const datos = new FormData();
    datos.append('accion', 'marcar_leida');
    datos.append('id', notificacionId);

    fetch('<?= APP_URL ?>app/ajax/notificacionesAjax.php', {
        method: 'POST',
        body: datos
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito && elementoDOM) {
            // Ocultar o atenuar la notificación en la interfaz
            elementoDOM.classList.add('opacity-40', 'pointer-events-none');
        }
    })
    .catch(err => console.error(err));
    }
</script>