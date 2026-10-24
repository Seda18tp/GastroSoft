<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name(APP_SESSION_NAME);
    session_start();
}

// Cierre Sesion
if (isset($_GET['cerrar_sesion'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    echo "<script>window.location.replace('" . APP_URL . "login');</script>";
    exit();
}

// Comprobar sesion
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>window.location.replace('" . APP_URL . "login');</script>";
    exit();
}

$fotoSession = trim($_SESSION['foto_perfil'] ?? '');

if (str_starts_with($fotoSession, 'data:image/')) {
    $avatarImg = $fotoSession;
} elseif (!empty($fotoSession) && file_exists(dirname(__DIR__, 2) . '/img/perfil/' . $fotoSession)) {
    $avatarImg = APP_URL . 'app/img/perfil/' . $fotoSession . '?v=' . time();
} else {
    $avatarImg = APP_URL . 'app/img/default.png';
}
?>
<header class="w-full bg-[#121929] px-6 py-4 flex items-center justify-between border-b border-[#23324A] select-none relative" style="z-index: 50;">
    <div class="flex items-center space-x-3">
        <img class="w-10 h-10 rounded-full object-cover border border-[#23324A]" 
             src="<?php echo APP_URL; ?>app/img/logo.png" 
             alt="Logo GastroSoft">
        <span class="text-xl font-bold tracking-tight text-white font-mono">GastroSoft</span>
    </div>

    <div class="relative">
        <button type="button" 
                id="btn-usuario-toggle" 
                class="flex items-center gap-3 p-1 rounded-full hover:bg-[#182338] transition cursor-pointer border border-transparent focus:outline-none">
            
            <div class="text-right hidden sm:block pointer-events-none">
                <span class="block text-sm font-bold text-white leading-tight">
                    <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario Activo') ?>
                </span>
                <span class="block text-[11px] font-mono text-slate-400 uppercase tracking-wider">
                    <?= htmlspecialchars($_SESSION['rol'] ?? 'Invitado') ?>
                </span>
            </div>
            <!-- Foto perfil -->
            <div class="w-10 h-10 rounded-full overflow-hidden border border-[#23324A] bg-[#182338] pointer-events-none">
                <img src="<?= $avatarImg ?>" class="w-full h-full object-cover" alt="Foto">
            </div>
        </button>

        <!-- Desplegable -->
        <div id="dropdown-usuario-box" 
             class="absolute right-0 mt-2 w-48 bg-[#162032] border border-[#26354D] rounded-xl shadow-2xl py-2 font-mono text-sm"
             style="display: none; z-index: 1000;">
            <a href="<?php echo APP_URL; ?>miPerfil" class="block px-4 py-2.5 text-slate-300 hover:text-white hover:bg-[#1E293B] transition text-xs">
                Mi perfil
            </a>
            <div class="border-t border-[#23324A] my-1"></div>
            <a href="<?php echo APP_URL; ?>?cerrar_sesion=true" class="block px-4 py-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/20 transition font-bold text-xs">
                Cerrar sesion
            </a>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-usuario-toggle');
        const menu = document.getElementById('dropdown-usuario-box');

        if (btn && menu) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }
    });
</script>