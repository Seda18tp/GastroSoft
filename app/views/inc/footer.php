<footer class="w-full bg-[#121929] border-t border-cardBorder px-8 py-5 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
    
    <div class="flex ml-auto">
        Copyright &copy; 2026 GastroSoft. Todos los derechos reservados.
    </div>
</footer>
<script>
    let versionSistemaActual = null;

    function verificarCambiosSistema() {
        const modalAbierto = document.querySelector('#modal-agregar-plato:not(.hidden)');
        const tieneBorradorPendiente = typeof comandaBorrador !== 'undefined' && comandaBorrador.length > 0;

        if (modalAbierto || tieneBorradorPendiente) {
            return;
        }

        fetch('<?= APP_URL ?>app/ajax/sincronizarAjax.php')
            .then(res => {
                if (!res.ok) throw new Error("HTTP error " + res.status);
                return res.json();
            })
            .then(data => {
                if (!data || !data.version) {
                    console.warn("Respuesta sin versión:", data);
                    return;
                }

                if (versionSistemaActual === null) {
                    versionSistemaActual = data.version;
                    console.log("Sincronizador activo. Versión inicial:", versionSistemaActual);
                } else if (versionSistemaActual !== data.version) {
                    console.log("Cambio detectado en base de datos. Actualizando...");
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
                console.error("Error al consultar sincronizarAjax.php:", err);
            });
    }

    setInterval(verificarCambiosSistema, 3500);
</script>