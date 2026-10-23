<?php
    namespace app\models;
    class viewsModel{
        /*--Modelo obtener vista--*/
        protected function obtenerVistasModelo($vista){
            $listaBlanca=["admin","mesero","cocinero",
            "caja","miPerfil","notiMesero","configSalon",
            "registrarGasto","cerrarCaja","historialPedido",
            "notiCocinero","estadisticas","carta","inventario",
            "usuarios","nuevaReserva","nuevoPlato","editarPlato"];

            if (in_array($vista,$listaBlanca)) {
                if (is_file("./app/views/content/".$vista."-view.php")) {
                    $contenido="./app/views/content/".$vista."-view.php";
                }else{
                    $contenido="404";
                }
            }elseif($vista=="login" || $vista=="index"){
                $contenido="login";
            }else{
                $contenido="404";
            }
            return $contenido;
        }
    }
?>