<!--caja-->
<main class="flex-1 w-full max-w-[1550px] mx-auto p-4 sm:p-6 font-mono select-none flex flex-col lg:flex-row gap-8">

  <!--Mesas-->
  <section class="flex-1 flex flex-col">
    
    <!--Botones Superiores-->
    <div class="flex items-center justify-end gap-3 mb-6">
      <a href="<?php echo APP_URL; ?>cerrarCaja" 
         class="px-6 py-2 rounded-full font-bold text-xs bg-[#162032] border border-[#26354D] text-white hover:bg-[#F59E0B]">
        Cerrar caja
      </a>
      <a href="<?php echo APP_URL; ?>registrarGasto/" 
         class="px-6 py-2 rounded-full font-bold text-xs bg-[#162032] border border-[#26354D] text-slate-300 text-white hover:bg-[#F59E0B]">
        Registrar gasto
      </a>
    </div>

    <!-- Lista de Mesas Precuenta-->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="#" 
           class="block bg-[#121A2A] border 'border-[#F59E0B] shadow-lg border-brandAccent rounded-2xl p-4 transition transform hover:scale-[1.01] active:scale-98">
          
          <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-white">Mesa #2</span>
            <span class="px-4 py-1 rounded-full text-xs font-bold bg-[#F59E0B] text-[#0D1322]">
              Precuenta
            </span>
          </div>

          <div class="flex items-end justify-between mt-3">
            <span class="text-xs text-slate-400">
              Juan
            </span>
            <span class="text-base font-extrabold text-white">
              23000
            </span>
          </div>

        </a>
    </div>

  </section>

  <div class="hidden lg:block w-[1px] bg-[#23324A] self-stretch"></div>

  <aside class="w-full lg:w-[460px] flex flex-col justify-between">

    <div>
      <!-- Titulo de Liquidacion-->
      <h2 class="text-xl font-bold text-white mb-6">
        Liquidación Mesa #2
      </h2>

      <!-- Formulario de Pago -->
      <form action="#" method="POST" id="form-liquidacion">
        <input type="hidden" name="mesa_id">
        <input type="hidden" name="total_cuenta" id="total_cuenta">

        <!--Metodos de Pago-->
        <div class="space-y-3 mb-6">
          <?php 
            $metodos = [
              ['id' => 'Efectivo', 'label' => 'Efectivo'],
              ['id' => 'Transferencia', 'label' => 'Transferencia'],
              ['id' => 'Tarjeta', 'label' => 'Tarjeta'],
              ['id' => 'Mixta', 'label' => 'Mixta']
            ];
            $metodoDefault = 'Efectivo';
          ?>

          <?php foreach ($metodos as $metodo): ?>
            <label class="block cursor-pointer">
              <input type="radio" 
                     name="metodo_pago" 
                     value="<?= $metodo['id'] ?>" 
                     class="hidden peer" 
                     <?= $metodo['id'] === $metodoDefault ? 'checked' : '' ?>
                     onchange="seleccionarMetodo('<?= $metodo['id'] ?>')">
              
              <div class="w-full py-3 px-5 rounded-full border border-[#23324A] bg-[#121A2A] text-slate-300 text-sm font-semibold 
                          peer-checked:border-[#10B981] peer-checked:text-[#10B981] peer-checked:bg-[#121A2A] transition duration-150">
                <?= $metodo['label'] ?>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <!--Contenedor de Valor Recibido y Cambio-->
        <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-5 mb-8">
          <div class="grid grid-cols-2 gap-4">
            
            <!--RecibidoS-->
            <div>
              <label class="block text-xs font-bold text-white mb-2">
                Dinero Recibido
              </label>
              <div class="bg-[#162032] border border-[#23324A] rounded-xl px-4 py-3">
                <input type="text" 
                       name="dinero_recibido" 
                       id="dinero_recibido" 
                       value="50,000.00" 
                       class="w-full bg-transparent text-sm font-bold text-white focus:outline-none"
                       oninput="recalcularCambio(this.value)">
              </div>
            </div>

            <!--Cambio-->
            <div>
              <span class="block text-xs font-bold text-white mb-2">
                Cambio
              </span>
              <div class="bg-[#162032] border border-[#23324A] rounded-xl px-4 py-3 flex items-center">
                <span class="text-sm font-bold text-white" id="texto-cambio">
                  26,000.00
                </span>
              </div>
            </div>

          </div>
        </div>

        <!--Registrar Pago-->
        <button type="submit" 
                class="w-full py-3.5 rounded-full bg-[#10B981] text-[#0D1322] font-mono text-sm font-extrabold uppercase tracking-wider hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#10B981]/20">
          Registrar pago
        </button>

      </form>
    </div>

  </aside>

</main>

<script>
  function limpiarNumero(valor) {
    return parseFloat(valor.toString().replace(/,/g, '').replace(/\s/g, '')) || 0;
  }

  function recalcularCambio(valorStr) {
    const total = parseFloat(document.getElementById('total_cuenta').value) || 0;
    const recibido = limpiarNumero(valorStr);
    const cambio = Math.max(0, recibido - total);

    document.getElementById('texto-cambio').innerText = cambio.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function seleccionarMetodo(metodo) {
    const total = parseFloat(document.getElementById('total_cuenta').value) || 0;
    const inputRecibido = document.getElementById('dinero_recibido');

    if (metodo !== 'Efectivo') {
      inputRecibido.value = total.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
    recalcularCambio(inputRecibido.value);
  }
</script>