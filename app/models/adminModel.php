<?php
namespace app\models;

class adminModel extends mainModel {

    public function obtenerMetricas() {
        $sqlVentas = "SELECT COALESCE(SUM(total), 0) AS total_ventas 
                      FROM pedido 
                      WHERE fecha::date = CURRENT_DATE 
                        AND estado::text = 'Pagado'";
        $ventas = $this->ejecutarConsulta($sqlVentas)->fetch()['total_ventas'] ?? 0;

        $sqlMesas = "SELECT COUNT(*) AS mesas_ocupadas 
                     FROM mesa 
                     WHERE estado::text = 'Ocupada' OR estado::text = 'Precuenta'";
        $mesas = $this->ejecutarConsulta($sqlMesas)->fetch()['mesas_ocupadas'] ?? 0;

        $sqlPedidos = "SELECT COUNT(*) AS total_pedidos 
                       FROM pedido 
                       WHERE fecha::date = CURRENT_DATE";
        $pedidos = $this->ejecutarConsulta($sqlPedidos)->fetch()['total_pedidos'] ?? 0;

        $sqlStock = "SELECT COUNT(*) AS alertaStock 
                     FROM inventario 
                     WHERE stock_actual <= stock_minimo";
        $alertasStock = $this->ejecutarConsulta($sqlStock)->fetch()['alertaStock'] ?? 0;

        return [
            'ventas_dia'     => $ventas,
            'mesas_ocupadas' => $mesas,
            'total_pedidos'  => $pedidos,
            'alertas_stock'  => $alertasStock
        ];
    }

    public function listarReservasDia() {
        $sql = "SELECT 
                    r.id,
                    r.nombre_cliente,
                    r.telefono,
                    r.cantidad_personas,
                    r.fecha_reserva,
                    r.hora_inicio,
                    r.hora_fin,
                    r.estado,
                    r.observaciones,
                    m.numero_mesa
                FROM reserva r
                INNER JOIN mesa m ON r.mesa_id = m.id
                WHERE r.fecha_reserva = CURRENT_DATE
                ORDER BY r.hora_inicio ASC";

        return $this->ejecutarConsulta($sql);
    }

    public function obtenerVentasMetodo() {
        $sql = "SELECT 
                    metodo_pago, 
                    SUM(monto) AS total_monto
                FROM pago
                WHERE fecha::date = CURRENT_DATE
                GROUP BY metodo_pago
                ORDER BY total_monto DESC";

        return $this->ejecutarConsulta($sql);
    }

    public function obtenerTopProductos($limite = 5) {
        $limite = (int)$limite;
        $sql = "SELECT 
                    prod.nombre AS producto_nombre,
                    SUM(dp.cantidad) AS total_vendido
                FROM detalle_pedido dp
                INNER JOIN producto prod ON dp.producto_id = prod.id
                INNER JOIN pedido p ON dp.pedido_id = p.id
                WHERE p.fecha::date = CURRENT_DATE
                  AND LOWER(TRIM(p.estado::text)) IN ('pagado', 'entregado', 'listo')
                GROUP BY prod.id, prod.nombre
                ORDER BY total_vendido DESC
                LIMIT $limite";

        return $this->ejecutarConsulta($sql);
    }

    public function listarCategorias() {
        $sql = "SELECT id, nombre FROM categoria ORDER BY nombre ASC";
        return $this->ejecutarConsulta($sql);
    }

    public function listarProductosCarta($categoria_id = 0) {
        $categoria_id = (int)$categoria_id;

        if ($categoria_id > 0) {
            $sql = "SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_venta, p.img_url, p.estado, c.nombre AS categoria_nombre
                    FROM producto p
                    LEFT JOIN categoria c ON p.categoria_id = c.id
                    WHERE p.categoria_id = :cat_id
                    ORDER BY p.id DESC";
            return $this->ejecutarConsulta($sql, [':cat_id' => $categoria_id]);
        }

        $sql = "SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_venta, p.img_url, p.estado, c.nombre AS categoria_nombre
                FROM producto p
                LEFT JOIN categoria c ON p.categoria_id = c.id
                ORDER BY p.id DESC";
        return $this->ejecutarConsulta($sql);
    }

