<?php
    use app\controllers\cocinaController;

    if (!isset($_SESSION['id_usuario']) || strtolower($_SESSION['rol'] ?? '') !== 'cocinero') {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insCocina = new cocinaController();
    $historial = $insCocina->historialPedidos(25);
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Historial de Pedidos</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Registro de órdenes despachadas</p>
    </div>
    <a href="<?php echo APP_URL; ?>cocinero" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
  </div>

  <div class="bg-[#121929] border border-[#23324A] rounded-2xl shadow-xl overflow-hidden">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-[#23324A] text-slate-400 font-bold uppercase bg-[#101726]">
          <th class="py-4 px-6">ID Pedido</th>
          <th class="py-4 px-6">Mesa</th>
          <th class="py-4 px-6">Hora / Fecha</th>
          <th class="py-4 px-6">Mesero</th>
          <th class="py-4 px-6">Estado</th>
          <th class="py-4 px-6 text-right">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#23324A]/60 text-slate-200">
        <?php if (empty($historial)): ?>
          <tr>
            <td colspan="6" class="py-8 text-center text-slate-500">
              No hay pedidos registrados en el historial.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($historial as $pedido): ?>
            <tr class="hover:bg-[#1B2539]/50 transition">
              <td class="py-4 px-6 font-bold text-white">#<?= str_pad($pedido['id'], 4, '0', STR_PAD_LEFT) ?></td>
              <td class="py-4 px-6 text-[#F59E0B]">Mesa #<?= htmlspecialchars($pedido['mesa_numero']) ?></td>
              <td class="py-4 px-6 text-slate-400">
                <?= date('h:i A - d/m', strtotime($pedido['fecha'])) ?>
              </td>
              <td class="py-4 px-6 font-sans"><?= htmlspecialchars($pedido['mesero_nombre']) ?></td>
              <td class="py-4 px-6">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30">
                  <?= htmlspecialchars($pedido['estado']) ?>
                </span>
              </td>
              <td class="py-4 px-6 text-right font-bold text-white">
                $ <?= number_format($pedido['total'], 0, ',', '.') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>