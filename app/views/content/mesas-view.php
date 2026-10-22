<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Configuración del Salón</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Gestión y distribución de mesas físicas</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="<?php echo APP_URL; ?>mesero" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
        Ver Salón Mesero
      </a>
      <button class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#F59E0B] text-[#0D1322] hover:brightness-110 shadow-lg shadow-[#F59E0B]/20 transition">
        + Nueva Mesa
      </button>
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-4 flex flex-col items-center justify-center text-center">
      <span class="text-2xl font-black text-white">#1</span>
      <span class="text-[11px] text-slate-400 mt-1">Salón Principal</span>
      <span class="text-[10px] text-[#10B981] font-bold uppercase mt-2">Capacidad: 4</span>
    </div>
  </div>
</main>
