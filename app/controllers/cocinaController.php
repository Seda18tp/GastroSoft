<?php
namespace app\controllers;

use app\models\cocinaModel;

class cocinaController extends cocinaModel {

    /* Obtener comandas agrupadas por pedido */
    public function obtenerPedidos() {
        $consulta = $this->listarPedidos();
        $filas = $consulta->fetchAll();

        $comandas = [];

        foreach ($filas as $fila) {
            $pedidoId = $fila['pedido_id'];

            if (!isset($comandas[$pedidoId])) {
                $comandas[$pedidoId] = [
                    'pedido_id'     => $pedidoId,
                    'estado'        => $fila['pedido_estado'],
                    'mesero_id'     => $fila['mesero_id'],
                    'mesa_numero'   => $fila['mesa_numero'] ?? 'S/N',
                    'mesero_nombre' => $fila['mesero_nombre'] ?? 'Sin asignar',
                    'hora'          => !empty($fila['fecha']) ? date('h:i A', strtotime($fila['fecha'])) : '--:--',
                    'items'         => []
                ];
            }

            if (!empty($fila['producto_nombre'])) {
                $comandas[$pedidoId]['items'][] = [
                    'detalle_id' => $fila['detalle_id'],
                    'cantidad'   => $fila['cantidad'],
                    'producto'   => $fila['producto_nombre'],
                    'nota'       => $fila['observaciones'] ?? ''
                ];
            }
        }

        return $comandas;
    }

    /* Procesar el cambio de estado de la comanda */
    public function cambiarEstadoPedido() {
        $pedido_id   = (int)($_POST['pedido_id'] ?? 0);
        $estado_act  = trim($_POST['estado_actual'] ?? '');
        $mesa_numero = trim($_POST['mesa_numero'] ?? '');
        $mesero_id   = (int)($_POST['mesero_id'] ?? 0);

        if ($pedido_id <= 0) {
            return;
        }

        if (strtolower($estado_act) === 'en cocina') {
            // Pasa a En Preparacion
            $this->actualizarEstadoPedido($pedido_id, 'En Preparacion');
        } elseif (strtolower($estado_act) === 'en preparacion') {
            // Pasa a Listo
            $this->actualizarEstadoPedido($pedido_id, 'Listo');

            // Notificar al mesero
            $mensaje = "El pedido de la Mesa #" . $mesa_numero . " está listo para servir.";
            $this->crearNotificacionMesero($pedido_id, $mesero_id, $mensaje);
        }

        header("Location: " . APP_URL . "cocinero");
        exit();
    }

    public function obtenerNotificaciones($limite = 5) {
        $consulta = $this->verNotificaciones($limite);
        return $consulta->fetchAll();
    }

    public function obtenerHistorialPedidos($limite = 20) {
        $consulta = $this->historialPedidos($limite);
        return $consulta->fetchAll();
    }

    public function notificacionesCocinero($limite = 50) {
        $consulta = $this->historialNotificacionesCocinero($limite);
        return $consulta->fetchAll();
    }
}