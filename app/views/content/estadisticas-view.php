<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();
    $statsMetodos = $insAdmin->obtenerEstadisticas();
    $topProductos = $insAdmin->obtenerTop(5);
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
  <!--Nav-->
  <div class="flex flex-wrap items-center gap-2 mb-8">
    <a href="<?php echo APP_URL; ?>admin" class="px-5 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
    <a href="<?php echo APP_URL; ?>estadisticas" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition">
      Estadísticas
    </a>
    <a href="<?php echo APP_URL; ?>carta" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
      Carta
    </a>
    <a href="<?php echo APP_URL; ?>inventario" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
      Inventario
    </a>
    <a href="<?php echo APP_URL; ?>usuarios" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
      Usuarios
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!--Ventas-->
    <div class="bg-[#121929] border border-[#23324A] rounded-2xl p-6 shadow-xl flex flex-col justify-between">
      <div>
        <h3 class="font-mono text-sm font-bold text-white mb-4">Ventas por Método</h3>
        <div class="space-y-3 font-mono text-xs">
          <?php if (empty($statsMetodos['metodos'])): ?>
            <p class="py-6 text-center text-slate-500">No hay pagos registrados hoy.</p>
          <?php else: ?>
            <?php foreach ($statsMetodos['metodos'] as $item): ?>
              <div class="flex justify-between py-2 border-b border-[#23324A]/70">
                <span class="text-slate-400"><?= htmlspecialchars($item['metodo']) ?></span>
                <span class="font-bold text-white">
                  $ <?= number_format($item['monto'], 0, ',', '.') ?> 
                  <span class="text-slate-400 font-normal">(<?= $item['porcentaje'] ?>%)</span>
                </span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if ($statsMetodos['gran_total'] > 0): ?>
        <div class="pt-4 border-t border-[#23324A] flex justify-between items-center text-xs font-mono mt-4">
          <span class="text-slate-400 font-bold uppercase">Total Recaudado:</span>
          <span class="font-black text-[#10B981] text-sm">
            $ <?= number_format($statsMetodos['gran_total'], 0, ',', '.') ?>
          </span>
        </div>
      <?php endif; ?>
    </div>

    <!--PlatosTop-->
    <div class="lg:col-span-2 bg-[#121929] border border-[#23324A] rounded-2xl p-6 shadow-xl">
      <h3 class="font-mono text-sm font-bold text-white mb-4">Top Productos Vendidos Hoy</h3>
      
      <div class="space-y-4 font-mono text-xs">
        <?php if (empty($topProductos)): ?>
          <p class="py-8 text-center text-slate-500">No hay comandas despachadas hoy.</p>
        <?php else: ?>
          <?php foreach ($topProductos as $prod): ?>
            <div>
              <div class="flex justify-between text-slate-300 mb-1">
                <span class="font-medium"><?= htmlspecialchars($prod['nombre']) ?></span>
                <span class="font-bold" style="color: <?= $prod['color'] ?>;">
                  <?= $prod['cantidad'] ?> <?= ($prod['cantidad'] == 1) ? 'unidad' : 'unidades' ?>
                </span>
              </div>
              <div class="w-full bg-[#182338] h-2 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" 
                     style="width: <?= $prod['ancho'] ?>%; background-color: <?= $prod['color'] ?>;">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</main>