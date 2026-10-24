<footer class="w-full bg-[#121929] border-t border-[#23324A] px-8 py-5 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
    <div class="flex ml-auto font-mono">
        Copyright &copy; 2026 GastroSoft. Todos los derechos reservados.
    </div>
</footer>

<script>
    let versionSistemaActual = null;

    function verificarCambiosSistema() {
        const algunModalAbierto = document.querySelector('.fixed:not(.hidden)');
        const inputsActivos = document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA' || document.activeElement.tagName === 'SELECT');
        const tieneBorradorMesero = typeof comandaBorrador !== 'undefined' && comandaBorrador.length > 0;

        if (algunModalAbierto || inputsActivos || tieneBorradorMesero) {
            return;
        }

        fetch('<?= APP_URL ?>app/ajax/sincronizarAjax.php')
            .then(res => {
                if (!res.ok) throw new Error("HTTP error " + res.status);
                return res.json();
            })
            .then(data => {
                if (!data || !data.version) return;

                if (versionSistemaActual === null) {
                    versionSistemaActual = data.version;
                } else if (versionSistemaActual !== data.version) {
                    versionSistemaActual = data.version;

                    const urlParams = new URLSearchParams(window.location.search);
                    const mesaActual = (typeof mesaSeleccionadaGlobal !== 'undefined' && mesaSeleccionadaGlobal) 
                                        ? mesaSeleccionadaGlobal.mesa_id 
                                        : urlParams.get('mesa');

                    if (mesaActual) {
                        window.location.replace(window.location.pathname + '?mesa=' + mesaActual);
                    } else {
                        window.location.reload();
                    }
                }
            })
            .catch(err => {
                console.error("Sincronizador en pausa:", err);
            });
    }

    setInterval(verificarCambiosSistema, 3500);
</script>