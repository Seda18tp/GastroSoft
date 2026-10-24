<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_ajuste_stock'])) {
        $insAdmin->ajustarStock();
    }

    $inventario = $insAdmin->obtenerInventario();
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
  <!--Nav-->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div class="flex flex-wrap items-center gap-2">
      <a href="<?php echo APP_URL; ?>admin" class="px-5 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Volver
      </a>
      <a href="<?php echo APP_URL; ?>estadisticas" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Estadísticas
      </a>
      <a href="<?php echo APP_URL; ?>carta" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Carta
      </a>
      <a href="<?php echo APP_URL; ?>inventario" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition">
        Inventario
      </a>
      <a href="<?php echo APP_URL; ?>usuarios" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Usuarios
      </a>
    </div>
  </div>

  <!--inventario-->
  <div class="bg-[#121929] border border-[#23324A] rounded-2xl shadow-xl overflow-hidden font-mono">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-[#23324A] text-slate-400 font-bold uppercase bg-[#101726]">
          <th class="py-4 px-6">Producto</th>
          <th class="py-4 px-6 text-center">Unidad</th>
          <th class="py-4 px-6 text-center">Stock Mínimo</th>
          <th class="py-4 px-6 text-center">Stock Actual</th>
          <th class="py-4 px-6 text-center">Estado</th>
          <th class="py-4 px-6 text-right">Acción</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#23324A]/60 text-slate-200">
        <?php if (empty($inventario)): ?>
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-500 text-xs">
              No hay productos registrados en inventario.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($inventario as $item): 
              $stockActual = (float)$item['stock_actual'];
              $stockMin = (float)$item['stock_minimo'];

              if ($stockActual <= 0) {
                  $colorTexto = 'text-rose-500';
                  $badgeClase = 'bg-rose-500/15 text-rose-400 border-rose-500/30';
                  $estadoTexto = 'Agotado';
              } elseif ($stockActual <= $stockMin) {
                  $colorTexto = 'text-[#F59E0B]';
                  $badgeClase = 'bg-[#F59E0B]/15 text-[#F59E0B] border-[#F59E0B]/30';
                  $estadoTexto = 'Bajo Stock';
              } else {
                  $colorTexto = 'text-[#10B981]';
                  $badgeClase = 'bg-[#10B981]/15 text-[#10B981] border-[#10B981]/30';
                  $estadoTexto = 'Óptimo';
              }
          ?>
            <tr class="hover:bg-[#1B2539]/50 transition">
              <td class="py-4 px-6 font-bold text-white font-sans text-sm">
                <?= htmlspecialchars($item['producto_nombre']) ?>
              </td>
              <td class="py-4 px-6 text-center text-slate-400">
                <?= htmlspecialchars($item['und_medida']) ?>
              </td>
              <td class="py-4 px-6 text-center text-slate-300">
                <?= number_format($stockMin, 2) ?>
              </td>
              <td class="py-4 px-6 text-center font-bold <?= $colorTexto ?>">
                <?= number_format($stockActual, 2) ?>
              </td>
              <td class="py-4 px-6 text-center">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold border <?= $badgeClase ?>">
                  <?= $estadoTexto ?>
                </span>
              </td>
              <td class="py-4 px-6 text-right">
                <button type="button" 
                        onclick='abrirModalAjuste(<?= json_encode($item) ?>)' 
                        class="text-[#F59E0B] hover:underline font-semibold cursor-pointer">
                  Editar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!--Ajustar Stock-->
<div id="modal-ajuste" class="fixed inset-0 bg-[#0A0F1D]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl font-mono">
    
    <div class="flex items-center justify-between pb-4 border-b border-[#23324A] mb-5">
      <h3 class="text-white font-bold text-sm uppercase" id="modal-titulo">Ajustar Stock</h3>
      <button type="button" onclick="cerrarModalAjuste()" class="text-slate-400 hover:text-white text-lg">✕</button>
    </div>

    <form action="" method="POST" class="space-y-4">
      <input type="hidden" name="modulo_ajuste_stock" value="1">
      <input type="hidden" name="inventario_id" id="form-inventario-id">

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Producto</label>
        <input type="text" id="form-producto-nombre" disabled 
               class="w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-2.5 text-slate-300 text-xs cursor-not-allowed">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Stock Actual</label>
          <input type="number" step="0.01" name="stock_actual" id="form-stock-actual" required 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Stock Mínimo</label>
          <input type="number" step="0.01" name="stock_minimo" id="form-stock-minimo" required 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Unidad de Medida</label>
        <input type="text" name="und_medida" id="form-und-medida" required placeholder="unidad, ml, gr..." 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div class="pt-4 flex gap-3">
        <button type="button" onclick="cerrarModalAjuste()" 
                class="flex-1 py-3 rounded-full bg-[#182338] border border-[#26354D] text-slate-300 font-bold text-xs uppercase hover:text-white transition">
          Cancelar
        </button>
        <button type="submit" 
                class="flex-1 py-3 rounded-full bg-[#10B981] text-[#0D1322] font-bold text-xs uppercase hover:brightness-110 transition shadow-lg shadow-[#10B981]/20">
          Guardar
        </button>
      </div>
    </form>

  </div>
</div>
