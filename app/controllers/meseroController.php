<?php
namespace app\controllers;

use app\models\meseroModel;

class meseroController extends meseroModel {

    public function listarMesas() {
        $consulta = $this->obtenerMesasConPedidos();
        $mesas = $consulta ? $consulta->fetchAll() : [];

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
        $consulta = $this->obtenerZonas();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function listarNotificaciones() {
    $usuario_id = (int)($_SESSION['id_usuario'] ?? 0);
    $consulta = $this->obtenerNotificacionesCocina($usuario_id);
    return $consulta ? $consulta->fetchAll() : [];
    }

    public function listarProductos() {
        $consulta = $this->listarProductosDisponibles();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function guardarYEnviarComanda() {
        $mesa_id    = (int)($_POST['mesa_id'] ?? 0);
        $items_raw  = $_POST['items_json'] ?? '[]';
        $items      = json_decode($items_raw, true) ?? [];
        $usuario_id = (int)($_SESSION['id_usuario'] ?? 1);

        if ($mesa_id > 0 && !empty($items)) {
            $this->procesarComanda($mesa_id, $items, $usuario_id);
        }

        echo "<script>window.location.replace('" . APP_URL . "mesero?mesa=" . $mesa_id . "');</script>";
        exit();
    }

    public function pedirCuenta() {
        $mesa_id = (int)($_POST['mesa_id'] ?? 0);
        if ($mesa_id > 0) {
            $this->solicitarPrecuenta($mesa_id);
        }
        echo "<script>window.location.replace('" . APP_URL . "mesero?mesa=" . $mesa_id . "');</script>";
        exit();
    }
}
?>