<?php
namespace app\controllers;

use app\models\userModel;

class userController extends userModel {

    public function actualizarPerfil() {
        $id = $_SESSION['id_usuario'] ?? 0;
        $nombre = $this->limpiarCadena($_POST['nombre_completo'] ?? '');
        $clave1 = trim($_POST['nueva_clave'] ?? '');
        $clave2 = trim($_POST['confirmar_clave'] ?? '');

        if ($id <= 0 || empty($nombre)) {
            return [
                "tipo"  => "error",
                "texto" => "El nombre completo no puede estar vacío."
            ];
        }


        $this->actualizarUsuario($id, $nombre);
        $_SESSION['nombre'] = $nombre;

        if (!empty($clave1) || !empty($clave2)) {
            if ($clave1 !== $clave2) {
                return [
                    "tipo"  => "error",
                    "texto" => "Las contraseñas no coinciden."
                ];
            }

            if (strlen($clave1) < 6) {
                return [
                    "tipo"  => "error",
                    "texto" => "La nueva contraseña debe tener al menos 6 caracteres."
                ];
            }

            $claveHash = password_hash($clave1, PASSWORD_BCRYPT);
            $this->actualizarClave($id, $claveHash);
        }

        return [
            "tipo"  => "exito",
            "texto" => "Perfil actualizado correctamente."
        ];
    }
}