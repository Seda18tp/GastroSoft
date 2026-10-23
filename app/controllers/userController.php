<?php
namespace app\controllers;

use app\models\userModel;

class userController extends userModel {

    public function actualizarPerfil() {
        $usuario_id = (int)($_SESSION['id_usuario'] ?? 0);
        $nombre     = $this->limpiarCadena($_POST['nombre_completo'] ?? '');
        $clave1     = trim($_POST['nueva_clave'] ?? '');
        $clave2     = trim($_POST['confirmar_clave'] ?? '');

        if ($usuario_id <= 0 || empty($nombre)) {
            return [
                "tipo"  => "error",
                "texto" => "El nombre completo no puede estar vacío."
            ];
        }

        // 1. Guardar la foto en app/img/perfil/
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['foto_perfil']['tmp_name'];
            $extension  = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $permitidos)) {
                // Ruta absoluta hacia app/img/perfil/
                $carpetaDestino = dirname(__DIR__) . '/img/perfil/';
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }

                $nuevoNombreArchivo = 'user_' . $usuario_id . '_' . time() . '.' . $extension;
                $destinoCompleto    = $carpetaDestino . $nuevoNombreArchivo;

                if (move_uploaded_file($archivoTmp, $destinoCompleto)) {
                    $this->actualizarFotoPerfil($usuario_id, $nuevoNombreArchivo);
                    $_SESSION['foto_perfil'] = $nuevoNombreArchivo;
                } else {
                    return [
                        "tipo"  => "error",
                        "texto" => "No se pudo guardar la imagen en app/img/perfil/."
                    ];
                }
            } else {
                return [
                    "tipo"  => "error",
                    "texto" => "Formato no válido. Solo JPG, PNG y WEBP."
                ];
            }
        }

        // 2. Actualizar nombre
        $this->actualizarUsuario($usuario_id, $nombre);
        $_SESSION['nombre'] = $nombre;

        // 3. Actualizar contraseña
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
                    "texto" => "La contraseña debe tener mínimo 6 caracteres."
                ];
            }
            $claveHash = password_hash($clave1, PASSWORD_BCRYPT);
            $this->actualizarClave($usuario_id, $claveHash);
        }

        return [
            "tipo"  => "exito",
            "texto" => "Perfil actualizado correctamente."
        ];
    }
}