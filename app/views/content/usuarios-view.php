<?php
use app\controllers\adminController;

$rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
    header("Location: " . APP_URL . "login");
    exit();
}

$insAdmin = new adminController();
$alerta = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['modulo_guardar_usuario'])) {
        $alerta = $insAdmin->guardarUsuario();
    } elseif (isset($_POST['modulo_cambiar_estado_usuario'])) {
        $alerta = $insAdmin->alternarEstadoUsuario();
    }
}

$usuarios = $insAdmin->obtenerUsuarios();
$roles = $insAdmin->obtenerRoles();
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
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
      <a href="<?php echo APP_URL; ?>inventario" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Inventario
      </a>
      <a href="<?php echo APP_URL; ?>usuarios" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition">
        Usuarios
      </a>
    </div>

    <button type="button" onclick="abrirModalUsuario(null)" class="px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#10B981] text-[#0D1322] hover:brightness-110 shadow-lg shadow-[#10B981]/20 transition cursor-pointer">
      + Nuevo Usuario
    </button>
  </div>

  <?php if (isset($alerta)): ?>
    <div class="mb-6 p-4 rounded-2xl <?= ($alerta['tipo'] === 'exito') ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : 'bg-rose-500/15 border border-rose-500/30 text-rose-300' ?> text-xs font-mono flex items-center gap-2">
      <span><?= ($alerta['tipo'] === 'exito') ? '✓' : '⚠️' ?></span>
      <span><?= htmlspecialchars($alerta['texto']) ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-[#121929] border border-[#23324A] rounded-2xl shadow-xl overflow-hidden font-mono">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-[#23324A] text-slate-400 font-bold uppercase bg-[#101726]">
          <th class="py-4 px-6">Nombre Completo</th>
          <th class="py-4 px-6">Email</th>
          <th class="py-4 px-6 text-center">Rol</th>
          <th class="py-4 px-6 text-center">Estado</th>
          <th class="py-4 px-6 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#23324A]/60 text-slate-200">
        <?php foreach ($usuarios as $u): 
            $activo = (strtolower(trim($u['estado'])) === 'activo');
        ?>
          <tr class="hover:bg-[#1B2539]/50 transition">
            <td class="py-4 px-6 font-bold text-white font-sans text-sm">
              <?= htmlspecialchars($u['nombre_completo']) ?>
            </td>
            <td class="py-4 px-6 text-slate-300">
              <?= htmlspecialchars($u['email']) ?>
            </td>
            <td class="py-4 px-6 text-center">
              <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#182338] text-[#F59E0B] border border-[#26354D]">
                <?= htmlspecialchars($u['rol_nombre']) ?>
              </span>
            </td>
            <td class="py-4 px-6 text-center">
              <span class="px-3 py-1 rounded-full text-[10px] font-bold border <?= $activo ? 'bg-[#10B981]/15 text-[#10B981] border-[#10B981]/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30' ?>">
                <?= $activo ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <td class="py-4 px-6 text-right">
              <div class="flex items-center justify-end gap-3">
                <button type="button" 
                        onclick='abrirModalUsuario(<?= json_encode($u) ?>)' 
                        class="text-[#F59E0B] hover:underline font-semibold cursor-pointer">
                  Editar
                </button>
                <form action="" method="POST" class="inline m-0 p-0">
                  <input type="hidden" name="modulo_cambiar_estado_usuario" value="1">
                  <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="estado_actual" value="<?= htmlspecialchars($u['estado']) ?>">
                  <button type="submit" 
                          onclick="return confirm('¿Deseas cambiar el estado de este usuario?')"
                          class="font-semibold cursor-pointer <?= $activo ? 'text-rose-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
                    <?= $activo ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

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
        <input type="text" name="nombre_completo" id="form-user-nombre" required placeholder="Ej: Camilo Sanchez" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Correo Electrónico</label>
        <input type="email" name="email" id="form-user-email" required placeholder="correo@gastrosoft.com" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Rol</label>
        <select name="rol_id" id="form-user-rol" required class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
          <option value="">Selecciona rol...</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Estado</label>
        <select name="estado" id="form-user-estado" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase">Contraseña</label>
        <input type="password" name="password" id="form-user-pass" placeholder="••••••••••••" 
               class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        <p id="hint-password" class="text-[10px] text-slate-400 mt-1 hidden font-sans">Dejar en blanco para mantener la contraseña actual al editar.</p>
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