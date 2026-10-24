<?php
// Limpiar cualquier salida previa o advertencia que rompa el JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . "/config/conexion.php";
require_once dirname(__DIR__, 2) . "/config/app.php";
require_once dirname(__DIR__, 2) . "/autoload.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

try {
    $db = new \app\models\mainModel();

    $sql = "SELECT 
                (SELECT COUNT(*) FROM pedido WHERE LOWER(estado) NOT IN ('pagado', 'cancelado')) AS pedidos_activos,
                (SELECT GROUP_CONCAT(CONCAT(id, ':', estado) ORDER BY id SEPARATOR '|') FROM pedido WHERE LOWER(estado) NOT IN ('pagado', 'cancelado')) AS estados_pedidos,
                (SELECT GROUP_CONCAT(CONCAT(id, ':', estado) ORDER BY id SEPARATOR '|') FROM mesa) AS estados_mesas,
                (SELECT MAX(id) FROM notificacion) AS ultima_notif,
                (SELECT COUNT(*) FROM notificacion) AS total_notif,
                (SELECT MAX(id) FROM detalle_pedido) AS ultimo_detalle,
                (SELECT COUNT(*) FROM detalle_pedido) AS total_detalles";

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