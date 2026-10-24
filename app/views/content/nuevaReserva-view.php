<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_nueva_reserva'])) {
        $alerta = $insAdmin->registrarReservaM();
    }

    $mesas = $insAdmin->obtenerMesasReserva();
?>

<main class="flex-1 p-6 md:p-8 max-w-2xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Registrar Reserva</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Agendar mesa en el restaurante</p>
    </div>
    <a href="<?php echo APP_URL; ?>admin" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
  </div>

  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 shadow-2xl">
    <?php if (isset($alerta)): ?>
      <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
        <?= htmlspecialchars($alerta['texto']) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off" class="space-y-4">
      <input type="hidden" name="modulo_nueva_reserva" value="1">

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Nombre del Cliente</label>
        <input type="text" name="nombre_cliente" required placeholder="Ej: Camilo Torres" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Teléfono de Contacto</label>
          <input type="text" name="telefono" placeholder="310..." 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Mesa Asignada</label>
          <select name="mesa_id" required class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="">Seleccione Mesa...</option>
            <?php foreach ($mesas as $m): ?>
              <option value="<?= $m['id'] ?>">
                Mesa #<?= htmlspecialchars($m['numero_mesa']) ?> (<?= $m['capacidad'] ?> Pers. - <?= htmlspecialchars($m['ubicacion']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Comensales</label>
          <input type="number" name="cantidad_personas" value="2" min="1" required 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs text-center focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Fecha</label>
          <input type="date" name="fecha_reserva" value="<?= date('Y-m-d') ?>" required 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-3 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Hora</label>
          <input type="time" name="hora_inicio" value="19:00" required 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-3 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Observaciones (Opcional)</label>
        <textarea name="observaciones" rows="2" placeholder="Notas sobre alergias, cumpleaños, ubicación..." 
                  class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans"></textarea>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full py-3.5 rounded-full bg-[#F59E0B] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#F59E0B]/20 cursor-pointer">
          Confirmar Reserva
        </button>
      </div>
    </form>
  </div>
</main>