<?php
namespace app\controllers;

use app\models\loginModel;

class loginController extends loginModel {

    public function iniciarSesionControlador() {
        // 1. Limpiar entradas
        $email = $this->limpiarCadena($_POST['usuario_email'] ?? '');
        $clave = trim($_POST['usuario_clave'] ?? '');

        if (empty($email) || empty($clave)) {
            return [
                "tipo"    => "error",
                "titulo"  => "Campos vacíos",
                "mensaje" => "Debe ingresar el correo y la contraseña."
            ];
        }

        // 2. Consultar el usuario en la BD
        $consulta = $this->obtenerUsuario($email);

        if ($consulta->rowCount() == 0) {
            return [
                "tipo"    => "error",
                "titulo"  => "Error de acceso",
                "mensaje" => "El correo electrónico no se encuentra registrado."
            ];
        }

        $usuario = $consulta->fetch(\PDO::FETCH_ASSOC);

        if (!password_verify($clave, $usuario['usuario_clave'])) {
            return [
                "tipo"    => "error",
                "titulo"  => "Error de acceso",
                "mensaje" => "Contraseña incorrecta."
            ];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['id']     = $usuario['usuario_id'];
        $_SESSION['nombre'] = $usuario['usuario_nombre'];
        $_SESSION['email']  = $usuario['usuario_email'];
        $_SESSION['rol']    = $usuario['usuario_rol'];

        $redireccion = "dashboard";
        switch ($usuario['usuario_rol']) {
            case 'Administrador':
                $redireccion = "<?php echo APP_URL; ?>admin/";
                break;
            case 'Cajero':
                $redireccion = "caja";
                break;
            case 'Mesero':
                $redireccion = "mesero";
                break;
            case 'Cocina':
                $redireccion = "cocinero";
                break;
        }

        return [
            "tipo"        => "exito",
            "titulo"      => "Bienvenido",
            "mensaje"     => "Acceso autorizado.",
            "redireccion" => $redireccion
        ];
    }
}