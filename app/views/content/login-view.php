<?php
  use app\controllers\loginController;

  $alerta = null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form'])) {
      $insLogin = new loginController();
      $respuesta = $insLogin->iniciarSesion();

      if (isset($respuesta['tipo']) && $respuesta['tipo'] === 'redireccionar') {
          echo "<script>window.location.href = '" . $respuesta['url'] . "';</script>";
          exit();
      } else {
          $alerta = $respuesta;
      }
  }
?>

<div class="min-h-screen flex items-center justify-center w-full bg-[#0D1322] px-4 select-none">
  <div class="bg-[#121A2A] border border-[#23324A] shadow-2xl rounded-3xl px-8 py-8 max-w-md w-full">
    
    <!-- Marca -->
    <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-[#23324A]">
      <img class="w-14 h-14 rounded-full object-cover border border-[#2E3F5C] shadow-md" 
           src="<?php echo APP_URL; ?>app/img/logo.png" alt="Logo GastroSoft">
      <div>
        <h1 class="text-2xl font-black text-white tracking-wide font-mono">GastroSoft</h1>
        <p class="text-xs text-slate-400 font-mono">Punto de venta</p>
      </div>
    </div>

    <!-- Alerta en caso de credenciales incorrectas -->
    <?php if (isset($alerta['texto'])): ?>
      <div class="mb-4 p-3 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs font-mono flex items-center gap-2">
        <span>⚠️</span>
        <span><?= htmlspecialchars($alerta['texto']) ?></span>
      </div>
    <?php endif; ?>

    <!-- Formulario de Acceso -->
    <form action="" method="POST" autocomplete="off" class="space-y-4 font-mono">
      <input type="hidden" name="login_form" value="1">

      <div>
        <label for="usuario_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
          Correo Electrónico
        </label>
        <input type="email" id="usuario_email" name="usuario_email" required 
              autocomplete="username" placeholder="cocina@gastrosoft.com"
              class="w-full px-4 py-3 bg-[#182338] border border-[#2E3F5C] rounded-2xl text-white text-xs focus:outline-none focus:border-[#F59E0B] transition">
      </div>

      <div>
        <label for="usuario_clave" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
          Contraseña
        </label>
        <input type="password" id="usuario_clave" name="usuario_clave" required 
              autocomplete="current-password" placeholder="••••••••••••"
              class="w-full px-4 py-3 bg-[#182338] border border-[#2E3F5C] rounded-2xl text-white text-xs focus:outline-none focus:border-[#F59E0B] transition">
      </div>

      <div class="pt-2">
        <button type="submit" 
                class="w-full py-3.5 px-4 rounded-full font-bold text-xs uppercase tracking-wider text-[#0D1322] bg-[#F59E0B] hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#F59E0B]/20 cursor-pointer">
          Iniciar Sesión
        </button>
      </div>
    </form>

    <div class="mt-6 pt-4 border-t border-[#23324A] text-center">
      <span class="text-[11px] text-slate-500 font-mono">GastroSoft POS &copy; 2026</span>
    </div>

  </div>
</div>