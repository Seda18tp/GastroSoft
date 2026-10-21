<!--app/views/inc/navbar.php-->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: APP_URL.'login/''; ");
    exit();
}
if (isset($_GET['cerrar_sesion'])) {
    session_unset();
    session_destroy();
    echo "<script>window.location.href = '" . APP_URL . "login/';</script>";
    exit();
}
?>

<header class="w-full bg-[#121929] px-8 py-4 flex items-center justify-between relative border-b border-cardBorder/40 select-none">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
        <img class="w-10 h-10 rounded-full object-cover border border-cardBorder" src="<?php echo APP_URL; ?>app/img/logo.png" alt="Logo GastroSoft">
        <span class="text-xl font-bold tracking-tight text-white font-mono">GastroSoft</span>
    </div>

    <!-- Usuario y Menú Desplegable -->
    <div class="relative">
        <button id="user-menu-btn" class="flex items-center ring-2 ring-transparent hover:ring-brandAccent rounded-full transition">
            <div class="text-right mx-4 hidden sm:block">
                <span class="block text-sm font-bold tracking-tight text-white leading-tight">
                    <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario Activo') ?>
                </span>
                <span class="block text-[11px] font-mono text-slate-400 uppercase tracking-wider">
                    <?= htmlspecialchars($_SESSION['rol'] ?? 'Invitado') ?>
                </span>
            </div>
            <img class="w-10 h-10 rounded-full object-cover border border-cardBorder" src="<?php echo APP_URL; ?>app/img/default.png" alt="Foto de perfil">
        </button>

        <!-- Dropdown -->
        <div id="user-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-cardBg border border-cardBorder rounded-xl shadow-2xl py-2 text-sm z-50 transition-all duration-150">
            <a href="<?php echo APP_URL; ?>miPerfil/" class="flex items-center px-4 py-2 text-slate-300 hover:text-white hover:bg-[#253047] transition">
                Mi perfil
            </a>
            <div class="border-t border-cardBorder my-1"></div>
            <a href="<?php echo APP_URL; ?>login/" class="flex items-center px-4 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-950/20 transition font-medium">
                Cerrar sesión
            </a>
        </div>
    </div>
</header>