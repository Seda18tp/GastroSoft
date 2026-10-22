<?php
namespace app\models;

class adminModel extends mainModel {

    public function obtenerMetricas() {
        //ventas del día (suma de pedidos pagados)
        $sqlVentas = "SELECT COALESCE(SUM(total), 0) AS total_ventas 
                      FROM pedido 
                      WHERE DATE(fecha) = CURDATE() 
                        AND estado IN ('Pagado')";
        $ventas = $this->ejecutarConsulta($sqlVentas)->fetch()['total_ventas'];

        //Mesas ocupadas
        $sqlMesas = "SELECT COUNT(*) AS mesas_ocupadas 
                     FROM mesa 
                     WHERE estado = 'Ocupada'";
        $mesas = $this->ejecutarConsulta($sqlMesas)->fetch()['mesas_ocupadas'];

        //Pedidos hoy
        $sqlPedidos = "SELECT COUNT(*) AS total_pedidos 
                       FROM pedido 
                       WHERE DATE(fecha) = CURDATE()";
        $pedidos = $this->ejecutarConsulta($sqlPedidos)->fetch()['total_pedidos'];

        //Alertas Stock
        $sqlStock = "SELECT COUNT(*) AS alertaStock 
                     FROM inventario 
                     WHERE stock_actual <= stock_minimo";
        $alertasStock = $this->ejecutarConsulta($sqlStock)->fetch()['alertaStock'];

        return [
            'ventas_dia'     => $ventas,
            'mesas_ocupadas' => $mesas,
            'total_pedidos'  => $pedidos,
            'alertas_stock'  => $alertasStock
        ];
    }

    /* Listado de Reservas del Día */
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
                WHERE r.fecha_reserva = CURDATE()
                ORDER BY r.hora_inicio ASC";

