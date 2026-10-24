<?php
namespace app\models;

class viewsModel {
    // Obtener vista lista blanca
    protected function obtenerVistasM($vista) {
        $listaBlanca = [
            "admin", "mesero", "cocinero",
            "caja", "miPerfil", "notiMesero", "configSalon",
            "registrarGasto", "cerrarCaja", "historialPedido",
            "notiCocinero", "estadisticas", "carta", "inventario",
            "usuarios", "nuevaReserva", "nuevoPlato", "editarPlato"
        ];

        if (in_array($vista, $listaBlanca)) {
            // Resolver la ruta absoluta a la raíz del proyecto
            $rutaArchivo = dirname(__DIR__, 2) . "/app/views/content/" . $vista . "-view.php";

            if (is_file($rutaArchivo)) {
                $contenido = $rutaArchivo;
            } else {
                $contenido = "404";
            }
        } elseif ($vista == "login" || $vista == "index") {
            $contenido = "login";
        } else {
            $contenido = "404";
        }

        return $contenido;
    }
}