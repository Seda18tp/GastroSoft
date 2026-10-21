<?php
namespace app\models;

class loginModel extends mainModel {

    /* Obtener datos del usuario por email */
    protected function obtenerUsuario($email) {
        $sql = $this->ejecutarConsulta(
            "SELECT usuario_id, usuario_nombre, usuario_email, usuario_clave, usuario_rol, usuario_estado 
             FROM usuarios 
             WHERE usuario_email = :email LIMIT 1",
            [":email" => $email]
        );
        return $sql;
    }
}