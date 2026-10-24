<?php
    use app\controllers\userController;

    if (!isset($_SESSION['id_usuario'])) {
        echo "<script>window.location.replace('" . APP_URL . "login');</script>";
        exit();
    }

    $insUsuario = new userController();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_perfil'])) {
        $alerta = $insUsuario->actualizarPerfil();
    }

    $rol = strtolower($_SESSION['rol'] ?? '');
    $urlRetorno = match ($rol) {
        'cocinero'               => APP_URL . 'cocinero',
        'administrador', 'admin' => APP_URL . 'admin',
        'cajero'                 => APP_URL . 'caja',
        default                  => APP_URL . 'mesero'
    };

    $fotoSession = trim($_SESSION['foto_perfil'] ?? '');
    $rutaFisica  = dirname(__DIR__, 2) . '/img/perfil/' . $fotoSession;

    if (!empty($fotoSession) && file_exists($rutaFisica)) {
        $avatarImg = APP_URL . 'app/img/perfil/' . $fotoSession . '?v=' . time();
    } else {
        $avatarImg = APP_URL . 'app/img/default.png';
    }
?>

<main class="flex-1 p-6 md:p-8 max-w-3xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-6">
    <a href="<?= $urlRetorno ?>" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white hover:border-[#F59E0B] transition shadow-md">
      Volver
    </a>
  </div>

  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-8 shadow-2xl">
    <form action="" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-4">
      <input type="hidden" name="modulo_perfil" value="1">

      <div class="flex items-center space-x-5 pb-6 border-b border-[#23324A] mb-6">
        <input type="file" 
               name="foto_perfil" 
               id="input-archivo-foto" 
               accept="image/jpeg,image/png,image/webp,image/jpg" 
               class="hidden" 
               onchange="previewImg(this)">

        <div class="relative group cursor-pointer" onclick="document.getElementById('input-archivo-foto').click();" title="Haz clic para cambiar tu foto">
          <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#2E3F5C] group-hover:border-[#F59E0B] transition bg-[#182338]">
            <img id="img-vista-previa" src="<?= $avatarImg ?>" class="w-full h-full object-cover" alt="Perfil">
          </div>
        </div>

        <div>
          <h2 class="text-xl font-black text-white"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario Activo') ?></h2>
          <span class="text-xs font-bold text-[#F59E0B] uppercase tracking-wider"><?= htmlspecialchars($_SESSION['rol'] ?? 'Rol') ?></span>
          <p class="text-[10px] text-slate-400 font-sans mt-0.5">Haz clic sobre el avatar para elegir una nueva foto</p>
        </div>
      </div>

      <?php if (isset($alerta)): ?>
        <div class="mb-5 p-3.5 rounded-2xl <?= ($alerta['tipo'] === 'exito') ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : 'bg-rose-500/15 border border-rose-500/30 text-rose-300' ?> text-xs flex items-center gap-2">
          <span><?= ($alerta['tipo'] === 'exito') ? '✓' : '⚠️' ?></span>
          <span><?= htmlspecialchars($alerta['texto']) ?></span>
        </div>
      <?php endif; ?>

      <div>
        <label for="nombre_completo" class="block text-xs font-bold text-slate-300 mb-2 uppercase">Nombre Completo</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required
               value="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-[#F59E0B] transition">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Correo Electrónico</label>
        <input type="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" disabled 
               class="w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-slate-400 text-xs cursor-not-allowed">
      </div>

      <div class="pt-4 border-t border-[#23324A]">
        <h4 class="text-xs font-bold text-white uppercase mb-1">Cambiar Contraseña</h4>
        <p class="text-[11px] text-slate-400 font-sans mb-3">Deja estos campos vacíos si deseas conservar la contraseña actual.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <input type="password" name="nueva_clave" placeholder="Nueva Contraseña" autocomplete="new-password"
                 class="bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] transition">
          <input type="password" name="confirmar_clave" placeholder="Confirmar Contraseña" autocomplete="new-password"
                 class="bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] transition">
        </div>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full py-3.5 rounded-full bg-[#10B981] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#10B981]/20 cursor-pointer">
          Guardar Cambios
        </button>
      </div>
    </form>
  </div>
</main>
<script>
      function previewImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-vista-previa').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>