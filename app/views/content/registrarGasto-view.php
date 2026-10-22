<main class="flex-1 p-6 md:p-8 max-w-xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Registrar Gasto de Caja</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Salida de efectivo menor o compras de urgencia</p>
    </div>
    <a href="<?php echo APP_URL; ?>caja" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
  </div>

  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 shadow-2xl">
    <form class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Concepto / Descripción</label>
        <input type="text" placeholder="Ej: Compra de hielo, limones de urgencia..." class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Monto Retirado ($)</label>
        <input type="number" step="any" placeholder="0.00" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F59E0B]">
      </div>
      <div class="pt-4">
        <button type="button" class="w-full py-3.5 rounded-full bg-[#EF4444] text-white font-mono text-xs font-extrabold uppercase hover:brightness-110 transition shadow-lg shadow-[#EF4444]/20">
          Guardar Salida de Dinero
        </button>
      </div>
    </form>
  </div>
</main>
