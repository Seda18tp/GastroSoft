<?php
namespace app\models;

class userModel extends mainModel {

    public function obtenerUsuario($id) {
        $sql = "SELECT id, nombre_completo, email, password, estado 
                FROM usuario 
                WHERE id = :id 
                LIMIT 1";
        return $this->ejecutarConsulta($sql, [':id' => $id]);
    }

    public function actualizarUsuario($id, $nombre) {
        $sql = "UPDATE usuario SET nombre_completo = :nombre WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':nombre' => $nombre,
            ':id'     => $id
        ]);
    }

    public function actualizarClave($id, $claveHash) {
        $sql = "UPDATE usuario SET password = :password WHERE id = :id";
        return $this->ejecutarConsulta($sql, [
            ':password' => $claveHash,
            ':id'       => $id
        ]);
    }
}