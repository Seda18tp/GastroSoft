<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Verifica el rol del usuario
if ($_SESSION['role_id'] !== 1) {
    echo "Acceso denegado. Solo los administradores pueden acceder a esta página.";
    exit;
}
?>
<!--admin-view.php-->
<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
  <!-- menu-->
  <div class="flex flex-wrap items-center gap-2 mb-8">
    <a href="<?php echo APP_URL; ?>estadisticas" 
       class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white hover:bg-brandAccent">
      Estadísticas
    </a>
    <a href="<?php echo APP_URL; ?>carta" 
       class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white hover:bg-brandAccent">
      Carta
    </a>
    <a href="<?php echo APP_URL; ?>inventario" 
       class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white hover:bg-brandAccent">
      Inventario
    </a>
    <a href="<?php echo APP_URL; ?>usuarios" 
       class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white hover:bg-brandAccent">
      Usuarios
    </a>
  </div>

  <!--Tarjetas Resumen-->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <!--Ventas-->
    <div class="bg-[#121929] border border-pos-border rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-semibold text-pos-text-muted uppercase tracking-wider">Ventas del día:</span>
      <div class="mt-3 font-mono text-2xl font-black text-pos-green tracking-tight">
      </div>
    </div>

    <!--Mesas-->
    <div class="bg-[#121929] border border-pos-border rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-semibold text-pos-text-muted uppercase tracking-wider">Mesas Ocupadas:</span>
      <div class="mt-3 font-mono text-2xl font-black text-pos-cyan tracking-tight">
        </span>
      </div>
    </div>

    <!--Total pedidos-->
    <div class="bg-[#121929] border border-pos-border rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-semibold text-pos-text-muted uppercase tracking-wider">Total pedidos:</span>
      <div class="mt-3 font-mono text-2xl font-black text-pos-orange tracking-tight">
      </div>
    </div>

    <!--Alertas stock-->
    <div class="bg-[#121929] border border-pos-border rounded-2xl p-5 shadow-lg flex flex-col justify-between">
      <span class="text-xs font-semibold text-red-400 uppercase tracking-wider">Alertas stock:</span>
      <div class="mt-3 font-mono text-2xl font-black text-pos-red tracking-tight">
      </div>
    </div>

  </div>

  <!--Reservas-->
  <div class="mb-4 flex items-center justify-between">
    <h3 class="text-base font-extrabold text-white tracking-wide">Reservas del día</h3>
    <a href="<?php echo APP_URL; ?>nuevaReserva" class="px-4 py-1.5 bg-brandAccent border border-pos-border rounded-full text-xs font-semibold text-pos-orange hover:bg-orange-300 hover:text-pos-bg transition">
      + Nueva Reserva
    </a>
  </div>

  <div class="bg-pos-card border border-pos-border rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm font-sans">
        <thead>
          <tr class="border-b border-pos-border text-pos-text-muted text-xs font-bold uppercase tracking-wider bg-[#111A29]">
            <th class="py-4 px-6">Hora</th>
            <th class="py-4 px-6">Cliente</th>
            <th class="py-4 px-6 text-center">Comensales</th>
            <th class="py-4 px-6 text-center">Mesa</th>
            <th class="py-4 px-6">Estado</th>
            <th class="py-4 px-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-pos-border/60 font-mono text-xs text-slate-200">
          <?php if (!empty($reservas)): ?>
            <?php foreach ($reservas as $reserva): ?>
              <tr class="hover:bg-[#1B2539]/50 transition duration-150">
                <td class="py-4 px-6 font-semibold text-white">
                  <?= htmlspecialchars($reserva['hora']) ?>
                </td>
                <td class="py-4 px-6 font-sans font-medium text-slate-300">
                  <?= htmlspecialchars($reserva['cliente']) ?>
                </td>
                <td class="py-4 px-6 text-center font-bold text-pos-cyan">
                  <?= htmlspecialchars($reserva['comensales']) ?>
                </td>
                <td class="py-4 px-6 text-center font-bold text-pos-orange">
                  <?= htmlspecialchars($reserva['mesa']) ?>
                </td>
                <td class="py-4 px-6 font-sans">
                  <?php if ($reserva['estado'] === 'Confirmado'): ?>
                    <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-pos-green/15 text-pos-green border border-pos-green/30">
                      Confirmado
                    </span>
                  <?php else: ?>
                    <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-pos-orange/15 text-pos-orange border border-pos-orange/30">
                      <?= htmlspecialchars($reserva['estado']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-6 text-right font-sans space-x-3">
                  <a href="#" 
                     class="text-slate-300 hover:text-white font-semibold transition">
                    Editar
                  </a>
                  <a href="#"
                     class="text-red-400 hover:underline font-semibold transition">
                    Eliminar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="py-8 text-center text-pos-text-muted font-sans text-xs">
                No hay reservas registradas para hoy.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>