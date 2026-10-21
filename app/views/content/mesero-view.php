<!--mesero-->
<main class="flex-1 w-full max-w-[1400px] mx-auto p-4 sm:p-6 font-sans select-none flex flex-col lg:flex-row gap-6">
  <section class="flex-1 flex flex-col gap-6">

    <!--Contenedor Principal-->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-2xl">
      
      <!-- Filtros-->
      <div class="flex items-center gap-2 mb-6">
        <a href="<?php echo APP_URL; ?>mesas/" class="px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#F59E0B] text-white shadow-md border border-[#2E3F5C]">
          Todas las mesas
        </a>
        <select name="zonas" class="px-2 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#182338] border border-[#2E3F5C] text-white hover:bg-[#F59E0B]">
          <option selected>Filtrar Zona</option>
          <option value="">Interior Restaurante</option>
          <option value="">Exterior Restaurante</option>
          <option value="">Caseta</option>
          <option value="">Piscina</option>  
        </select>
      </div>

      <!--Mesas-->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <a href="#" 
             class=" 'ring-4 ring-white/50 scale-[1.02]' : '' ?> h-32 rounded-2xl flex flex-col items-center justify-center p-3 font-mono transition transform active:scale-95 shadow-lg cursor-pointer bg-[#F59E0B]">
            <span class="text-3xl font-extrabold leading-none">2</span>
            <span class="text-xs font-bold mt-2 uppercase tracking-wider">Precuenta</span>
          </a>
      </div>

    </div>

    <!--Notificaciones-->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-mono text-sm font-bold text-white tracking-wide">Notificaciones</h3>
        <a href="<?php echo APP_URL; ?>notiMesero/" class="px-4 py-1 rounded-full font-mono text-[11px] font-bold bg-[#F59E0B] text-[#0D1322] hover:brightness-110 transition">
          Ver todas
        </a >
      </div>
      <div class="space-y-3">
        <?php if (!empty($notificaciones)): ?>
          <?php foreach ($notificaciones as $noti): ?>
            <div class="h-10 px-4 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center justify-between font-mono text-xs text-slate-300">
              <span><?= htmlspecialchars($noti['mensaje']) ?></span>
              <span class="text-[10px] text-slate-500"><?= $noti['hora'] ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Barras tipo placeholder del diseño -->
          <div class="h-10 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center px-4 font-mono text-xs text-slate-400">
            Mesa #2 solicitó la precuenta
          </div>
          <div class="h-10 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center px-4 font-mono text-xs text-slate-400">
            Cocina: Hamburguesa Mesa #2 lista para servir
          </div>
          <div class="h-10 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center px-4 font-mono text-xs text-slate-400">
            Stock de Cerveza Club Colombia por agotarse
          </div>
        <?php endif; ?>
      </div>
    </div>

  </section>

  <aside class="w-full lg:w-[420px] bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
    
    <div>
      <!-- Detalles de Mesa Seleccionada -->
      <div class="flex items-center justify-between pb-4 border-b border-[#23324A]">
        <div>
          <h2 class="font-mono text-lg font-black text-white">
            Mesa #<?= $mesa_activa['numero'] ?? '2' ?>
          </h2>
          <span class="font-mono text-xs text-slate-400">
            Pedido #<?= $mesa_activa['pedido_id'] ?? '0104' ?>
          </span>
        </div>

        <span class="px-4 py-1 rounded-full font-mono text-xs font-bold bg-[#F59E0B] border border-[#EF4444] text-gray-300">
          <?= $mesa_activa['estado'] ?? 'Precuenta' ?>
        </span>
      </div>

      <!-- Ítems del Pedido -->
      <div class="mt-6 space-y-4">
        <?php if (!empty($mesa_activa['items'])): ?>
          <?php foreach ($mesa_activa['items'] as $item): ?>
            <div class="flex items-start justify-between font-mono">
              <div>
                <span class="font-bold text-sm text-white"><?= $item['cantidad'] ?>X <?= htmlspecialchars($item['nombre']) ?></span>
                <?php if (!empty($item['nota'])): ?>
                  <p class="text-xs text-slate-400 mt-0.5 font-sans">Nota: <?= htmlspecialchars($item['nota']) ?></p>
                <?php endif; ?>
              </div>
              <span class="font-bold text-sm text-white">
                <?= number_format($item['precio'], 2, ',', '.') ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="font-mono text-xs text-slate-500 py-6 text-center">No hay platos añadidos en esta mesa.</p>
        <?php endif; ?>
      </div>
    </div>

    <!--Total-->
    <div class="pt-6 border-t border-[#23324A] mt-8">
      
      <!--Subtotal-->
      <div class="flex items-center justify-between mb-6 font-mono">
        <span class="text-xs text-slate-400 uppercase tracking-wider">Subtotal:</span>
        <span class="text-xl font-black text-white">
          <?= number_format($mesa_activa['subtotal'] ?? 24000, 2, ',', '.') ?>
        </span>
      </div>

      <!--Botones-->
      <div class="grid grid-cols-2 gap-3 mb-3">
        <a href="index.php?url=pedir-cuenta&mesa=<?= $mesa_activa['numero'] ?? 2 ?>" 
           class="py-3 px-3 rounded-full font-mono text-xs font-bold text-center border border-[#F59E0B] text-[#F59E0B] bg-[#182338] hover:bg-[#F59E0B]/10 active:scale-95 transition">
          Pedir cuenta
        </a>

        <a href="index.php?url=carta&mesa=<?= $mesa_activa['numero'] ?? 2 ?>" 
           class="py-3 px-3 rounded-full font-mono text-xs font-bold text-center bg-[#10B981] hover:brightness-110 text-[#0D1322] active:scale-95 transition shadow-md shadow-[#10B981]/20">
          + Añadir platos
        </a>
      </div>

      <!-- Botón Principal: Enviar a Cocina -->
      <a href="index.php?url=enviar-cocina&mesa=<?= $mesa_activa['numero'] ?? 2 ?>" 
         class="block w-full py-3.5 rounded-2xl font-mono text-xs font-extrabold text-center uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] hover:brightness-110 active:scale-98 transition shadow-lg shadow-[#F59E0B]/20">
        Enviar pedido a cocina
      </a>

    </div>

  </aside>

</main>