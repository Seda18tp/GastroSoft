<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);

$raiz = dirname(__DIR__, 2);
require_once $raiz . "/config/conexion.php";
require_once $raiz . "/config/app.php";
require_once $raiz . "/autoload.php";

try {
    $db = new \app\models\mainModel();

    $sql = "SELECT 
                (SELECT COUNT(*) FROM pedido WHERE LOWER(estado::text) NOT IN ('pagado', 'cancelado')) AS pedidos_activos,
                (SELECT STRING_AGG(id || ':' || estado, '|' ORDER BY id) FROM pedido WHERE LOWER(estado::text) NOT IN ('pagado', 'cancelado')) AS estados_pedidos,
                (SELECT STRING_AGG(id || ':' || estado, '|' ORDER BY id) FROM mesa) AS estados_mesas,
                (SELECT MAX(id) FROM notificacion) AS ultima_notif,
                (SELECT COUNT(*) FROM notificacion) AS total_notif,
                (SELECT MAX(id) FROM detalle_pedido) AS ultimo_detalle,
                (SELECT COUNT(*) FROM detalle_pedido) AS total_detalles,
                (SELECT SUM(stock_actual::numeric) FROM inventario) AS balance_stock,
                (SELECT COUNT(*) FROM producto) AS total_productos";

    $stmt = $db->ejecutarConsulta($sql);
    $data = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : [];

    ob_clean();
    echo json_encode([
        'exito'   => true,
        'version' => md5(json_encode($data))
    ]);
} catch (\Throwable $e) {
    ob_clean();
    echo json_encode([
        'exito'   => false,
        'error'   => $e->getMessage()
    ]);
}
exit();
?>