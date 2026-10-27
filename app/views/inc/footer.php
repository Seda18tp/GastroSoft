<footer class="w-full bg-[#121929] border-t border-[#23324A] px-6 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
    <div class="font-mono text-[11px]">
        Copyright &copy; <?= date('Y') ?> GastroSoft. Todos los derechos reservados.
    </div>
</footer>

<script>
(() => {
    let versionActual = null;

    function auditarCambios() {
        // Pausar si el usuario tiene un modal abierto o está escribiendo
        const modalAbierto = document.querySelector('#modal-agregar-plato:not(.hidden), #modal-ajuste:not(.hidden), .fixed:not(.hidden)');
        const escribiendo = document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
        const hayBorrador = typeof comandaBorrador !== 'undefined' && comandaBorrador.length > 0;

        if (modalAbierto || escribiendo || hayBorrador) {
            return;
        }

        fetch('<?= APP_URL ?>app/ajax/sincronizarAjax.php', { cache: 'no-store' })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.version) return;

                if (versionActual === null) {
                    versionActual = data.version;
                } else if (versionActual !== data.version) {
                    versionActual = data.version;

                    // Si hay una mesa seleccionada en salón, conservar el parámetro URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const mesaId = (typeof mesaSeleccionadaGlobal !== 'undefined' && mesaSeleccionadaGlobal) 
                                   ? mesaSeleccionadaGlobal.mesa_id 
                                   : urlParams.get('mesa');

                    if (mesaId && window.location.pathname.includes('mesero')) {
                        window.location.replace(window.location.pathname + '?mesa=' + mesaId);
                    } else {
                        window.location.reload();
                    }
                }
            })
            .catch(err => console.debug("Sync en espera:", err));
    }

    // Consultar cada 3.5 segundos
    setInterval(auditarCambios, 3500);
})();
</script>