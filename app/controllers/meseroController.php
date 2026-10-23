<?php
namespace app\controllers;
use app\models\meseroModel;

class meseroController extends meseroModel {

    public function listarMesas() {
        $consulta = $this->obtenerMesasConPedidos();
        $mesas = $consulta->fetchAll();

        foreach ($mesas as &$m) {
            if (!empty($m['pedido_id'])) {
                $m['items'] = $this->obtenerDetallesPedido($m['pedido_id'])->fetchAll();
            } else {
                $m['items'] = [];
            }
        }
        return $mesas;
    }

    public function listarZonas() {
        return $this->obtenerZonas()->fetchAll();
    }

    public function listarNotificaciones() {
        return $this->obtenerNotificacionesCocina()->fetchAll();
    }

    public function listarProductos() {
        return $this->listarProductosDisponibles()->fetchAll();
    }

    public function agregarPlato() {
        $mesa_id     = (int)($_POST['mesa_id'] ?? 0);
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = max(1, (int)($_POST['cantidad'] ?? 1));
        $notas       = $this->limpiarCadena($_POST['observaciones'] ?? '');
        $usuario_id  = (int)($_SESSION['id_usuario'] ?? 1);

        if ($mesa_id > 0 && $producto_id > 0) {
            $this->agregarPlatoAPedido($mesa_id, $producto_id, $cantidad, $notas, $usuario_id);
        }

        header("Location: " . APP_URL . "mesero?mesa=" . $mesa_id);
        exit();
    }

    public function pedirCuenta() {
        $mesa_id = (int)($_POST['mesa_id'] ?? 0);
        if ($mesa_id > 0) {
            $this->solicitarPrecuenta($mesa_id);
        }
        header("Location: " . APP_URL . "mesero?mesa=" . $mesa_id);
        exit();
    }
    /* Procesar envío del pedido a cocina */
    public function enviarCocina() {
        $pedido_id  = (int)($_POST['pedido_id'] ?? 0);
        $mesa_id    = (int)($_POST['mesa_id'] ?? 0);
        $usuario_id = (int)($_SESSION['id_usuario'] ?? 1);

        if ($pedido_id > 0 && $mesa_id > 0) {
            $this->enviarPedidoACocina($pedido_id, $mesa_id, $usuario_id);
        }

        header("Location: " . APP_URL . "mesero?mesa=" . $mesa_id);
        exit();
    }
}