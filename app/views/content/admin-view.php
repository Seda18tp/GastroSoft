<?php
use app\controllers\adminController;

$rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
    header("Location: " . APP_URL . "login");
    exit();       
}

$insAdmin = new adminController();

// Procesar cambio de estado de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_cambiar_estado_reserva'])) {
    $insAdmin->cambiarEstadoReservaC();
}

$metricas = $insAdmin->obtenerMetricas();
$reservas = $insAdmin->obtenerReservasDia();
?>
<main class="flex-1 w-full max-w-[1550px] mx-auto p-4 sm:p-6 font-mono select-none">
  
  <!-- Botones de Navegación Rápida -->
  <div class="flex flex-wrap gap-3 mb-6">
    <a href="<?php echo APP_URL; ?>estadisticas" class="px-5 py-2 rounded-full font-bold text-xs bg-[#182338] border border-[#2E3F5C] text-slate-300 hover:text-white hover:border-[#F59E0B] transition shadow-md">
      ESTADÍSTICAS
    </a>
    <a href="<?php echo APP_URL; ?>carta" class="px-5 py-2 rounded-full font-bold text-xs bg-[#182338] border border-[#2E3F5C] text-slate-300 hover:text-white hover:border-[#F59E0B] transition shadow-md">
      CARTA
    </a>
    <a href="<?php echo APP_URL; ?>inventario" class="px-5 py-2 rounded-full font-bold text-xs bg-[#182338] border border-[#2E3F5C] text-slate-300 hover:text-white hover:border-[#F59E0B] transition shadow-md">
      INVENTARIO
    </a>
    <a href="<?php echo APP_URL; ?>usuarios" class="px-5 py-2 rounded-full font-bold text-xs bg-[#182338] border border-[#2E3F5C] text-slate-300 hover:text-white hover:border-[#F59E0B] transition shadow-md">
      USUARIOS
    </a>
  </div>

  <!-- Tarjetas Superiores (KPIs) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <!-- Ventas del Día -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">VENTAS DEL DÍA:</span>
      <span class="text-2xl font-black text-white mt-2">
        $ <?= number_format($metricas['ventas_dia'], 0, ',', '.') ?>
      </span>
    </div>

    <!-- Mesas Ocupadas -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">MESAS OCUPADAS:</span>
      <span class="text-2xl font-black text-[#F59E0B] mt-2">
        <?= $metricas['mesas_ocupadas'] ?>
      </span>
    </div>

    <!-- Total Pedidos -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">TOTAL PEDIDOS:</span>
      <span class="text-2xl font-black text-white mt-2">
        <?= $metricas['total_pedidos'] ?>
      </span>
    </div>

    <!-- Alertas Stock -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-bold text-rose-400 uppercase tracking-wider">ALERTAS STOCK:</span>
      <span class="text-2xl font-black <?= $metricas['alertas_stock'] > 0 ? 'text-rose-500' : 'text-slate-400' ?> mt-2">
        <?= $metricas['alertas_stock'] ?>
      </span>
    </div>

  </div>

  <!-- Sección de Reservas del Día -->
  <div class="flex items-center justify-between mb-4">
    <h3 class="text-base font-extrabold text-white tracking-wide">Reservas del día</h3>
    <a href="<?php echo APP_URL; ?>nuevaReserva" class="px-5 py-2 rounded-full text-xs font-bold bg-[#F59E0B] text-[#0D1322] hover:brightness-110 transition shadow-md">
      Nueva Reserva
    </a>
  </div>

  <!-- Tabla de Reservas -->
  <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl shadow-xl overflow-hidden font-mono">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-[#23324A] text-slate-400 font-bold uppercase bg-[#101726]">
          <th class="py-4 px-6">HORA</th>
          <th class="py-4 px-6">CLIENTE</th>
          <th class="py-4 px-6">COMENSALES</th>
          <th class="py-4 px-6">MESA</th>
          <th class="py-4 px-6">ESTADO</th>
          <th class="py-4 px-6 text-center">ACCIONES</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#23324A]/60 text-slate-200">
        <?php if (empty($reservas)): ?>
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-500 text-xs">
              No hay reservas registradas para hoy.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($reservas as $res): 
              $estadoVal = strtolower(trim($res['estado'] ?? ''));
              $badgeClase = 'bg-blue-500/15 text-blue-300 border-blue-500/30';
              if ($estadoVal === 'completada' || $estadoVal === 'finalizada') {
                  $badgeClase = 'bg-[#10B981]/15 text-[#10B981] border-[#10B981]/30';
              } elseif ($estadoVal === 'cancelada') {
                  $badgeClase = 'bg-rose-500/15 text-rose-400 border-rose-500/30';
              }
          ?>
            <tr class="hover:bg-[#1B2539]/50 transition">
              <td class="py-4 px-6 text-[#F59E0B] font-bold">
                <?= date('h:i A', strtotime($res['hora_inicio'])) ?>
              </td>
              <td class="py-4 px-6 font-bold text-white">
                <?= htmlspecialchars($res['nombre_cliente']) ?>
                <?php if (!empty($res['telefono'])): ?>
                  <span class="block text-[11px] text-slate-400 font-sans font-normal"><?= htmlspecialchars($res['telefono']) ?></span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6">
                <?= $res['cantidad_personas'] ?> pers.
              </td>
              <td class="py-4 px-6 text-white font-semibold">
                Mesa #<?= htmlspecialchars($res['numero_mesa']) ?>
              </td>
              <td class="py-4 px-6">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold border <?= $badgeClase ?>">
                  <?= htmlspecialchars($res['estado']) ?>
                </span>
              </td>
              <td class="py-4 px-6 text-center">
                <button type="button" 
                        onclick='abrirModalDetalleReserva(<?= json_encode($res, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                        class="text-xs text-[#F59E0B] hover:underline font-semibold cursor-pointer">
                  Ver detalles
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

