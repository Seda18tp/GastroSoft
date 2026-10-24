<?php
    $baseApp = dirname(__DIR__, 2);
    require_once $baseApp . "/models/mainModel.php";
    require_once $baseApp . "/models/adminModel.php";
    require_once $baseApp . "/controllers/adminController.php";

    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        echo "<script>window.location.replace('" . APP_URL . "login');</script>";
        exit();
    }

    $insAdmin = new adminController();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['modulo_guardar_usuario'])) {
            $alerta = $insAdmin->guardarUsuario();
        } elseif (isset($_POST['modulo_estado_usuario'])) {
            $alerta = $insAdmin->alternarEstadoUsuario();
        }
    }

    $usuarios = $insAdmin->obtenerUsuarios();
    $roles    = $insAdmin->obtenerRoles();
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
  
  <!--Navegacion-->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div class="flex flex-wrap items-center gap-2">
      <a href="<?php echo APP_URL; ?>admin" class="px-5 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Volver
      </a>
      <a href="<?php echo APP_URL; ?>estadisticas" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Estadisticas
      </a>
      <a href="<?php echo APP_URL; ?>carta" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Carta
      </a>
      <a href="<?php echo APP_URL; ?>inventario" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Inventario
      </a>
      <a href="<?php echo APP_URL; ?>usuarios" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition">
        Usuarios
      </a>
    </div>
    
    <button type="button" 
            onclick="abrirModalUsuario(null)"
            class="px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#10B981] text-[#0D1322] hover:brightness-110 shadow-lg shadow-[#10B981]/20 transition cursor-pointer">
      + Nuevo Usuario
    </button>
  </div>

  <!--ContenedorAlerta -->
  <?php if (isset($alerta) && is_array($alerta)): ?>
    <div class="mb-5 p-3.5 rounded-2xl <?= ($alerta['tipo'] === 'exito') ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : 'bg-rose-500/15 border border-rose-500/30 text-rose-300' ?> text-xs flex items-center gap-2 font-mono">
      <span><?= ($alerta['tipo'] === 'exito') ? '✓' : '⚠️' ?></span>
      <span><?= htmlspecialchars($alerta['texto']) ?></span>
    </div>
  <?php endif; ?>

  <!--TablaUsuarios-->
  <div class="bg-[#121929] border border-[#23324A] rounded-2xl shadow-xl overflow-hidden font-mono">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-[#23324A] text-slate-400 font-bold uppercase bg-[#101726]">
          <th class="py-4 px-6">Nombre Completo</th>
          <th class="py-4 px-6">Correo</th>
          <th class="py-4 px-6 text-center">Rol</th>
          <th class="py-4 px-6 text-center">Estado</th>
          <th class="py-4 px-6 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#23324A]/60 text-slate-200">
        <?php if (empty($usuarios)): ?>
          <tr>
            <td colspan="5" class="py-12 text-center text-slate-500 text-xs">
              No hay usuarios registrados.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($usuarios as $u): 
              $estadoVal = strtolower(trim($u['estado'] ?? ''));
              $activo = ($estadoVal === 'activo' || $estadoVal === '1');
              $esPropioUsuario = ((int)$u['id'] === (int)$_SESSION['id_usuario']);
          ?>
            <tr class="hover:bg-[#1B2539]/50 transition">
              <td class="py-4 px-6 font-bold text-white font-sans text-sm">
                <?= htmlspecialchars($u['nombre_completo'] ?? $u['nombre'] ?? '') ?>
                <?php if ($esPropioUsuario): ?>
                  <span class="ml-2 text-[10px] font-mono font-bold text-[#F59E0B]">(Tú)</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6 text-slate-400 font-sans">
                <?= htmlspecialchars($u['email']) ?>
              </td>
              <td class="py-4 px-6 text-center font-bold text-[#F59E0B]">
                <?= htmlspecialchars($u['rol_nombre'] ?? $u['rol'] ?? '') ?>
              </td>
              <td class="py-4 px-6 text-center">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold border <?= $activo ? 'bg-[#10B981]/15 text-[#10B981] border-[#10B981]/30' : 'bg-rose-500/15 text-rose-400 border-rose-500/30' ?>">
                  <?= $activo ? 'Activo' : 'Inactivo' ?>
                </span>
              </td>
              <td class="py-4 px-6 text-right space-x-3">
                <button type="button" 
                        onclick='abrirModalUsuario(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                        class="text-slate-300 hover:text-white font-semibold cursor-pointer">
                  Editar
                </button>

                <?php if (!$esPropioUsuario): ?>
                  <form action="" method="POST" class="inline m-0 p-0">
                    <input type="hidden" name="modulo_estado_usuario" value="1">
                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="estado_actual" value="<?= htmlspecialchars($u['estado']) ?>">
                    <button type="submit" class="font-semibold transition cursor-pointer <?= $activo ? 'text-[#EF4444] hover:underline' : 'text-[#10B981] hover:underline' ?>">
                      <?= $activo ? 'Desactivar' : 'Activar' ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-slate-600 text-[11px]">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<!--Crear/EditarUsuario -->
<div id="modal-usuario" class="fixed inset-0 bg-[#0A0F1D]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl font-mono">
    
    <div class="flex items-center justify-between pb-4 border-b border-[#23324A] mb-5">
      <h3 class="text-white font-bold text-sm uppercase" id="modal-titulo-usuario">Nuevo Usuario</h3>
      <button type="button" onclick="cerrarModalUsuario()" class="text-slate-400 hover:text-white text-lg cursor-pointer">✕</button>
    </div>

    <form action="" method="POST" autocomplete="off" class="space-y-4">
      <input type="hidden" name="modulo_guardar_usuario" value="1">
      <input type="hidden" name="usuario_id" id="form-user-id" value="0">

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Nombre Completo</label>
        <input type="text" name="nombre_completo" id="form-user-nombre" required placeholder="Ej: Carlos Mesero"
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Correo Electrónico</label>
        <input type="email" name="email" id="form-user-email" required placeholder="correo@gastrosoft.com"
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Rol</label>
          <select name="rol_id" id="form-user-rol" required
                  class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="">Seleccione...</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?? $r['rol_id'] ?>"><?= htmlspecialchars($r['nombre'] ?? $r['rol_nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Estado</label>
          <select name="estado" id="form-user-estado"
                  class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase" id="label-password">Contraseña</label>
        <input type="password" name="password" id="form-user-pass" autocomplete="new-password" placeholder="••••••••"
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        <p class="text-[10px] text-slate-400 mt-1 font-sans hidden" id="hint-password">Dejar en blanco si no deseas cambiar la contraseña actual.</p>
      </div>

      <div class="pt-4 flex gap-3">
        <button type="button" onclick="cerrarModalUsuario()" 
                class="flex-1 py-3 rounded-full bg-[#182338] border border-[#26354D] text-slate-300 font-bold text-xs uppercase hover:text-white transition cursor-pointer">
          Cancelar
        </button>
        <button type="submit" 
                class="flex-1 py-3 rounded-full bg-[#10B981] text-[#0D1322] font-bold text-xs uppercase hover:brightness-110 transition shadow-lg shadow-[#10B981]/20 cursor-pointer">
          Guardar
        </button>
      </div>
    </form>

  </div>
</div>
<?php require_once "./app/views/inc/script.php"; ?>