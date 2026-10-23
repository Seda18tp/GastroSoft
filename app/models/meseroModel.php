<?php
namespace app\models;

class meseroModel extends mainModel {

    /* 1. Obtener todas las mesas con sus pedidos activos */
    public function obtenerMesasConPedidos() {
        $sql = "SELECT 
                    m.id AS mesa_id,
                    m.numero_mesa,
                    m.ubicacion,
                    m.estado AS mesa_estado,
                    p.id AS pedido_id,
                    COALESCE(p.total, 0) AS subtotal_pedido,
                    p.estado AS pedido_estado
                FROM mesa m
                LEFT JOIN pedido p ON p.mesa_id = m.id AND LOWER(p.estado) NOT IN ('pagado', 'cancelado')
                ORDER BY CAST(m.numero_mesa AS UNSIGNED) ASC, m.numero_mesa ASC";

        return $this->ejecutarConsulta($sql);
    }

    /* 2. Zonas disponibles */
    public function obtenerZonas() {
        $sql = "SELECT DISTINCT ubicacion FROM mesa WHERE ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion ASC";
        return $this->ejecutarConsulta($sql);
    }

    /* 3. Detalles de productos pedidos en una mesa */
    public function obtenerDetallesPedido($pedido_id) {
        $sql = "SELECT 
                    dp.id AS detalle_id,
                    dp.cantidad,
                    dp.precio_unitario,
                    dp.subtotal,
                    dp.observaciones,
                    pr.nombre AS producto_nombre
                FROM detalle_pedido dp
                INNER JOIN producto pr ON dp.producto_id = pr.id
                WHERE dp.pedido_id = :pedido_id";

        return $this->ejecutarConsulta($sql, [':pedido_id' => (int)$pedido_id]);
    }

    /* 4. Notificaciones enviadas por cocina */
    public function obtenerNotificacionesCocina($limite = 5) {
        $sql = "SELECT 
                    id, 
                    mensaje, 
                    tipo, 
                    DATE_FORMAT(fecha, '%h:%i %p') AS hora 
                FROM notificacion 
                ORDER BY fecha DESC 
                LIMIT " . (int)$limite;

        return $this->ejecutarConsulta($sql);
    }

    /* 5. Catálogo de productos disponibles para añadir */
    public function listarProductosDisponibles() {
        $sql = "SELECT id, nombre, precio_venta, categoria_id 
                FROM producto 
                WHERE estado = 1 
                ORDER BY nombre ASC";
        return $this->ejecutarConsulta($sql);
    }

