<?php
    use app\controllers\cocinaController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'cocinero' && $rolActual !== 'admin' && $rolActual !== 'administrador')) {
        echo "<script>window.location.replace('" . APP_URL . "login');</script>";
        exit();
    }

    $insCocina = new cocinaController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cambiar_estado'])) {
        $insCocina->cambiarEstadoPedido();
    }

    $comandas = $insCocina->obtenerPedidos();
    $notificaciones = $insCocina->obtenerNotificaciones(5);
?>

<main class="flex-1 w-full max-w-[1550px] mx-auto p-4 sm:p-6 font-mono select-none flex flex-col lg:flex-row gap-6">
  <section class="flex-1 flex flex-col">
    
    <!--Historial-->
    <div class="mb-6">
      <a href="<?php echo APP_URL; ?>historialPedido" 
         class="inline-block px-5 py-2 rounded-full font-bold text-xs bg-[#182338] text-white hover:bg-[#F59E0B] shadow-md transition">
        Ver historial
      </a>
    </div>

    <!--Pedidos-->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

      <?php if (empty($comandas)): ?>
        <div class="col-span-full py-16 text-center text-slate-500 text-sm">
          No hay pedidos activos en cocina en este momento.
        </div>
      <?php else: ?>
        <?php foreach ($comandas as $comanda): 
            $enPrep = (strtolower(trim($comanda['estado'])) === 'en preparacion');
        ?>
          <div class="bg-[#121A2A] border <?= $enPrep ? 'border-blue-500/50 shadow-blue-500/10' : 'border-[#23324A]' ?> rounded-2xl overflow-hidden shadow-xl flex flex-col justify-between h-[360px] transition-all duration-300">
            
            <!--Encabezado-->
            <div class="<?= $enPrep ? 'bg-[#172554]' : 'bg-[#1C283E]' ?> px-4 py-3 border-b border-[#23324A] flex flex-col gap-1 transition-colors">
              <div class="flex items-center justify-between">
                <span class="text-sm font-extrabold text-white">Mesa #<?= htmlspecialchars($comanda['mesa_numero']) ?></span>
                <span class="text-xs font-bold text-[#F59E0B]"><?= $comanda['hora'] ?></span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Mesero: <?= htmlspecialchars($comanda['mesero_nombre']) ?></span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $enPrep ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' ?>">
                  <?= htmlspecialchars($comanda['estado']) ?>
                </span>
              </div>
            </div>

            <!--Detalles-->
            <div class="p-4 flex-1 space-y-3 overflow-y-auto">
              <?php if (empty($comanda['items'])): ?>
                <div class="text-xs text-slate-500 italic">Sin platillos cargados</div>
              <?php else: ?>
                <?php foreach ($comanda['items'] as $item): ?>
                  <div class="border-b border-[#23324A]/40 pb-2">
                    <div class="text-sm font-bold text-white leading-tight">
                      <span class="text-[#F59E0B] font-bold"><?= $item['cantidad'] ?>x</span> 
                      <?= htmlspecialchars($item['producto']) ?>
                    </div>
                    <?php if (!empty($item['nota'])): ?>
                      <div class="text-[11px] text-slate-400 font-sans mt-0.5">
                        Nota: <?= htmlspecialchars($item['nota']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <!--BotónEstado-->
            <div class="p-4 pt-0">
              <form action="" method="POST">
                <input type="hidden" name="accion_cambiar_estado" value="1">
                <input type="hidden" name="pedido_id" value="<?= $comanda['pedido_id'] ?>">
                <input type="hidden" name="estado_actual" value="<?= htmlspecialchars($comanda['estado']) ?>">
                <input type="hidden" name="mesa_numero" value="<?= htmlspecialchars($comanda['mesa_numero']) ?>">
                <input type="hidden" name="mesero_id" value="<?= $comanda['mesero_id'] ?>">

                <?php if (!$enPrep): ?>
                  <button type="submit" 
                          class="w-full py-2.5 rounded-full font-mono text-xs font-bold text-center uppercase tracking-wide transition active:scale-95 bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-900/30 cursor-pointer">
                    En Preparación
                  </button>
                <?php else: ?>
                  <button type="submit" 
                          class="w-full py-2.5 rounded-full font-mono text-xs font-bold text-center uppercase tracking-wide transition active:scale-95 bg-greenAccent hover:brightness-110 text-[#0D1322] font-black shadow-lg shadow-emerald-900/30 cursor-pointer">
                    Listo
                  </button>
                <?php endif; ?>
              </form>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </section>

  <div class="hidden lg:block w-[1px] bg-[#23324A] self-stretch mx-2"></div>

  <!--Notificaciones-->
  <aside class="w-full lg:w-[360px] flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-extrabold text-white tracking-wide">Notificaciones</h3>
      <a href="<?php echo APP_URL; ?>notiCocinero" 
         class="px-4 py-1 rounded-full text-xs font-bold bg-[#F59E0B] text-[#0D1322] hover:brightness-110 transition shadow-sm">
        Ver todas
      </a>
    </div>

    <div class="space-y-3">
      <?php if (!empty($notificaciones)): ?>
        <?php foreach ($notificaciones as $noti): ?>
          <div onclick="leerNotificacion(<?= $noti['id'] ?>, this)" 
              title="Clic para marcar como leída"
              class="h-11 px-4 rounded-full bg-[#182338] border border-[#2E3F5C] hover:border-[#10B981] flex items-center justify-between font-mono text-xs text-slate-300 transition cursor-pointer group">
            <span class="truncate pr-3 font-sans group-hover:text-white">
              <?= htmlspecialchars($noti['mensaje']) ?>
            </span>
            <div class="flex items-center gap-2 shrink-0">
              <span class="text-[11px] text-slate-400 font-mono"><?= $noti['hora'] ?></span>
              <span class="text-[10px] text-emerald-400 opacity-0 group-hover:opacity-100 transition">✓</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="h-11 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center px-4 font-mono text-xs text-slate-400">
          No hay notificaciones pendientes.
        </div>
      <?php endif; ?>
    </div>
  </aside>

</main>