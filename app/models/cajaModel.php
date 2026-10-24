<?php
namespace app\models;

class cajaModel extends mainModel {
    public function obtenerMesasLiquidacion() {
        $sql = "SELECT 
                    m.id AS mesa_id,
                    m.numero_mesa,
                    m.estado AS mesa_estado,
                    p.id AS pedido_id,
                    p.total,
                    p.estado AS pedido_estado,
                    u.nombre_completo AS mesero_nombre
                FROM mesa m
                INNER JOIN pedido p ON p.mesa_id = m.id AND LOWER(p.estado::text) NOT IN ('pagado', 'cancelado')
                LEFT JOIN usuario u ON p.usuario_id = u.id
                WHERE LOWER(m.estado::text) IN ('precuenta')
                ORDER BY m.numero_mesa ASC";
        return $this->ejecutarConsulta($sql);
    }

    public function registrarPago($pedido_id, $mesa_id, $metodo_pago, $monto, $monto_recibido, $cambio) {
        try {
            $sqlPago = "INSERT INTO pago (pedido_id, metodo_pago, monto, monto_recibido, cambio, fecha) 
                        VALUES (:pedido_id, :metodo_pago::metodo_pago_tipo, :monto, :monto_recibido, :cambio, CURRENT_TIMESTAMP)
                        ON CONFLICT (pedido_id) DO UPDATE 
                        SET metodo_pago = EXCLUDED.metodo_pago,
                            monto = EXCLUDED.monto,
                            monto_recibido = EXCLUDED.monto_recibido,
                            cambio = EXCLUDED.cambio,
                            fecha = CURRENT_TIMESTAMP";

            $this->ejecutarConsulta($sqlPago, [
                ':pedido_id'      => (int)$pedido_id,
                ':metodo_pago'    => $metodo_pago,
                ':monto'          => (float)$monto,
                ':monto_recibido' => (float)$monto_recibido,
                ':cambio'         => (float)$cambio
            ]);

            $sqlPedido = "UPDATE pedido SET estado = 'Pagado'::estado_pedido WHERE id = :pedido_id";
            $this->ejecutarConsulta($sqlPedido, [':pedido_id' => (int)$pedido_id]);

            $sqlMesa = "UPDATE mesa SET estado = 'Disponible'::estado_mesa WHERE id = :mesa_id";
            $this->ejecutarConsulta($sqlMesa, [':mesa_id' => (int)$mesa_id]);

            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function obtenerBalanceTurno() {
        $sqlVentas = "SELECT COALESCE(SUM(monto), 0) AS total_ventas 
                      FROM pago 
                      WHERE fecha::date = CURRENT_DATE";
        $stmtVentas = $this->ejecutarConsulta($sqlVentas);
        $ventas = (float)($stmtVentas->fetch()['total_ventas'] ?? 0);

        $gastos = 0.0;
        try {
            $sqlGastos = "SELECT COALESCE(SUM(monto), 0) AS total_gastos 
                          FROM gasto 
                          WHERE fecha::date = CURRENT_DATE";
            $stmtGastos = $this->ejecutarConsulta($sqlGastos);
            $gastos = (float)($stmtGastos->fetch()['total_gastos'] ?? 0);
        } catch (\PDOException $e) {
            $gastos = 0.0;
        }

        $esperado = $ventas - $gastos;

        return [
            'ventas'   => $ventas,
            'gastos'   => $gastos,
            'esperado' => $esperado
        ];
    }

    public function registrarCierreCaja($datos) {
        $sql = "INSERT INTO cierre_caja (usuario_id, fecha, monto_ventas, monto_gastos, 
                    monto_esperado, monto_real, diferencia, observaciones) VALUES (:usuario_id, 
                    CURRENT_TIMESTAMP, :monto_ventas, :monto_gastos, :monto_esperado, :monto_real, :diferencia, :observaciones)";

        return $this->ejecutarConsulta($sql, [
            ':usuario_id'     => $datos['usuario_id'],
            ':monto_ventas'   => $datos['monto_ventas'],
            ':monto_gastos'   => $datos['monto_gastos'],
            ':monto_esperado' => $datos['monto_esperado'],
            ':monto_real'     => $datos['monto_real'],
            ':diferencia'     => $datos['diferencia'],
            ':observaciones'  => $datos['observaciones']
        ]);
    }

    public function registrarGasto($datos) {
        $sql = "INSERT INTO gasto (usuario_id, description, monto, fecha) 
                VALUES (:usuario_id, :description, :monto, CURRENT_TIMESTAMP)";

        return $this->ejecutarConsulta($sql, [
            ':usuario_id'   => (int)$datos['usuario_id'],
            ':description'  => $datos['description'],
            ':monto'        => (float)$datos['monto']
        ]);
    }
}