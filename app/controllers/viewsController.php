<?php
    namespace app\controllers;
    use app\models\viewsModel;

    class viewsController extends viewsModel{
        //obtener vistas
        public function obtenerVistas($vista){
            if ($vista!="") {
                $respuesta=$this->obtenerVistasM($vista);
            }else{
                $respuesta="login";
            }
            return $respuesta;
        }
    }
?>