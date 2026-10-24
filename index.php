<?php
    ob_start();     

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/app/views/inc/session_start.php.php";

    if (isset($_GET['cerrar_sesion'])) {
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

    if (isset($_GET['views'])) {
        $url = explode("/", $_GET['views']);
    } else {
        $url = ["login"];
    }
    ob_end_flush();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once "./app/views/inc/head.php"; ?>
</head>
<body class="bg-[#0D1322] text-slate-100 min-h-screen flex flex-col font-sans">
    <?php
        use app\controllers\viewsController;

        $viewsController = new viewsController();
        $vista = $viewsController->obtenerVistas($url[0]);

        if ($vista == "login" || $vista == "404") {
            require_once "./app/views/content/" . $vista . "-view.php";
        } else {
    ?>
    <main class="page-container flex-1 flex flex-col">

        <?php require_once "./app/views/inc/navbar.php"; ?>     

        <section class="full-width pageContent scroll flex-1" id="pageContent">
            <?php 
                require_once $vista;
            ?>
        </section>
        <?php require_once "./app/views/inc/footer.php"; ?>
        <?php require_once "./app/views/inc/script.php"; ?>

    </main>
    <?php
        }
    ?>
</body>
</html>