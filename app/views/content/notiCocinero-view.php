<?php
    use app\controllers\cocinaController;

    if (!isset($_SESSION['id_usuario']) || strtolower($_SESSION['rol'] ?? '') !== 'cocinero') {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insCocina = new cocinaController();
    $notificaciones = $insCocina->notificacionesCocinero(50);
?>

<main class="flex-1 p-6 md:p-8 max-w-4xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-black text-white">Centro de Notificaciones Cocina</h2>
      <p class="text-xs text-slate-400 mt-1 font-sans">Avisos de salón, cambios y cancelaciones</p>
    </div>
    <a href="<?php echo APP_URL; ?>cocinero" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
  </div>

  <div class="space-y-3">
    <?php if (empty($notificaciones)): ?>
      <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-8 text-center text-slate-500 text-xs">
        No hay notificaciones registradas por el momento.
      </div>
    <?php else: ?>
      <?php foreach ($notificaciones as $notif): ?>
        <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl p-4 flex items-center justify-between hover:border-[#2E3F5C] transition">
          <div class="flex items-center gap-3">
            <!-- Indicador visual: Ámbar si no está leída, gris si ya fue leída -->
            <span class="w-2.5 h-2.5 rounded-full <?= ($notif['leido'] == 0) ? 'bg-[#F59E0B]' : 'bg-slate-600' ?>"></span>
            <span class="text-xs <?= ($notif['leido'] == 0) ? 'text-white font-medium' : 'text-slate-300' ?>">
              <?= htmlspecialchars($notif['mensaje']) ?>
            </span>
          </div>
          <span class="text-[11px] text-slate-500 font-sans">
            <?= date('h:i A - d/m', strtotime($notif['fecha'])) ?>
          </span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>