<?php
namespace app\models;

class cocinaModel extends mainModel {

    public function listarPedidos() {
        $sql = "SELECT 
                    p.id AS pedido_id,
                    p.estado AS pedido_estado,
                    p.usuario_id AS mesero_id,
                    m.numero_mesa AS mesa_numero,
                    u.nombre_completo AS mesero_nombre,
                    dp.id AS detalle_id,
                    dp.cantidad,
                    dp.observaciones,
                    prod.nombre AS producto_nombre,
                    p.fecha
                FROM pedido p
                INNER JOIN mesa m ON p.mesa_id = m.id
                INNER JOIN usuario u ON p.usuario_id = u.id
                LEFT JOIN detalle_pedido dp ON p.id = dp.pedido_id
                LEFT JOIN producto prod ON dp.producto_id = prod.id
                WHERE LOWER(TRIM(p.estado)) IN ('Pendiente','En Preparacion')
                ORDER BY p.fecha ASC";

        return $this->ejecutarConsulta($sql);
    }

    public function actualizarEstadoPedido($pedido_id, $nuevo_estado) {
        $sql = "UPDATE pedido SET estado = :estado WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':estado' => $nuevo_estado,
            ':id'     => $pedido_id
        ]);
    }

    public function crearNotificacionMesero($pedido_id, $mesero_id, $mensaje) {
        $sql = "INSERT INTO notificacion (pedido_id, usuario_id, tipo, mensaje, fecha) 
                VALUES (:pedido_id, :usuario_id, 'Pedido_listo', :mensaje, NOW())";
        return $this->ejecutarConsulta($sql, [
            ':pedido_id'   => $pedido_id,
            ':usuario_id'  => $mesero_id,
            ':mensaje'     => $mensaje
        ]);
    }

    public function verNotificaciones($limite = 5) {
        $sql = "SELECT 
                    id, 
                    mensaje, 
                    tipo, 
                    DATE_FORMAT(fecha, '%h:%i %p') AS hora 
                FROM notificacion 
                WHERE tipo = 'nuevo_pedido' 
                ORDER BY fecha DESC 
                LIMIT " . (int)$limite;

        return $this->ejecutarConsulta($sql);
    }

    public function historialPedidos($limite = 20) {
        $limite = (int)$limite;
        $sql = "SELECT 
                    p.id,
                    p.total,
                    p.estado,
                    p.fecha,
                    m.numero_mesa AS mesa_numero,
                    u.nombre_completo AS mesero_nombre
                FROM pedido p
                INNER JOIN mesa m ON p.mesa_id = m.id
                INNER JOIN usuario u ON p.usuario_id = u.id
                WHERE p.estado IN ('Listo', 'Pagado', 'cancelado')
                ORDER BY p.fecha DESC 
                LIMIT $limite";

        return $this->ejecutarConsulta($sql);
    }

    public function historialNotificacionesCocinero($limite = 50) {
        $limite = (int)$limite;
        $sql = "SELECT id, pedido_id, usuario_id, tipo, mensaje, fecha 
                FROM notificacion 
                ORDER BY fecha DESC 
                LIMIT $limite";
        return $this->ejecutarConsulta($sql);
    }
}
?>