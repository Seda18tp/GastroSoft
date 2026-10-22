<?php
namespace app\controllers;

use app\models\adminModel;

class adminController extends adminModel {

    public function obtenerValores() {
        return $this->obtenerMetricas();
    }

    public function obtenerReservasDia() {
        $consulta = $this->listarReservasDia();
        return $consulta->fetchAll();
    }
    public function obtenerEstadisticas() {
    $consulta = $this->obtenerVentasMetodo();
    $filas = $consulta->fetchAll();

    $granTotal = 0;
    foreach ($filas as $f) {
        $granTotal += (float)$f['total_monto'];
    }

    $metodos = [];
    foreach ($filas as $f) {
        $monto = (float)$f['total_monto'];
        $porcentaje = ($granTotal > 0) ? round(($monto / $granTotal) * 100) : 0;

        $metodos[] = [
            'metodo'     => ucfirst(strtolower($f['metodo_pago'])),
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
    $productos = $consulta->fetchAll();

    if (empty($productos)) {
        return [];
    }
    $maxVentas = (int)$productos[0]['total_vendido'];

    $resultado = [];
    $colores = ['#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899'];

    foreach ($productos as $indice => $prod) {
        $cantidad = (int)$prod['total_vendido'];
        $anchoBarra = ($maxVentas > 0) ? round(($cantidad / $maxVentas) * 100) : 0;

        $resultado[] = [
            'nombre'     => $prod['producto_nombre'],
            'cantidad'   => $cantidad,
            'ancho'      => $anchoBarra,
            'color'      => $colores[$indice % count($colores)]
        ];
    }

    return $resultado;
    }
    // Obtener categorias
    public function obtenerCategorias() {
    $consulta = $this->listarCategorias();
    return $consulta->fetchAll();
    }

    //Obtener productos segun categoria
    public function obtenerProductosCartaControlador($categoria_id = 0) {
    $consulta = $this->listarProductosCarta($categoria_id);
    return $consulta->fetchAll();
    }

    //Procesar cambio de estado
    public function alternarEstadoProductoControlador() {
    $id = (int)($_POST['producto_id'] ?? 0);
    $estadoActual = trim($_POST['estado_actual'] ?? '');

    if ($id > 0) {
        $nuevoEstado = (strtolower($estadoActual) === 'activo') ? 'Inactivo' : 'Activo';
        $this->cambiarEstadoProducto($id, $nuevoEstado);
    }

    $cat = isset($_GET['cat']) ? '?cat=' . (int)$_GET['cat'] : '';
    header("Location: " . APP_URL . "carta" . $cat);
    exit();
    }
    // Obtener datos de un producto
    public function obtenerProductoPorId($id) {
    $consulta = $this->obtenerProductoId($id);
    return $consulta->fetch(); 
    }
    // Registrar Plato
    public function registrarPlato() {
        // 1. Capturar datos tolerando ambos nombres de campo
        $nombre = trim($_POST['nombre'] ?? '');
        if (method_exists($this, 'limpiarCadena')) {
            $nombre = $this->limpiarCadena($nombre);
        }

        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        
        // Captura precio_venta o precio indistintamente
        $precio_raw = $_POST['precio_venta'] ?? $_POST['precio'] ?? 0;
        $precio_venta = (float)$precio_raw;

        $descripcion = trim($_POST['descripcion'] ?? '');
        if (method_exists($this, 'limpiarCadena')) {
            $descripcion = $this->limpiarCadena($descripcion);
        }

        $estado = trim($_POST['estado'] ?? 'Activo');

        // 2. Validación
        if ($nombre === '' || $categoria_id <= 0 || $precio_venta <= 0) {
            return [
                "tipo"  => "error", 
                "texto" => "Nombre, categoría y precio son obligatorios."
            ];
        }

        // 3. Procesar archivo de imagen
        $nombreImagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
            $tipoArchivo = mime_content_type($_FILES['imagen']['tmp_name']);

            if (in_array($tipoArchivo, $permitidos)) {
                // Ruta física absoluta al directorio de imágenes
                $carpetaDestino = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR;
                
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }

                $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImagen = md5(uniqid(rand(), true)) . "." . strtolower($extension);

                move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $nombreImagen);
            }
        }

        // 4. Mapeo exacto a columnas de la BD
        $datos = [
            'categoria_id' => $categoria_id,
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'precio_venta' => $precio_venta,
            'img_url'      => $nombreImagen,
            'estado'       => $estado
        ];

        $this->registrarProducto($datos);

