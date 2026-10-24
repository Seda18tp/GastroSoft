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

        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['foto_perfil']['tmp_name'];
            $extension  = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $permitidos)) {

                if ($_FILES['foto_perfil']['size'] > 2 * 1024 * 1024) {
                    return [
                        "tipo"  => "error",
                        "texto" => "La imagen no debe superar los 2MB."
                    ];
                }

                $contenidoBinario = file_get_contents($archivoTmp);
                $tipoMime = ($extension === 'jpg') ? 'jpeg' : $extension;
                $imagenBase64 = 'data:image/' . $tipoMime . ';base64,' . base64_encode($contenidoBinario);


                $this->actualizarFotoPerfil($usuario_id, $imagenBase64);
                $_SESSION['foto_perfil'] = $imagenBase64;
            } else {
                return [
                    "tipo"  => "error",
                    "texto" => "Formato no válido. Solo JPG, PNG y WEBP."
                ];
            }
        }

        $this->actualizarUsuario($usuario_id, $nombre);
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