<?php
namespace app\models;

class loginModel extends mainModel {

    // Obtener datos email 
    protected function obtenerUsuario($email) {
        $sql = $this->ejecutarConsulta(
            "SELECT u.id, u.nombre_completo, u.email, u.password, u.estado, u.foto_perfil, r.nombre AS rol_nombre 
            FROM usuario u 
            INNER JOIN rol r ON u.rol_id = r.id 
            WHERE u.email = :email LIMIT 1",
            [":email" => $email]
        );
        return $sql;
    }
}
?>