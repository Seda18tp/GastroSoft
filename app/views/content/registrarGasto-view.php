<?php
    use app\controllers\cajaController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador' && $rolActual !== 'cajero')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insCaja = new cajaController();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_registrar_gasto'])) {
        $alerta = $insCaja->registrarGastoC();
    }
?>

<main class="flex-1 p-6 md:p-8 max-w-xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Registrar Gasto de Caja</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Compras de urgencia</p>
    </div>
    <a href="<?php echo APP_URL; ?>caja" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
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
      <input type="hidden" name="modulo_registrar_gasto" value="1">

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Descripción</label>
        <input type="text" 
               name="descripcion" 
               required 
               placeholder="Ej: Compra de hielo, limones de urgencia..." 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Monto Gastado</label>
        <input type="number" 
               step="any" 
               name="monto" 
               required 
               placeholder="0.00" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div class="pt-4">
        <button type="submit" 
                class="w-full py-3.5 rounded-full bg-[#EF4444] text-white font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#EF4444]/20 cursor-pointer">
          Guardar Gasto
        </button>
      </div>
    </form>
  </div>
</main>