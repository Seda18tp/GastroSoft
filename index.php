<?php
ob_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/app/views/inc/session_start.php";

if (isset($_GET['cerrar_sesion']) && $_GET['cerrar_sesion'] === 'true') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . APP_URL . "login");
    exit();
}


if (isset($_GET['views']) && !empty($_GET['views'])) {
    $ruta = $_GET['views'];
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $uri = trim($uri, '/');
    $uri = preg_replace('#^gastrosoft/?#i', '', $uri);
    $ruta = !empty($uri) ? $uri : "login";
}

$url = explode("/", $ruta);

use app\controllers\viewsController;

$viewsController = new viewsController();
$vista = $viewsController->obtenerVistas($url[0]);

$vistasPublicas = ['login', '404'];
if (!isset($_SESSION['id_usuario']) && !in_array($vista, $vistasPublicas)) {
    header("Location: " . APP_URL . "login");
    exit();
}

if (isset($_SESSION['id_usuario']) && $vista === 'login') {
    $rol = strtolower($_SESSION['rol'] ?? '');
    $destino = match ($rol) {
        'administrador', 'admin' => 'admin',
        'cocinero'               => 'cocinero',
        'cajero'                 => 'caja',
        default                  => 'mesero'
    };
    header("Location: " . APP_URL . $destino);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . "/app/views/inc/head.php"; ?>
</head>
<body class="bg-[#0D1322] text-slate-100 min-h-screen flex flex-col font-sans">
    <?php
        if ($vista == "login" || $vista == "404") {
            require_once __DIR__ . "/app/views/content/" . $vista . "-view.php";
        } else {
    ?>
    <main class="page-container flex-1 flex flex-col">

        <?php require_once __DIR__ . "/app/views/inc/navbar.php"; ?>     

        <section class="full-width pageContent scroll flex-1" id="pageContent">
            <?php 
                require_once $vista;
            ?>
        </section>
        <?php require_once __DIR__ . "/app/views/inc/footer.php"; ?>
        <?php require_once __DIR__ . "/app/views/inc/script.php"; ?>

    </main>
    <?php
        }
    ?>
</body>
</html>
<?php
ob_end_flush();
?>