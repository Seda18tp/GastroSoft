<footer class="w-full bg-[#121929] border-t border-[#23324A] px-6 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
    <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="font-mono text-[11px] text-slate-400">Sincronización en vivo activa</span>
    </div>
    <div class="font-mono text-[11px]">
        Copyright &copy; <?= date('Y') ?> GastroSoft. Todos los derechos reservados.
    </div>
</footer>

<script>
(() => {
    let ultimoHashSistema = null;
    let sincronizando = false;

    async function auditarCambiosEnVivo() {
        if (sincronizando) return;

        // 1. Si el usuario está interactuando, pausar para no perder cambios
        const modalAbierto = document.querySelector('.fixed:not(.hidden), [role="dialog"]:not(.hidden)');
        const escribiendo = document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
        const hayBorrador = typeof comandaBorrador !== 'undefined' && comandaBorrador.length > 0;

        if (modalAbierto || escribiendo || hayBorrador) {
            return;
        }

        try {
            sincronizando = true;
            const res = await fetch('<?= APP_URL ?>app/ajax/sincronizarAjax.php', { cache: 'no-store' });
            if (!res.ok) throw new Error("HTTP error " + res.status);
            const data = await res.json();

            if (!data || !data.version) return;

            if (ultimoHashSistema === null) {
                ultimoHashSistema = data.version;
            } else if (ultimoHashSistema !== data.version) {
                ultimoHashSistema = data.version;

                // 2. Actualización suave del contenedor principal sin recargar toda la ventana
                const contenedor = document.getElementById('pageContent');
                if (contenedor) {
                    const respuestaHtml = await fetch(window.location.href, { cache: 'no-store' });
                    const textoHtml = await respuestaHtml.text();
                    const docTemporal = new DOMParser().parseFromString(textoHtml, 'text/html');
                    const nuevoContenedor = docTemporal.getElementById('pageContent');

                    if (nuevoContenedor) {
                        contenedor.innerHTML = nuevoContenedor.innerHTML;
                        
                        // Re-ejecutar scripts inline contenidos en la vista si existen
                        nuevoContenedor.querySelectorAll('script').forEach(viejoScript => {
                            const nuevoScript = document.createElement('script');
                            Array.from(viejoScript.attributes).forEach(attr => nuevoScript.setAttribute(attr.name, attr.value));
                            nuevoScript.appendChild(document.createTextNode(viejoScript.innerHTML));
                            contenedor.appendChild(nuevoScript);
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            }
        } catch (error) {
            console.debug("Auto-sync en espera:", error);
        } finally {
            sincronizando = false;
        }
    }

    // Monitoreo cada 3.5 segundos
    setInterval(auditarCambiosEnVivo, 3500);
})();
</script>