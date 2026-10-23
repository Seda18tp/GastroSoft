<?php
    use app\controllers\meseroController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insMesero = new meseroController();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_guardar_mesa'])) {
        $alerta = $insMesero->guardarMesas();
    }

    $mesas = $insMesero->obtenerMesas();
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-mono select-none">
  <!-- Cabecera -->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Configuración del Salón</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Gestión y distribución de mesas físicas</p>
    </div>
    <div class="flex items-center gap-3">
      <button type="button" 
              onclick="abrirModalMesa(null)" 
              class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#10B981] text-[#0D1322] hover:brightness-110 shadow-lg shadow-[#10B981]/20 transition cursor-pointer">
        + Nueva Mesa
      </button>
      <a href="<?php echo APP_URL; ?>mesas" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
        Volver
      </a>
    </div>
  </div>

  <?php if (isset($alerta)): ?>
    <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
      <?= htmlspecialchars($alerta['texto']) ?>
    </div>
  <?php endif; ?>

  <!-- Cuadrícula de Mesas -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    <?php if (empty($mesas)): ?>
      <div class="col-span-full py-20 text-center text-slate-500 text-xs">
        No hay mesas registradas en el salón. Crea una con el botón superior.
      </div>
    <?php else: ?>
      <?php foreach ($mesas as $m): ?>
        <div onclick='abrirModalMesa(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
             class="bg-[#121A2A] border border-[#23324A] hover:border-[#F59E0B] rounded-2xl p-4 flex flex-col items-center justify-center text-center transition transform hover:scale-[1.03] cursor-pointer shadow-lg group">
          <span class="text-2xl font-black text-white group-hover:text-[#F59E0B] transition">
            #<?= htmlspecialchars($m['numero_mesa']) ?>
          </span>
          <span class="text-[11px] text-slate-400 mt-1 font-sans truncate max-w-[120px]">
            <?= htmlspecialchars($m['ubicacion']) ?>
          </span>
          <span class="text-[10px] text-[#10B981] font-bold uppercase mt-2">
            Capacidad: <?= $m['capacidad'] ?>
          </span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- Modal Crear / Editar Mesa -->
<div id="modal-mesa" class="fixed inset-0 bg-[#0A0F1D]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 font-mono">
  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl">
    
    <div class="flex items-center justify-between pb-4 border-b border-[#23324A] mb-5">
      <h3 class="text-white font-bold text-sm uppercase" id="modal-titulo-mesa">Nueva Mesa</h3>
      <button type="button" onclick="cerrarModalMesa()" class="text-slate-400 hover:text-white text-lg cursor-pointer">✕</button>
    </div>

    <form action="" method="POST" autocomplete="off" class="space-y-4">
      <input type="hidden" name="modulo_guardar_mesa" value="1">
      <input type="hidden" name="mesa_id" id="form-mesa-id" value="0">

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Número o Identificador</label>
        <input type="text" name="numero_mesa" id="form-numero-mesa" required placeholder="Ej: 1, 2, B1..." 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Zona / Salón</label>
        <input type="text" name="ubicacion" id="form-ubicacion" required placeholder="Ej: Salón Principal, Terraza, Caseta..." 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Capacidad Máxima (Comensales)</label>
        <input type="number" name="capacidad" id="form-capacidad" min="1" max="50" value="4" required 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div class="pt-4 flex gap-3">
        <button type="button" onclick="cerrarModalMesa()" 
                class="flex-1 py-3 rounded-full bg-[#182338] border border-[#26354D] text-slate-300 font-bold text-xs uppercase hover:text-white transition cursor-pointer">
          Cancelar
        </button>
        <button type="submit" 
                class="flex-1 py-3 rounded-full bg-[#10B981] text-[#0D1322] font-bold text-xs uppercase hover:brightness-110 transition shadow-lg shadow-[#10B981]/20 cursor-pointer">
          Guardar
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function abrirModalMesa(mesa) {
  const modal = document.getElementById('modal-mesa');
  
  if (mesa) {
    document.getElementById('modal-titulo-mesa').innerText = 'Editar Mesa #' + mesa.numero_mesa;
    document.getElementById('form-mesa-id').value = mesa.id;
    document.getElementById('form-numero-mesa').value = mesa.numero_mesa;
    document.getElementById('form-ubicacion').value = mesa.ubicacion;
    document.getElementById('form-capacidad').value = mesa.capacidad;
  } else {
    document.getElementById('modal-titulo-mesa').innerText = 'Nueva Mesa';
    document.getElementById('form-mesa-id').value = 0;
    document.getElementById('form-numero-mesa').value = '';
    document.getElementById('form-ubicacion').value = '';
    document.getElementById('form-capacidad').value = 4;
  }
  
  modal.classList.remove('hidden');
}

function cerrarModalMesa() {
  document.getElementById('modal-mesa').classList.add('hidden');
}
</script>