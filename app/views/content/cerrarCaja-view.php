<main class="flex-1 p-6 md:p-8 max-w-4xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-2xl font-black text-white tracking-wide">Cierre de Caja</h2>
      <p class="text-xs text-slate-400 mt-1">Liquidación y balance del turno actual</p>
    </div>
    <a href="<?php echo APP_URL; ?>caja" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#162032] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver a caja
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5">
      <span class="text-xs text-slate-400 uppercase">Ventas Turno</span>
      <div class="text-2xl font-black text-[#10B981] mt-2">$ 846.000</div>
    </div>
    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5">
      <span class="text-xs text-slate-400 uppercase">Gastos Registrados</span>
      <div class="text-2xl font-black text-[#EF4444] mt-2">$ 45.000</div>
    </div>
    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-5">
      <span class="text-xs text-slate-400 uppercase">Total Esperado</span>
      <div class="text-2xl font-black text-[#38BDF8] mt-2">$ 801.000</div>
    </div>
  </div>

  <div class="bg-[#121929] border border-[#23324A] rounded-3xl p-6 shadow-2xl">
    <form class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Monto Real en Efectivo ($)</label>
          <input type="number" step="any" placeholder="0.00" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Diferencia Calculada</label>
          <div class="w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-[#F59E0B]">
            $ 0.00
          </div>
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Observaciones del Cierre</label>
        <textarea rows="3" placeholder="Comentarios del turno, novedades o faltantes..." class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl p-4 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans"></textarea>
      </div>
      <button type="button" class="w-full py-3.5 rounded-full bg-[#F59E0B] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 transition shadow-lg shadow-[#F59E0B]/20">
        Confirmar y Cerrar Caja
      </button>
    </form>
  </div>
</main>
