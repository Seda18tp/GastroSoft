<?php
namespace app\controllers;

use app\models\adminModel;

class adminController extends adminModel {

    public function obtenerValores() {
        return $this->obtenerMetricas();
    }

    public function obtenerReservasDia() {
        $consulta = $this->listarReservasDia();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function obtenerEstadisticas() {
        $consulta = $this->obtenerVentasMetodo();
        $filas = $consulta ? $consulta->fetchAll() : [];

        $granTotal = 0;
        foreach ($filas as $f) {
            $granTotal += (float)($f['total_monto'] ?? 0);
        }

        $metodos = [];
        foreach ($filas as $f) {
            $monto = (float)($f['total_monto'] ?? 0);
            $porcentaje = ($granTotal > 0) ? round(($monto / $granTotal) * 100) : 0;

            $metodos[] = [
                'metodo'     => ucfirst(strtolower($f['metodo_pago'] ?? 'Desconocido')),
                'monto'      => $monto,
                'porcentaje' => $porcentaje
            ];
        }

        return [
            'gran_total' => $granTotal,
            'metodos'    => $metodos
        ];
    }

    public function obtenerTop($limite = 5) {
        $consulta = $this->obtenerTopProductos($limite);
        $productos = $consulta ? $consulta->fetchAll() : [];

        if (empty($productos)) {
            return [];
        }

        $maxVentas = (int)($productos[0]['total_vendido'] ?? 0);
        $resultado = [];
        $colores = ['#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899'];

        foreach ($productos as $indice => $prod) {
            $cantidad = (int)($prod['total_vendido'] ?? 0);
            $anchoBarra = ($maxVentas > 0) ? round(($cantidad / $maxVentas) * 100) : 0;

            $resultado[] = [
                'nombre'   => $prod['producto_nombre'] ?? 'Sin nombre',
                'cantidad' => $cantidad,
                'ancho'    => $anchoBarra,
                'color'    => $colores[$indice % count($colores)]
            ];
        }

        return $resultado;
    }

    public function obtenerCategorias() {
        $consulta = $this->listarCategorias();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function obtenerProductosCarta($categoria_id = 0) {
        $consulta = $this->listarProductosCarta($categoria_id);
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function alternarEstadoProducto() {
        $id = (int)($_POST['producto_id'] ?? 0);
        $estadoActual = trim($_POST['estado_actual'] ?? '');

        if ($id > 0) {
            $nuevoEstado = (strtolower($estadoActual) === 'activo') ? 'Inactivo' : 'Activo';
            $this->cambiarEstadoProducto($id, $nuevoEstado);
        }

        $cat = isset($_GET['cat']) ? '?cat=' . (int)$_GET['cat'] : '';
        echo "<script>window.location.replace('" . APP_URL . "carta" . $cat . "');</script>";
        exit();
    }

    public function obtenerProductoPorId($id) {
        $consulta = $this->obtenerProductoId($id);
        return $consulta ? $consulta->fetch() : false;
    }

    public function registrarPlato() {
        $nombre = trim($_POST['nombre'] ?? '');
        if (method_exists($this, 'limpiarCadena')) {
            $nombre = $this->limpiarCadena($nombre);
        }

        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $precio_raw   = $_POST['precio_venta'] ?? $_POST['precio'] ?? 0;
        $precio_venta = (float)$precio_raw;

        $descripcion = trim($_POST['descripcion'] ?? '');
        if (method_exists($this, 'limpiarCadena')) {
            $descripcion = $this->limpiarCadena($descripcion);
        }

        $estado = trim($_POST['estado'] ?? 'Activo');

        if ($nombre === '' || $categoria_id <= 0 || $precio_venta <= 0) {
            return [
                "tipo"  => "error", 
                "texto" => "Nombre, categoría y precio son obligatorios."
            ];
        }

        $nombreImagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $permitidos)) {
                // Si la imagen supera 1.5MB, no procesar para no saturar la petición en Vercel
                if ($_FILES['imagen']['size'] <= 1500000) {
                    $tipoMime = ($extension === 'jpg') ? 'jpeg' : $extension;
                    $contenidoBinario = file_get_contents($_FILES['imagen']['tmp_name']);
                    $nombreImagen = 'data:image/' . $tipoMime . ';base64,' . base64_encode($contenidoBinario);
                }
            }
        }

        $datos = [
            'categoria_id' => $categoria_id,
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'precio_venta' => $precio_venta,
            'img_url'      => $nombreImagen,
            'estado'       => $estado
        ];

        try {
            $this->registrarProducto($datos);
            echo "<script>window.location.href = '" . APP_URL . "carta';</script>";
            exit();
        } catch (\Throwable $e) {
            return [
                "tipo"  => "error",
                "texto" => "Error al guardar el plato: " . $e->getMessage()
            ];
        }
    }