        return $this->ejecutarConsulta($sql);
    }
    //Ventas por metodo
    public function obtenerVentasMetodo() {
    $sql = "SELECT 
                metodo_pago, 
                SUM(monto) AS total_monto
            FROM pago
            WHERE DATE(fecha) = CURDATE()
            GROUP BY metodo_pago
            ORDER BY total_monto DESC";

    return $this->ejecutarConsulta($sql);
    }

   //Top productos
    public function obtenerTopProductos($limite = 5) {
    $limite = (int)$limite;

    $sql = "SELECT 
                prod.nombre AS producto_nombre,
                SUM(dp.cantidad) AS total_vendido
            FROM detalle_pedido dp
            INNER JOIN producto prod ON dp.producto_id = prod.id
            INNER JOIN pedido p ON dp.pedido_id = p.id
            WHERE DATE(p.fecha) = CURDATE()
              AND LOWER(TRIM(p.estado)) IN ('pagado','entregado', 'listo')
            GROUP BY prod.id, prod.nombre
            ORDER BY total_vendido DESC
            LIMIT $limite";

    return $this->ejecutarConsulta($sql);
    }
    //Listar categorias
    public function listarCategorias() {
    $sql = "SELECT id, nombre FROM categoria ORDER BY nombre ASC";
    return $this->ejecutarConsulta($sql);
    }

    //Listar productos

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

    //Alternar estado
    public function cambiarEstadoProducto($producto_id, $nuevo_estado) {
    $sql = "UPDATE producto SET estado = :estado WHERE id = :id";
    return $this->ejecutarConsulta($sql, [
        ':estado' => $nuevo_estado,
        ':id'     => $producto_id
    ]);
    }
    // Obtener producto
    public function obtenerProductoId($id) {
    $sql = "SELECT id, categoria_id, nombre, descripcion, precio_venta, img_url, estado 
            FROM producto 
            WHERE id = :id 
            LIMIT 1";
    return $this->ejecutarConsulta($sql, [':id' => (int)$id]);
    }

    //Registrar nuevo producto
    public function registrarProducto($datos) {
    $sql = "INSERT INTO producto (categoria_id, nombre, descripcion, precio_venta, img_url, estado) 
            VALUES (:categoria_id, :nombre, :descripcion, :precio_venta, :img_url, :estado)";
    return $this->ejecutarConsulta($sql, [
        ':categoria_id' => $datos['categoria_id'],
        ':nombre'       => $datos['nombre'],
        ':descripcion'  => $datos['descripcion'],
        ':precio_venta' => $datos['precio_venta'],
        ':img_url'      => $datos['img_url'],
        ':estado'       => $datos['estado']
    ]);
    }

    // Actualizar producto existente
    public function actualizarProducto($datos) {
    $sql = "UPDATE producto 
            SET categoria_id = :categoria_id, 
                nombre = :nombre, 
                descripcion = :descripcion, 
                precio_venta = :precio, 
                img_url = :imagen, 
                estado = :estado 
            WHERE id = :id";
    return $this->ejecutarConsulta($sql, [
        ':categoria_id' => $datos['categoria_id'],
        ':nombre'       => $datos['nombre'],
        ':descripcion'  => $datos['descripcion'],
        ':precio'       => $datos['precio_venta'],
        ':imagen'       => $datos['img_url'],
        ':estado'       => $datos['estado'],
        ':id'           => $datos['id']
    ]);
    }
    /* Listar inventario completo con el nombre del producto */
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

    /* Actualizar valores de stock */
    public function actualizarStock($inventario_id, $stock_actual, $stock_minimo, $und_medida) {
        $sql = "UPDATE inventario 
                SET stock_actual = :stock_actual, 
                    stock_minimo = :stock_minimo, 
                    und_medida = :und_medida,
                    actualizado_en = NOW() 
                WHERE id = :id";

        return $this->ejecutarConsulta($sql, [
            ':stock_actual' => $stock_actual,
            ':stock_minimo' => $stock_minimo,
            ':und_medida'   => $und_medida,
            ':id'           => $inventario_id
        ]);
    }
    //Listar usuarios
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

    // Listar roles 
    public function listarRoles() {
        $sql = "SELECT id, nombre FROM rol ORDER BY id ASC";
        return $this->ejecutarConsulta($sql);
    }

    //Registrar nuevo usuario
    public function registrarUsuario($datos) {
        $sql = "INSERT INTO usuario (nombre_completo, email, password, rol_id, estado) 
                VALUES (:nombre, :email, :password, :rol_id, :estado)";
        return $this->ejecutarConsulta($sql, [
            ':nombre'   => $datos['nombre'],
            ':email'    => $datos['email'],
            ':password' => $datos['password'],
            ':rol_id'   => $datos['rol_id'],
            ':estado'   => $datos['estado']
        ]);
    }

    //Actualizar usuario existente
    public function actualizarUsuario($datos) {
        if (!empty($datos['password'])) {
            $sql = "UPDATE usuario 
                    SET nombre_completo = :nombre, 
                        email = :email, 
                        password = :password, 
                        rol_id = :rol_id, 
                        estado = :estado 
                    WHERE id = :id";
            return $this->ejecutarConsulta($sql, [
                ':nombre'   => $datos['nombre'],
                ':email'    => $datos['email'],
                ':password' => $datos['password'],
                ':rol_id'   => $datos['rol_id'],
                ':estado'   => $datos['estado'],
                ':id'       => $datos['id']
            ]);
        }

        $sql = "UPDATE usuario 
                SET nombre_completo = :nombre, 
                    email = :email, 
                    rol_id = :rol_id, 
                    estado = :estado 
                WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':nombre' => $datos['nombre'],
            ':email'  => $datos['email'],
            ':rol_id' => $datos['rol_id'],
            ':estado' => $datos['estado'],
            ':id'     => $datos['id']
        ]);
    }

    // cambiar estado
    public function cambiarEstadoUsuario($id, $nuevoEstado) {
        $sql = "UPDATE usuario SET estado = :estado WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':estado' => $nuevoEstado,
            ':id'     => $id
        ]);
    }
    // Listar mesas 
    public function listarMesasReserva() {
        $sql = "SELECT id, numero_mesa, ubicacion, capacidad, estado 
                FROM mesa 
                ORDER BY numero_mesa ASC";
        return $this->ejecutarConsulta($sql);
    }

    // Guardar la reserva
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
                    :fecha_reserva, 
                    :hora_inicio, 
                    :hora_fin, 
                    :estado, 
                    :observaciones
                )";

        return $this->ejecutarConsulta($sql, [
            ':mesa_id'           => $datos['mesa_id'],
            ':nombre_cliente'    => $datos['nombre_cliente'],
            ':telefono'          => $datos['telefono'],
            ':cantidad_personas' => $datos['cantidad_personas'],
            ':fecha_reserva'     => $datos['fecha_reserva'],
            ':hora_inicio'       => $datos['hora_inicio'],
            ':hora_fin'          => $datos['hora_fin'],
            ':estado'            => $datos['estado'],
            ':observaciones'     => $datos['observaciones']
        ]);
    }
    public function cambiarEstadoReserva($reserva_id, $nuevo_estado) {
    $sql = "UPDATE reserva SET estado = :estado WHERE id = :id";
    return $this->ejecutarConsulta($sql, [
        ':estado' => $nuevo_estado,
        ':id'     => (int)$reserva_id
    ]);
    }
}