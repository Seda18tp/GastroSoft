<?php
namespace app\models;

class meseroModel extends mainModel {

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
                LEFT JOIN pedido p ON p.mesa_id = m.id AND LOWER(p.estado::text) NOT IN ('pagado', 'cancelado')
                ORDER BY CAST(m.numero_mesa AS INTEGER) ASC, m.numero_mesa ASC";

        return $this->ejecutarConsulta($sql);
    }

    public function obtenerZonas() {
        $sql = "SELECT DISTINCT ubicacion FROM mesa WHERE ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion ASC";
        return $this->ejecutarConsulta($sql);
    }

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

    public function obtenerNotificacionesCocina($usuario_id = 0, $limite = 5) {
        $sql = "SELECT 
                    id, 
                    mensaje, 
                    tipo, 
                    TO_CHAR(fecha, 'HH12:MI AM') AS hora 
                FROM notificacion 
                WHERE LOWER(tipo::text) = 'pedido_listo' 
                ORDER BY fecha DESC 
                LIMIT " . (int)$limite;

        return $this->ejecutarConsulta($sql);
    }

    public function listarProductosDisponibles() {
        $sql = "SELECT id, nombre, precio_venta, categoria_id, img_url 
                FROM producto 
                WHERE estado::text = 'Activo' OR disponible = true 
                ORDER BY nombre ASC";
        return $this->ejecutarConsulta($sql);
    }

    public function procesarComanda($mesa_id, $items, $usuario_id) {
        $conexion = $this->conectar();

        $sqlCheck = "SELECT id, estado FROM pedido 
                     WHERE mesa_id = :mesa_id 
                       AND LOWER(estado::text) NOT IN ('pagado', 'cancelado') 
                     LIMIT 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([':mesa_id' => (int)$mesa_id]);
        $pedido = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if ($pedido && !empty($pedido['id'])) {
            $pedido_id = (int)$pedido['id'];

            $sqlReactivar = "UPDATE pedido SET estado = 'Pendiente'::estado_pedido WHERE id = :pedido_id";
            $stmtReactivar = $conexion->prepare($sqlReactivar);
            $stmtReactivar->execute([':pedido_id' => $pedido_id]);

            $stmtMesa = $conexion->prepare("UPDATE mesa SET estado = 'Ocupada'::estado_mesa WHERE id = :mesa_id");
            $stmtMesa->execute([':mesa_id' => (int)$mesa_id]);

            $esAdicion = true;
        } else {
            $sqlNuevo = "INSERT INTO pedido (mesa_id, usuario_id, total, estado, fecha) 
                         VALUES (:mesa_id, :usuario_id, 0.00, 'Pendiente'::estado_pedido, CURRENT_TIMESTAMP)
                         RETURNING id";
            $stmtNuevo = $conexion->prepare($sqlNuevo);
            $stmtNuevo->execute([
                ':mesa_id'    => (int)$mesa_id,
                ':usuario_id' => (int)$usuario_id
            ]);

            $pedido_id = (int)($stmtNuevo->fetch(\PDO::FETCH_ASSOC)['id'] ?? 0);

            $stmtMesa = $conexion->prepare("UPDATE mesa SET estado = 'Ocupada'::estado_mesa WHERE id = :mesa_id");
            $stmtMesa->execute([':mesa_id' => (int)$mesa_id]);

            $esAdicion = false;
        }

        if ($pedido_id <= 0) return false;

        $sqlDetalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, observaciones, estado) 
                       VALUES (:pedido_id, :producto_id, :cantidad, :precio, :subtotal, :notas, 'Pendiente'::estado_detalle)";
        $stmtDetalle = $conexion->prepare($sqlDetalle);

        $sqlInventario = "UPDATE inventario 
                          SET stock_actual = GREATEST(0, stock_actual::numeric - :cantidad),
                              actualizado_en = CURRENT_TIMESTAMP 
                          WHERE producto_id = :producto_id";
        $stmtInventario = $conexion->prepare($sqlInventario);

        foreach ($items as $it) {
            $prod_id  = (int)($it['producto_id'] ?? 0);
            $cant     = (int)($it['cantidad'] ?? 1);
            $precio   = (float)($it['precio'] ?? 0);
            $subtotal = $cant * $precio;
            $notas    = trim($it['notas'] ?? '');

            if ($prod_id > 0) {
                $stmtDetalle->execute([
                    ':pedido_id'   => $pedido_id,
                    ':producto_id' => $prod_id,
                    ':cantidad'    => $cant,
                    ':precio'      => $precio,
                    ':subtotal'    => $subtotal,
                    ':notas'       => $notas
                ]);

                $stmtInventario->execute([
                    ':cantidad'    => $cant,
                    ':producto_id' => $prod_id
                ]);
            }
        }

        $sqlTotal = "UPDATE pedido 
                     SET total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_pedido WHERE pedido_id = :pedido_id) 
                     WHERE id = :pedido_id";
        $stmtTotal = $conexion->prepare($sqlTotal);
        $stmtTotal->execute([':pedido_id' => $pedido_id]);

        $stmtMesaInfo = $conexion->prepare("SELECT numero_mesa FROM mesa WHERE id = :mesa_id");
        $stmtMesaInfo->execute([':mesa_id' => (int)$mesa_id]);
        $numMesa = $stmtMesaInfo->fetch(\PDO::FETCH_ASSOC)['numero_mesa'] ?? $mesa_id;

        $mensajeNoti = $esAdicion 
            ? "Se agregaron platos extra a la comanda de la Mesa #" . $numMesa 
            : "Comanda enviada de la Mesa #" . $numMesa . " para preparación.";

        $sqlNoti = "INSERT INTO notificacion (pedido_id, usuario_id, tipo, mensaje, fecha) 
                    VALUES (:pedido_id, :usuario_id, 'nuevo_pedido', :mensaje, CURRENT_TIMESTAMP)";
        $stmtNoti = $conexion->prepare($sqlNoti);
        $stmtNoti->execute([
            ':pedido_id'   => $pedido_id,
            ':usuario_id'  => (int)$usuario_id,
            ':mensaje'     => $mensajeNoti
        ]);

        return true;
    }

    public function solicitarPrecuenta($mesa_id) {
        $sql = "UPDATE mesa SET estado = 'Precuenta'::estado_mesa WHERE id = :mesa_id";
        return $this->ejecutarConsulta($sql, [':mesa_id' => (int)$mesa_id]);
    }
}