<?php
    use app\controllers\cajaController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador' && $rolActual !== 'cajero')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insCaja = new cajaController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_cerrar_caja'])) {
        $insCaja->procesarCierreCaja();
    }

    $balance = $insCaja->obtenerBalanceCierre();
?>

<main class="flex-1 p-6 md:p-8 max-w-4xl mx-auto w-full font-mono select-none">
  <!-- Cabecera -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-2xl font-black text-white tracking-wide">Cierre de Caja</h2>
      <p class="text-xs text-slate-400 mt-1">Liquidación y balance del turno actual</p>
    </div>
    <a href="<?php echo APP_URL; ?>caja" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#162032] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
  </div>

  <!-- Tarjetas de Balance -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5 shadow-lg">
      <span class="text-xs text-slate-400 uppercase font-bold">Ventas Turno</span>
      <div class="text-2xl font-black text-[#10B981] mt-2">
        $ <?= number_format($balance['ventas'], 0, ',', '.') ?>
      </div>
    </div>

    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5 shadow-lg">
      <span class="text-xs text-slate-400 uppercase font-bold">Gastos Registrados</span>
      <div class="text-2xl font-black text-[#EF4444] mt-2">
        $ <?= number_format($balance['gastos'], 0, ',', '.') ?>
      </div>
    </div>

    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5 shadow-lg">
      <span class="text-xs text-slate-400 uppercase font-bold">Total Esperado</span>
      <div class="text-2xl font-black text-[#38BDF8] mt-2">
        $ <?= number_format($balance['esperado'], 0, ',', '.') ?>
      </div>
    </div>
  </div>

  <!-- Formulario de Liquidación -->
  <div class="bg-[#121929] border border-[#23324A] rounded-3xl p-6 md:p-8 shadow-2xl">
    <form action="" method="POST" autocomplete="off" class="space-y-5">
      <input type="hidden" name="modulo_cerrar_caja" value="1">
      <input type="hidden" name="ventas_totales" value="<?= $balance['ventas'] ?>">
      <input type="hidden" name="gastos_totales" value="<?= $balance['gastos'] ?>">
      <input type="hidden" name="monto_esperado" id="monto_esperado" value="<?= $balance['esperado'] ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Monto Real en Efectivo ($)</label>
          <input type="number" 
                 step="any" 
                 name="monto_real" 
                 id="monto_real" 
                 required 
                 placeholder="0.00" 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F59E0B]"
                 oninput="calcularDiferencia(this.value)">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Diferencia Calculada</label>
          <div id="box-diferencia" class="w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-[#F59E0B] flex items-center justify-between">
            <span id="texto-diferencia">$ 0.00</span>
            <span id="badge-estado" class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full hidden"></span>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Observaciones del Cierre</label>
        <textarea name="observaciones" 
                  rows="3" 
                  placeholder="Comentarios del turno, novedades, billetes falsos o justificaciones de faltantes/sobrantes..." 
                  class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl p-4 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans"></textarea>
      </div>

      <button type="submit" 
              onclick="return confirm('¿Confirmas el cierre del turno de caja?')" 
              class="w-full py-3.5 rounded-full bg-[#F59E0B] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#F59E0B]/20 cursor-pointer">
        Confirmar y Cerrar Caja
      </button>
    </form>
  </div>
</main>

<script>
function calcularDiferencia(valorRealStr) {
  const esperado = parseFloat(document.getElementById('monto_esperado').value) || 0;
  const real = parseFloat(valorRealStr) || 0;
  const dif = real - esperado;

  const textoDif = document.getElementById('texto-diferencia');
  const badge = document.getElementById('badge-estado');
  const contenedor = document.getElementById('box-diferencia');

  // Formato de moneda
  const difFormateada = Math.abs(dif).toLocaleString('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  badge.classList.remove('hidden');

  if (dif === 0) {
    textoDif.innerText = '$ 0.00';
    contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-[#10B981] flex items-center justify-between";
    badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30";
    badge.innerText = 'Exacto';
  } else if (dif > 0) {
    textoDif.innerText = '+ $ ' + difFormateada;
    contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-sky-400 flex items-center justify-between";
    badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-sky-500/15 text-sky-400 border border-sky-500/30";
    badge.innerText = 'Sobrante';
  } else {
    textoDif.innerText = '- $ ' + difFormateada;
    contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-rose-500 flex items-center justify-between";
    badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30";
    badge.innerText = 'Faltante';
  }
}
</script>