    public function actualizarPlato() {
        $id           = (int)($_POST['id'] ?? 0);
        $nombre       = $this->limpiarCadena($_POST['nombre'] ?? '');
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? $_POST['precio'] ?? 0);
        $descripcion  = $this->limpiarCadena($_POST['descripcion'] ?? '');
        $estado       = trim($_POST['estado'] ?? 'Activo');
        $imagenActual = !empty($_POST['imagen_actual']) ? trim($_POST['imagen_actual']) : null;

        if ($id <= 0 || empty($nombre) || $categoria_id <= 0 || $precio_venta <= 0) {
            return [
                "tipo"  => "error",
                "texto" => "Todos los campos obligatorios del plato deben ser completados."
            ];
        }

        $nombreImagen = $imagenActual;

        // Si se subió una nueva imagen, procesarla en Base64
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $permitidos)) {
                if ($_FILES['imagen']['size'] <= 1500000) { // Máximo 1.5MB
                    $tipoMime = ($extension === 'jpg') ? 'jpeg' : $extension;
                    $contenidoBinario = file_get_contents($_FILES['imagen']['tmp_name']);
                    $nombreImagen = 'data:image/' . $tipoMime . ';base64,' . base64_encode($contenidoBinario);
                }
            }
        }

        $datos = [
            'id'           => $id,
            'categoria_id' => $categoria_id,
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'precio_venta' => $precio_venta,
            'img_url'      => $nombreImagen,
            'estado'       => $estado
        ];

        try {
            $this->actualizarProducto($datos);
            echo "<script>window.location.href = '" . APP_URL . "carta';</script>";
            exit();
        } catch (\Throwable $e) {
            return [
                "tipo"  => "error",
                "texto" => "Error al actualizar el plato: " . $e->getMessage()
            ];
        }
    }

    public function resolverUrlImagen($imagen) {
        if (empty($imagen) || $imagen === 'default.png') {
            return APP_URL . "app/img/default.png";
        }

        if (strpos($imagen, 'http://') === 0 || strpos($imagen, 'https://') === 0) {
            return $imagen;
        }

        if (strpos($imagen, 'app/') === 0) {
            return APP_URL . $imagen;
        }

        return APP_URL . "app/img/productos/" . $imagen;
    }

    public function obtenerInventario() {
        $consulta = $this->listarInventario();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function ajustarStock() {
        $inventario_id = (int)($_POST['inventario_id'] ?? 0);
        $stock_actual  = (float)($_POST['stock_actual'] ?? 0);
        $stock_minimo  = (float)($_POST['stock_minimo'] ?? 0);
        $und_medida    = $this->limpiarCadena($_POST['und_medida'] ?? 'unidad');

        if ($inventario_id > 0) {
            $this->actualizarStock($inventario_id, $stock_actual, $stock_minimo, $und_medida);
        }

        echo "<script>window.location.replace('" . APP_URL . "inventario');</script>";
        exit();
    }

    public function obtenerUsuarios() {
        $consulta = $this->listarUsuarios();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function obtenerRoles() {
        $consulta = $this->listarRoles();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function guardarUsuario() {
        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        $nombre     = $this->limpiarCadena($_POST['nombre_completo'] ?? $_POST['nombre'] ?? '');
        $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $rol_id     = (int)($_POST['rol_id'] ?? 0);
        $estado     = trim($_POST['estado'] ?? 'Activo');
        $clave      = trim($_POST['password'] ?? '');

        if (empty($nombre) || empty($email) || $rol_id <= 0) {
            return [
                "tipo"  => "error",
                "texto" => "Todos los campos obligatorios deben ser diligenciados."
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "tipo"  => "error",
                "texto" => "El formato del correo electrónico no es válido."
            ];
        }

        if ($usuario_id === 0 && empty($clave)) {
            return [
                "tipo"  => "error",
                "texto" => "Debes asignar una contraseña para el nuevo usuario."
            ];
        }

        if ($this->verificarEmailExiste($email, $usuario_id)) {
            return [
                "tipo"  => "error",
                "texto" => "El correo electrónico '{$email}' ya se encuentra registrado."
            ];
        }

        try {
            if ($usuario_id === 0) {
                $datos = [
                    'nombre'   => $nombre,
                    'email'    => $email,
                    'password' => password_hash($clave, PASSWORD_BCRYPT),
                    'rol_id'   => $rol_id,
                    'estado'   => $estado
                ];
                $this->registrarUsuario($datos);
            } else {
                $datos = [
                    'id'       => $usuario_id,
                    'nombre'   => $nombre,
                    'email'    => $email,
                    'rol_id'   => $rol_id,
                    'estado'   => $estado,
                    'password' => !empty($clave) ? password_hash($clave, PASSWORD_BCRYPT) : ''
                ];
                $this->actualizarUsuario($datos);
            }

            echo "<script>window.location.href = '" . APP_URL . "usuarios';</script>";
            exit();
        } catch (\Throwable $e) {
            return [
                "tipo"  => "error",
                "texto" => "Error al registrar el usuario: " . $e->getMessage()
            ];
        }
    }

    public function alternarEstadoUsuario() {
        $id = (int)($_POST['usuario_id'] ?? 0);
        $estadoActual = trim($_POST['estado_actual'] ?? '');

        if ($id === (int)($_SESSION['id_usuario'] ?? 0)) {
            return [
                "tipo"  => "error",
                "texto" => "No puedes desactivar tu propio usuario."
            ];
        }

        if ($id > 0) {
            $nuevoEstado = (strtolower($estadoActual) === 'activo') ? 'Inactivo' : 'Activo';
            $this->cambiarEstadoUsuario($id, $nuevoEstado);
        }

        echo "<script>window.location.replace('" . APP_URL . "usuarios');</script>";
        exit();
    }

    public function obtenerMesasReserva() {
        $consulta = $this->listarMesasReserva();
        return $consulta ? $consulta->fetchAll() : [];
    }

    public function registrarReservaM() {
        $nombre_cliente    = $this->limpiarCadena($_POST['nombre_cliente'] ?? '');
        $telefono          = $this->limpiarCadena($_POST['telefono'] ?? '');
        $mesa_id           = (int)($_POST['mesa_id'] ?? 0);
        $cantidad_personas = (int)($_POST['cantidad_personas'] ?? 1);
        $fecha_reserva     = trim($_POST['fecha_reserva'] ?? '');
        $hora_inicio       = trim($_POST['hora_inicio'] ?? '');
        $observaciones     = $this->limpiarCadena($_POST['observaciones'] ?? '');

        if (empty($nombre_cliente) || $mesa_id <= 0 || empty($fecha_reserva) || empty($hora_inicio)) {
            return ["tipo" => "error", "texto" => "Nombre, mesa, fecha y hora son obligatorios."];
        }

        $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +2 hours'));

        $datos = [
            'mesa_id'           => $mesa_id,
            'nombre_cliente'    => $nombre_cliente,
            'telefono'          => $telefono,
            'cantidad_personas' => $cantidad_personas,
            'fecha_reserva'     => $fecha_reserva,
            'hora_inicio'       => $hora_inicio . ':00',
            'hora_fin'          => $hora_fin,
            'estado'            => 'Confirmada',
            'observaciones'     => $observaciones
        ];

        try {
            $this->registrarReserva($datos);
            echo "<script>window.location.href = '" . APP_URL . "admin';</script>";
            exit();
        } catch (\Throwable $e) {
            return ["tipo" => "error", "texto" => "Error: " . $e->getMessage()];
        }
    }

    public function cambiarEstadoReservaC() {
        $reserva_id   = (int)($_POST['reserva_id'] ?? 0);
        $nuevo_estado = $this->limpiarCadena($_POST['nuevo_estado'] ?? '');

        if ($reserva_id > 0 && !empty($nuevo_estado)) {
            $this->cambiarEstadoReserva($reserva_id, $nuevo_estado);
        }

        echo "<script>window.location.replace('" . APP_URL . "admin');</script>";
        exit();
    }
}
?>