    /* 6. Agregar plato: Si no hay pedido crea uno nuevo y luego inserta el detalle */
    /* Agregar plato a pedido con captura correcta de LAST_INSERT_ID */
    public function agregarPlatoAPedido($mesa_id, $producto_id, $cantidad, $notas, $usuario_id) {
        // 1. Obtener una única conexión para mantener el ID de inserción
        $conexion = $this->conectar();

        // 2. Verificar si ya existe un pedido activo para esta mesa
        $sqlCheck = "SELECT id FROM pedido 
                    WHERE mesa_id = :mesa_id 
                    AND LOWER(estado) NOT IN ('pagado', 'cancelado') 
                    LIMIT 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([':mesa_id' => (int)$mesa_id]);
        $pedido = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        // 3. Consultar precio de venta del producto
        $sqlProd = "SELECT precio_venta FROM producto WHERE id = :producto_id";
        $stmtProd = $conexion->prepare($sqlProd);
        $stmtProd->execute([':producto_id' => (int)$producto_id]);
        $precio = (float)($stmtProd->fetch(\PDO::FETCH_ASSOC)['precio_venta'] ?? 0);
        $subtotal = $precio * (int)$cantidad;

        if ($pedido && !empty($pedido['id'])) {
            $pedido_id = (int)$pedido['id'];
        } else {
            // 4. Crear nuevo pedido usando la misma conexión
            $sqlNuevoPedido = "INSERT INTO pedido (mesa_id, usuario_id, total, estado, fecha) 
                            VALUES (:mesa_id, :usuario_id, 0.00, 'En Cocina', NOW())";
            $stmtNuevo = $conexion->prepare($sqlNuevoPedido);
            $stmtNuevo->execute([
                ':mesa_id'    => (int)$mesa_id,
                ':usuario_id' => (int)$usuario_id
            ]);

            // Obtener el ID exacto del pedido recién insertado
            $pedido_id = (int)$conexion->lastInsertId();

            // Si la base de datos no devolvió el ID mediante lastInsertId, buscar el ID más reciente
            if ($pedido_id === 0) {
                $stmtLast = $conexion->prepare("SELECT id FROM pedido WHERE mesa_id = :mesa_id ORDER BY id DESC LIMIT 1");
                $stmtLast->execute([':mesa_id' => (int)$mesa_id]);
                $pedido_id = (int)($stmtLast->fetch(\PDO::FETCH_ASSOC)['id'] ?? 0);
            }

            // Marcar la mesa como Ocupada
            $stmtMesa = $conexion->prepare("UPDATE mesa SET estado = 'Ocupada' WHERE id = :mesa_id");
            $stmtMesa->execute([':mesa_id' => (int)$mesa_id]);
        }

        if ($pedido_id <= 0) {
            return false;
        }

        // 5. Insertar ítem en detalle_pedido con el pedido_id garantizado
        $sqlDetalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, observaciones, estado) 
                    VALUES (:pedido_id, :producto_id, :cantidad, :precio, :subtotal, :notas, 'pendiente')";
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        $stmtDetalle->execute([
            ':pedido_id'    => $pedido_id,
            ':producto_id'  => (int)$producto_id,
            ':cantidad'     => (int)$cantidad,
            ':precio'       => $precio,
            ':subtotal'     => $subtotal,
            ':notas'        => $notas
        ]);

        // 6. Recalcular el total del pedido
        $sqlTotal = "UPDATE pedido 
                    SET total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_pedido WHERE pedido_id = :pedido_id) 
                    WHERE id = :pedido_id";
        $stmtTotal = $conexion->prepare($sqlTotal);
        $stmtTotal->execute([':pedido_id' => $pedido_id]);

        return true;
    }

    /* 7. Pedir cuenta */
    public function solicitarPrecuenta($mesa_id) {
        $sql = "UPDATE mesa SET estado = 'Precuenta' WHERE id = :mesa_id";
        return $this->ejecutarConsulta($sql, [':mesa_id' => (int)$mesa_id]);
    }

    public function enviarPedidoACocina($pedido_id, $mesa_id, $usuario_id) {
        try {
            // A. Actualizar estado del pedido global
            $sqlPedido = "UPDATE pedido 
                        SET estado = 'En Cocina' 
                        WHERE id = :pedido_id";
            $this->ejecutarConsulta($sqlPedido, [':pedido_id' => (int)$pedido_id]);

            // B. Actualizar ítems pendientes a 'en_proceso'
            $sqlItems = "UPDATE detalle_pedido 
                        SET estado = 'En Cocina' 
                        WHERE pedido_id = :pedido_id AND estado = 'En Cocina'";
            $this->ejecutarConsulta($sqlItems, [':pedido_id' => (int)$pedido_id]);

            // C. Obtener número de mesa para el mensaje
            $sqlMesa = "SELECT numero_mesa FROM mesa WHERE id = :mesa_id";
            $stmtMesa = $this->ejecutarConsulta($sqlMesa, [':mesa_id' => (int)$mesa_id]);
            $numMesa = $stmtMesa->fetch()['numero_mesa'] ?? $mesa_id;

            // D. Insertar notificación para cocina
            $mensaje = "Nuevo pedido enviado de la Mesa #" . $numMesa . " para preparar.";
            $sqlNoti = "INSERT INTO notificacion (pedido_id, usuario_id, tipo, mensaje, leido, fecha) 
                        VALUES (:pedido_id, :usuario_id, 'cambio_pedido', :mensaje, 0, NOW())";
            $this->ejecutarConsulta($sqlNoti, [
                ':pedido_id'   => (int)$pedido_id,
                ':usuario_id'  => (int)$usuario_id,
                ':mensaje'     => $mensaje
            ]);

            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}