<?php
namespace app\controllers;
use app\models\cajaModel;

class cajaController extends cajaModel {

    public function listarMesasLiquidacion() {
        $consulta = $this->obtenerMesasLiquidacion();
        return $consulta->fetchAll();
    }
    public function liquidarMesa() {
    $pedido_id       = (int)($_POST['pedido_id'] ?? 0);
    $mesa_id         = (int)($_POST['mesa_id'] ?? 0);
    $metodo_pago     = trim($_POST['metodo_pago'] ?? 'Efectivo');
    $monto           = (float)($_POST['total_cuenta'] ?? 0);

    $recibido_limpio = str_replace([',', ' '], '', $_POST['dinero_recibido'] ?? '0');
    $monto_recibido  = (float)$recibido_limpio;

    $cambio_limpio   = str_replace([',', ' '], '', $_POST['cambio_calculado'] ?? '0');
    $cambio          = (float)$cambio_limpio;

    if ($pedido_id > 0 && $mesa_id > 0 && $monto > 0) {
        $this->registrarPago($pedido_id, $mesa_id, $metodo_pago, $monto, $monto_recibido, $cambio);
    }

    header("Location: " . APP_URL . "caja");
    exit();
    }

    public function obtenerBalanceCierre() {
        return $this->obtenerBalanceTurno();
    }

    public function procesarCierreCaja() {
        $monto_real     = (float)($_POST['monto_real'] ?? 0);
        $ventas         = (float)($_POST['ventas_totales'] ?? 0);
        $gastos         = (float)($_POST['gastos_totales'] ?? 0);
        $esperado       = (float)($_POST['monto_esperado'] ?? 0);
        $diferencia     = $monto_real - $esperado;
        $observaciones  = $this->limpiarCadena($_POST['observaciones'] ?? '');
        $usuario_id     = (int)($_SESSION['id_usuario'] ?? 1);

        $this->registrarCierreCaja([
            'usuario_id'     => $usuario_id,
            'monto_ventas'   => $ventas,
            'monto_gastos'   => $gastos,
            'monto_esperado' => $esperado,
            'monto_real'     => $monto_real,
            'diferencia'     => $diferencia,
            'observaciones'  => $observaciones
        ]);

        header("Location: " . APP_URL . "caja");
        exit();
    }
    public function registrarGastoC() {
    $descripcion = $this->limpiarCadena($_POST['descripcion'] ?? '');
    $monto       = (float)($_POST['monto'] ?? 0);
    $usuario_id  = (int)($_SESSION['id_usuario'] ?? 1);

    if (empty($descripcion) || $monto <= 0) {
        return [
            "tipo"  => "error",
            "texto" => "Debes indicar una descripción y un monto válido superior a cero."
        ];
    }

    $this->registrarGasto([
        'usuario_id'  => $usuario_id,
        'description' => $descripcion,
        'monto'       => $monto
    ]);

    header("Location: " . APP_URL . "caja");
    exit();
    }
}
?>