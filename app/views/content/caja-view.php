<?php
    use app\controllers\cajaController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador' && $rolActual !== 'cajero')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insCaja = new cajaController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_liquidar_pago'])) {
        $insCaja->liquidarMesa();
    }

    $mesasPrecuenta = $insCaja->listarMesasLiquidacion();
?>

<main class="flex-1 w-full max-w-[1550px] mx-auto p-4 sm:p-6 font-mono select-none flex flex-col lg:flex-row gap-8">

  <!--MesasPrecuenta-->
  <section class="flex-1 flex flex-col">
    
    <!--Botones-->
    <div class="flex items-center justify-end gap-3 mb-6">
      <a href="<?php echo APP_URL; ?>cerrarCaja" 
         class="px-6 py-2 rounded-full font-bold text-xs bg-[#162032] border border-[#26354D] text-white hover:bg-[#F59E0B] hover:text-[#0D1322] transition">
        Cerrar caja
      </a>
      <a href="<?php echo APP_URL; ?>registrarGasto" 
         class="px-6 py-2 rounded-full font-bold text-xs bg-[#162032] border border-[#26354D] text-slate-300 hover:text-white hover:border-[#F59E0B] transition">
        Registrar gasto
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php if (empty($mesasPrecuenta)): ?>
        <div class="col-span-full py-24 text-center text-slate-500 text-xs">
          No hay mesas pendientes de pago en este momento.
        </div>
      <?php else: ?>
        <?php foreach ($mesasPrecuenta as $m): 
            $esPrecuenta = strtolower(trim($m['mesa_estado'] ?? '')) === 'precuenta';
        ?>
          <div onclick='seleccionarMesaCobro(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, this)' 
               class="tarjeta-mesa cursor-pointer block bg-[#121A2A] border <?= $esPrecuenta ? 'border-[#F59E0B]/60 shadow-lg shadow-[#F59E0B]/5' : 'border-[#23324A]' ?> rounded-2xl p-4 transition transform hover:scale-[1.01] active:scale-98">
            
            <div class="flex items-center justify-between">
              <span class="text-sm font-bold text-white">Mesa #<?= htmlspecialchars($m['numero_mesa']) ?></span>
              <span class="px-3 py-1 rounded-full text-[11px] font-bold <?= $esPrecuenta ? 'bg-[#F59E0B] text-[#0D1322]' : 'bg-[#182338] text-slate-400 border border-[#26354D]' ?>">
                <?= htmlspecialchars(ucfirst($m['mesa_estado'])) ?>
              </span>
            </div>

            <div class="flex items-end justify-between mt-3">
              <span class="text-xs text-slate-400 font-sans">
                <?= !empty($m['mesero_nombre']) ? htmlspecialchars($m['mesero_nombre']) : 'Sin mesero' ?>
              </span>
              <span class="text-base font-extrabold text-white">
                $ <?= number_format($m['total'], 0, ',', '.') ?>
              </span>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </section>

  <div class="hidden lg:block w-[1px] bg-[#23324A] self-stretch"></div>

  <!--PanelLiquidación-->
  <aside class="w-full lg:w-[460px] flex flex-col justify-start">

    <div id="panel-vacio" class="h-full flex flex-col items-center justify-center py-20 px-6 text-center border border-dashed border-[#23324A] rounded-3xl bg-[#121A2A]/40">
      <div class="w-16 h-16 rounded-full bg-[#182338] flex items-center justify-center text-slate-500 mb-4 text-xl font-bold">
        $
      </div>
      <h3 class="text-white font-bold text-base mb-1">Sin mesa seleccionada</h3>
      <p class="text-xs text-slate-400 font-sans max-w-xs">
        Selecciona una mesa en precuenta de la lista para ver su liquidación y registrar el pago.
      </p>
    </div>

    <div id="panel-cobro" class="hidden flex flex-col">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl font-bold text-white" id="titulo-liquidacion">
            Liquidación Mesa #--
          </h2>
          <span class="text-xs text-slate-400 font-sans" id="subtitulo-mesero">
            Mesero: --
          </span>
        </div>
        <span class="text-lg font-black text-[#F59E0B]" id="badge-total-cuenta">
          $ 0
        </span>
      </div>

      <form action="" method="POST" id="form-liquidacion">
        <input type="hidden" name="modulo_liquidar_pago" value="1">
        <input type="hidden" name="mesa_id" id="form_mesa_id" value="">
        <input type="hidden" name="pedido_id" id="form_pedido_id" value="">
        <input type="hidden" name="total_cuenta" id="total_cuenta" value="0">
        <input type="hidden" name="cambio_calculado" id="cambio_calculado" value="0.00">

        <!--MetodosPago-->
        <div class="space-y-3 mb-6">
          <?php 
            $metodos = [
              ['id' => 'Efectivo', 'label' => 'Efectivo'],
              ['id' => 'Transferencia', 'label' => 'Transferencia'],
              ['id' => 'Tarjeta', 'label' => 'Tarjeta']
            ];
          ?>

          <?php foreach ($metodos as $index => $metodo): ?>
            <label class="block cursor-pointer">
              <input type="radio" 
                     name="metodo_pago" 
                     value="<?= $metodo['id'] ?>" 
                     class="hidden peer" 
                     <?= $index === 0 ? 'checked' : '' ?>
                     onchange="seleccionarMetodo('<?= $metodo['id'] ?>')">
              
              <div class="w-full py-3 px-5 rounded-full border border-[#23324A] bg-[#121A2A] text-slate-300 text-sm font-semibold 
                          peer-checked:border-[#10B981] peer-checked:text-[#10B981] peer-checked:bg-[#121A2A] transition duration-150">
                <?= $metodo['label'] ?>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Valor Recibido y Cambio -->
        <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 mb-8">
          <div class="grid grid-cols-2 gap-4">
            
            <div>
              <label class="block text-xs font-bold text-white mb-2">
                Dinero Recibido
              </label>
              <div class="bg-[#162032] border border-[#23324A] rounded-xl px-4 py-3">
                <input type="text" 
                       name="dinero_recibido" 
                       id="dinero_recibido" 
                       value="0.00" 
                       class="w-full bg-transparent text-sm font-bold text-white focus:outline-none"
                       oninput="recalcularCambio(this.value)">
              </div>
            </div>

            <div>
              <span class="block text-xs font-bold text-white mb-2">
                Cambio
              </span>
              <div class="bg-[#162032] border border-[#23324A] rounded-xl px-4 py-3 flex items-center">
                <span class="text-sm font-bold text-white" id="texto-cambio">
                  0.00
                </span>
              </div>
            </div>

          </div>
        </div>

        <!--cobrary Liberar Mesa -->
        <button type="submit" 
                class="w-full py-3.5 rounded-full bg-[#10B981] text-[#0D1322] font-mono text-sm font-extrabold uppercase tracking-wider hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#10B981]/20 cursor-pointer">
          Registrar pago
        </button>

      </form>
    </div>

  </aside>

</main>
<?php require_once "./app/views/inc/script.php"; ?>