    public function cambiarEstadoProducto($producto_id, $nuevo_estado) {
        $sql = "UPDATE producto SET estado = :estado::estado_generico WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':estado' => $nuevo_estado,
            ':id'     => (int)$producto_id
        ]);
    }

    public function obtenerProductoId($id) {
        $sql = "SELECT id, categoria_id, nombre, descripcion, precio_venta, img_url, estado 
                FROM producto 
                WHERE id = :id 
                LIMIT 1";
        return $this->ejecutarConsulta($sql, [':id' => (int)$id]);
    }

    public function registrarProducto($datos) {
        $sql = "INSERT INTO producto (categoria_id, nombre, descripcion, precio_venta, img_url, estado) 
                VALUES (:categoria_id, :nombre, :descripcion, :precio_venta, :img_url, :estado::estado_generico)";
        return $this->ejecutarConsulta($sql, [
            ':categoria_id' => $datos['categoria_id'],
            ':nombre'       => $datos['nombre'],
            ':descripcion'  => $datos['descripcion'],
            ':precio_venta' => $datos['precio_venta'],
            ':img_url'      => $datos['img_url'],
            ':estado'       => $datos['estado']
        ]);
    }

    public function actualizarProducto($datos) {
        $sql = "UPDATE producto 
                SET categoria_id = :categoria_id, 
                    nombre       = :nombre, 
                    descripcion  = :descripcion, 
                    precio_venta = :precio, 
                    img_url      = :imagen, 
                    estado       = :estado::estado_generico 
                WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':categoria_id' => $datos['categoria_id'],
            ':nombre'       => $datos['nombre'],
            ':descripcion'  => $datos['descripcion'],
            ':precio'       => $datos['precio_venta'],
            ':imagen'       => $datos['img_url'],
            ':estado'       => $datos['estado'],
            ':id'           => (int)$datos['id']
        ]);
    }

    public function listarInventario() {
        $sql = "SELECT 
                    i.id AS inventario_id,
                    i.producto_id,
                    p.nombre AS producto_nombre,
                    i.und_medida,
                    i.stock_minimo,
                    i.stock_actual,
                    i.actualizado_en
                FROM inventario i
                INNER JOIN producto p ON i.producto_id = p.id
                ORDER BY i.stock_actual ASC";

        return $this->ejecutarConsulta($sql);
    }

    public function actualizarStock($inventario_id, $stock_actual, $stock_minimo, $und_medida) {
        $sql = "UPDATE inventario 
                SET stock_actual   = :stock_actual, 
                    stock_minimo   = :stock_minimo, 
                    und_medida     = :und_medida, 
                    actualizado_en = CURRENT_TIMESTAMP 
                WHERE id = :id";

        return $this->ejecutarConsulta($sql, [
            ':stock_actual' => $stock_actual,
            ':stock_minimo' => $stock_minimo,
            ':und_medida'   => $und_medida,
            ':id'           => (int)$inventario_id
        ]);
    }

    public function listarUsuarios() {
        $sql = "SELECT 
                    u.id, 
                    u.nombre_completo, 
                    u.email, 
                    u.rol_id, 
                    r.nombre AS rol_nombre, 
                    u.estado 
                FROM usuario u
                INNER JOIN rol r ON u.rol_id = r.id
                ORDER BY u.id DESC";
        return $this->ejecutarConsulta($sql);
    }

    public function listarRoles() {
        $sql = "SELECT id, nombre FROM rol ORDER BY id ASC";
        return $this->ejecutarConsulta($sql);
    }

    public function registrarUsuario($datos) {
        $sql = "INSERT INTO usuario (rol_id, nombre_completo, email, password, estado) 
                VALUES (:rol_id, :nombre, :email, :password, :estado::estado_generico)";

        return $this->ejecutarConsulta($sql, [
            ':rol_id'   => $datos['rol_id'] ?? 1,
            ':nombre'   => $datos['nombre'] ?? '',
            ':email'    => $datos['email'] ?? '',
            ':password' => $datos['password'] ?? '',
            ':estado'   => $datos['estado'] ?? 'Activo'
        ]);
    }

    public function actualizarUsuario($datos) {
        $nombre = $datos['nombre'] ?? '';
        $email  = $datos['email'] ?? '';
        $rol_id = $datos['rol_id'] ?? 1;
        $estado = $datos['estado'] ?? 'Activo';
        $id     = (int)($datos['id'] ?? 0);
        $pass   = trim($datos['password'] ?? '');

        if (!empty($pass)) {
            $sql = "UPDATE usuario 
                    SET nombre_completo = :nombre, 
                        email           = :email, 
                        password        = :password, 
                        rol_id          = :rol_id, 
                        estado          = :estado::estado_generico 
                    WHERE id = :id";
            return $this->ejecutarConsulta($sql, [
                ':nombre'   => $nombre,
                ':email'    => $email,
                ':password' => $pass,
                ':rol_id'   => $rol_id,
                ':estado'   => $estado,
                ':id'       => $id
            ]);
        }

        $sql = "UPDATE usuario 
                SET nombre_completo = :nombre, 
                    email           = :email, 
                    rol_id          = :rol_id, 
                    estado          = :estado::estado_generico 
                WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':nombre' => $nombre,
            ':email'  => $email,
            ':rol_id' => $rol_id,
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }

    public function cambiarEstadoUsuario($id, $nuevoEstado) {
        $sql = "UPDATE usuario SET estado = :estado::estado_generico WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':estado' => $nuevoEstado,
            ':id'     => (int)$id
        ]);
    }

    public function verificarEmailExiste($email, $id = 0) {
        $id = (int)$id;
        if ($id > 0) {
            $sql = "SELECT id FROM usuario WHERE email = :email AND id != :id LIMIT 1";
            $stmt = $this->ejecutarConsulta($sql, [':email' => $email, ':id' => $id]);
        } else {
            $sql = "SELECT id FROM usuario WHERE email = :email LIMIT 1";
            $stmt = $this->ejecutarConsulta($sql, [':email' => $email]);
        }
        return $stmt->rowCount() > 0;
    }

    public function listarMesasReserva() {
        $sql = "SELECT id, numero_mesa, ubicacion, capacidad, estado 
                FROM mesa 
                ORDER BY numero_mesa ASC";
        return $this->ejecutarConsulta($sql);
    }

    public function registrarReserva($datos) {
        $sql = "INSERT INTO reserva (
                    mesa_id, 
                    nombre_cliente, 
                    telefono, 
                    cantidad_personas, 
                    fecha_reserva, 
                    hora_inicio, 
                    hora_fin, 
                    estado, 
                    observaciones
                ) VALUES (
                    :mesa_id, 
                    :nombre_cliente, 
                    :telefono, 
                    :cantidad_personas, 
                    :fecha_reserva::date, 
                    :hora_inicio::time, 
                    :hora_fin::time, 
                    :estado::estado_reserva, 
                    :observaciones
                )";

        return $this->ejecutarConsulta($sql, [
            ':mesa_id'           => (int)$datos['mesa_id'],
            ':nombre_cliente'    => $datos['nombre_cliente'],
            ':telefono'          => $datos['telefono'],
            ':cantidad_personas' => (int)$datos['cantidad_personas'],
            ':fecha_reserva'     => $datos['fecha_reserva'],
            ':hora_inicio'       => $datos['hora_inicio'],
            ':hora_fin'          => $datos['hora_fin'],
            ':estado'            => $datos['estado'],
            ':observaciones'     => $datos['observaciones']
        ]);
    }

    public function cambiarEstadoReserva($reserva_id, $nuevo_estado) {
        $sql = "UPDATE reserva SET estado = :estado::estado_reserva WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':estado' => $nuevo_estado,
            ':id'     => (int)$reserva_id
        ]);
    }
}