        header("Location: " . APP_URL . "carta");
        exit();
    }

    // Actualizar Plato

    public function actualizarPlato() {
        $id           = (int)($_POST['id'] ?? 0);
        $nombre       = $this->limpiarCadena($_POST['nombre'] ?? '');
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);
        $descripcion  = $this->limpiarCadena($_POST['descripcion'] ?? '');
        $estado       = $_POST['estado'] ?? 'Activo';
        $imagenActual = !empty($_POST['imagen_actual']) ? trim($_POST['imagen_actual']) : null;

        if ($id <= 0 || empty($nombre) || $categoria_id <= 0 || $precio_venta <= 0) {
            return;
        }

        $nombreImagen = $imagenActual;

        // Subida de nueva imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $tipoArchivo = mime_content_type($_FILES['imagen']['tmp_name']);
            $permitidos  = ['image/jpeg', 'image/png', 'image/webp'];

            if (in_array($tipoArchivo, $permitidos)) {
                // Carpeta accesible públicamente
                $carpeta = "./app/img/productos/";
                if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }

                $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImagen = md5(uniqid(rand(), true)) . "." . $extension;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . $nombreImagen)) {
                    // Eliminar archivo viejo si existía
                    if (!empty($imagenActual) && is_file($carpeta . $imagenActual)) {
                        unlink($carpeta . $imagenActual);
                    }
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

        $this->actualizarProducto($datos);

        header("Location: " . APP_URL . "carta");
        exit();
    }
    public function resolverUrlImagen($imagen) {
    if (empty($imagen) || $imagen === 'default.png') {
        return APP_URL . "app/img/default.png";
    }

    // S
    if (strpos($imagen, 'http://') === 0 || strpos($imagen, 'https://') === 0) {
        return $imagen;
    }

    // Si guardó la ruta relativa tipo "app/views/img/productos/..."
    if (strpos($imagen, 'app/') === 0) {
        return APP_URL . $imagen;
    }

    // Si solo guardó el nombre del archivo "foto.png"
    return APP_URL . "app/img/productos/" . $imagen;
    }
    /* Obtener lista de inventario */
    public function obtenerInventario() {
        $consulta = $this->listarInventario();
        return $consulta->fetchAll();
    }

    /* Procesar ajuste de stock */
    public function ajustarStock() {
        $inventario_id = (int)($_POST['inventario_id'] ?? 0);
        $stock_actual  = (float)($_POST['stock_actual'] ?? 0);
        $stock_minimo  = (float)($_POST['stock_minimo'] ?? 0);
        $und_medida    = $this->limpiarCadena($_POST['und_medida'] ?? 'unidad');

        if ($inventario_id > 0) {
            $this->actualizarStock($inventario_id, $stock_actual, $stock_minimo, $und_medida);
        }

        header("Location: " . APP_URL . "inventario");
        exit();
    }
    /* Listar usuarios */
    public function obtenerUsuarios() {
        $consulta = $this->listarUsuarios();
        return $consulta->fetchAll();
    }

    /* Listar roles */
    public function obtenerRoles() {
        $consulta = $this->listarRoles();
        return $consulta->fetchAll();
    }

    /* Guardar o actualizar usuario */
    public function guardarUsuario() {
        $id       = (int)($_POST['usuario_id'] ?? 0);
        $nombre   = $this->limpiarCadena($_POST['nombre_completo'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $rol_id   = (int)($_POST['rol_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'Activo';
        $password = trim($_POST['password'] ?? '');

        if (empty($nombre) || empty($email) || $rol_id <= 0) {
            return ["tipo" => "error", "texto" => "Nombre, correo y rol son obligatorios."];
        }

        if ($id === 0) {
            // Nuevo usuario requiere contraseña
            if (empty($password)) {
                return ["tipo" => "error", "texto" => "La contraseña es obligatoria para nuevos usuarios."];
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $this->registrarUsuario([
                'nombre'   => $nombre,
                'email'    => $email,
                'password' => $passwordHash,
                'rol_id'   => $rol_id,
                'estado'   => $estado
            ]);
        } else {
            // Actualizar usuario (contraseña opcional)
            $passwordHash = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : null;
            $this->actualizarUsuario([
                'id'       => $id,
                'nombre'   => $nombre,
                'email'    => $email,
                'password' => $passwordHash,
                'rol_id'   => $rol_id,
                'estado'   => $estado
            ]);
        }

        header("Location: " . APP_URL . "usuarios");
        exit();
    }

    /* Cambiar estado activo/inactivo */
    public function alternarEstadoUsuario() {
        $id = (int)($_POST['usuario_id'] ?? 0);
        $estadoActual = trim($_POST['estado_actual'] ?? '');

        // Evitar que el usuario logueado se desactive a sí mismo
        if ($id === (int)($_SESSION['id_usuario'] ?? 0)) {
            return;
        }

        if ($id > 0) {
            $nuevoEstado = (strtolower($estadoActual) === 'activo') ? 'Inactivo' : 'Activo';
            $this->cambiarEstadoUsuario($id, $nuevoEstado);
        }

        header("Location: " . APP_URL . "usuarios");
        exit();
    }
    /* Obtener lista de mesas disponibles */
    public function obtenerMesasReserva() {
        $consulta = $this->listarMesasReserva();
        return $consulta->fetchAll();
    }

    /* Procesar el registro de la reserva */
    public function registrarReservaM() {
        $nombre_cliente    = $this->limpiarCadena($_POST['nombre_cliente'] ?? '');
        $telefono          = $this->limpiarCadena($_POST['telefono'] ?? '');
        $mesa_id           = (int)($_POST['mesa_id'] ?? 0);
        $cantidad_personas = (int)($_POST['cantidad_personas'] ?? 1);
        $fecha_reserva     = trim($_POST['fecha_reserva'] ?? '');
        $hora_inicio       = trim($_POST['hora_inicio'] ?? '');
        $observaciones     = $this->limpiarCadena($_POST['observaciones'] ?? '');

        // Validación de campos clave
        if (empty($nombre_cliente) || $mesa_id <= 0 || empty($fecha_reserva) || empty($hora_inicio)) {
            return ["tipo" => "error", "texto" => "Nombre, mesa, fecha y hora son obligatorios."];
        }

        // Calcular hora de fin aproximada (2 horas de turno por defecto)
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

        $this->registrarReserva($datos);

        header("Location: " . APP_URL . "admin");
        exit();
    }
    /* Controlador para cambiar el estado de la reserva */
    public function cambiarEstadoReservaC() {
        $reserva_id   = (int)($_POST['reserva_id'] ?? 0);
        $nuevo_estado = $this->limpiarCadena($_POST['nuevo_estado'] ?? '');

        if ($reserva_id > 0 && !empty($nuevo_estado)) {
            $this->cambiarEstadoReserva($reserva_id, $nuevo_estado);
        }

        header("Location: " . APP_URL . "admin");
        exit();
    }
}