<!-- Modal Emergente de Detalles de Reserva -->
<div id="modal-detalle-reserva" class="fixed inset-0 bg-[#0A0F1D]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl font-mono">
    
    <div class="flex items-center justify-between pb-4 border-b border-[#23324A] mb-5">
      <div>
        <h3 class="text-white font-bold text-sm uppercase">Detalle de la Reserva</h3>
        <span id="det-reserva-id" class="text-[11px] text-slate-400 font-mono"></span>
      </div>
      <button type="button" onclick="cerrarModalDetalleReserva()" class="text-slate-400 hover:text-white text-lg">✕</button>
    </div>

    <!-- Datos Informativos -->
    <div class="space-y-3 text-xs mb-6">
      <div class="bg-[#162032] p-3.5 rounded-xl border border-[#26354D] flex justify-between items-center">
        <span class="text-slate-400 uppercase font-bold">Cliente:</span>
        <span id="det-cliente" class="text-white font-sans font-bold text-sm"></span>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#162032] p-3 rounded-xl border border-[#26354D]">
          <span class="text-slate-400 block text-[10px] uppercase font-bold">Teléfono</span>
          <span id="det-telefono" class="text-white font-bold"></span>
        </div>
        <div class="bg-[#162032] p-3 rounded-xl border border-[#26354D]">
          <span class="text-slate-400 block text-[10px] uppercase font-bold">Mesa Asignada</span>
          <span id="det-mesa" class="text-[#F59E0B] font-bold"></span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#162032] p-3 rounded-xl border border-[#26354D]">
          <span class="text-slate-400 block text-[10px] uppercase font-bold">Comensales</span>
          <span id="det-personas" class="text-white font-bold"></span>
        </div>
        <div class="bg-[#162032] p-3 rounded-xl border border-[#26354D]">
          <span class="text-slate-400 block text-[10px] uppercase font-bold">Horario</span>
          <span id="det-horario" class="text-emerald-400 font-bold"></span>
        </div>
      </div>

      <div class="bg-[#162032] p-3.5 rounded-xl border border-[#26354D]">
        <span class="text-slate-400 block text-[10px] uppercase font-bold mb-1">Observaciones:</span>
        <p id="det-observaciones" class="text-slate-200 font-sans italic text-xs leading-relaxed"></p>
      </div>
    </div>

    <!-- Formulario para Cambiar Estado -->
    <form action="" method="POST" class="pt-3 border-t border-[#23324A] flex flex-col sm:flex-row gap-3 items-center">
      <input type="hidden" name="modulo_cambiar_estado_reserva" value="1">
      <input type="hidden" name="reserva_id" id="form-reserva-id">

      <div class="w-full sm:flex-1">
        <select name="nuevo_estado" id="form-reserva-estado" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-3 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
          <option value="Confirmada">Confirmada</option>
          <option value="finalizada">Completa</option>
          <option value="Cancelada">Cancelada</option>
        </select>
      </div>

      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-full bg-[#10B981] text-[#0D1322] font-bold text-xs uppercase hover:brightness-110 transition shadow-md shadow-[#10B981]/20 cursor-pointer">
        Guardar Estado
      </button>
    </form>

  </div>
</div>

<script>
function abrirModalDetalleReserva(res) {
  document.getElementById('det-reserva-id').innerText = '#' + res.id;
  document.getElementById('form-reserva-id').value = res.id;
  document.getElementById('det-cliente').innerText = res.nombre_cliente;
  document.getElementById('det-telefono').innerText = res.telefono ? res.telefono : 'No registrado';
  document.getElementById('det-mesa').innerText = 'Mesa #' + res.numero_mesa;
  document.getElementById('det-personas').innerText = res.cantidad_personas + ' personas';
  
  const hInicio = res.hora_inicio ? res.hora_inicio.substring(0, 5) : '--:--';
  const hFin = res.hora_fin ? res.hora_fin.substring(0, 5) : '--:--';
  document.getElementById('det-horario').innerText = hInicio + ' a ' + hFin;
  
  document.getElementById('det-observaciones').innerText = res.observaciones ? res.observaciones : 'Sin observaciones especiales.';
  document.getElementById('form-reserva-estado').value = res.estado;

  document.getElementById('modal-detalle-reserva').classList.remove('hidden');
}

function cerrarModalDetalleReserva() {
  document.getElementById('modal-detalle-reserva').classList.add('hidden');
}
</script>