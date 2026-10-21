<?php
    namespace app\models;
    use \PDO;

    if (file_exists(__DIR__."/../../config/app.php")) {
        require_once __DIR__."/../../config/app.php";
    }

    class mainModel {
        private $server = DB_SERVER;
        private $db     = DB_NAME;
        private $user   = DB_USER;
        private $pass   = DB_PASS;

        /* Conectar a la base de datos */
        protected function conectar() {
            $conexion = new PDO("mysql:host=".$this->server.";dbname=".$this->db.";charset=utf8mb4", $this->user, $this->pass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        }

        /* Ejecutar consultas simples */
        public function ejecutarConsulta($consulta, $params = []) {
            $sql = $this->conectar()->prepare($consulta);
            $sql->execute($params);
            return $sql;
        }

        /* Limpiar cadenas de texto */
        public function limpiarCadena($cadena) {
            $palabras = [
                "<script>", "</script>", "<script src", "<script type=", 
                "SELECT * FROM", "DELETE FROM", "INSERT INTO", "DROP TABLE", 
                "DROP DATABASE", "TRUNCATE TABLE", "SHOW TABLES", "SHOW DATABASES", 
                "<?php", "?>", "--", "^", "==", ";", "::", "'or", "1=1"
            ];
            $cadena = trim((string)$cadena);
            $cadena = stripslashes($cadena);
            foreach ($palabras as $palabra) {
                $cadena = str_ireplace($palabra, "", $cadena);
            }
            return trim(stripslashes($cadena));
        }

        /* Insertar datos genéricos */
        protected function guardarDatos($tabla, $datos) {
            $query = "INSERT INTO $tabla (";
            $campos = "";
            $marcadores = "";
            $valores = [];

            foreach ($datos as $item) {
                $campos .= $item["campo_nombre"].",";
                $marcadores .= ":".$item["campo_nombre"].",";
                $valores[":".$item["campo_nombre"]] = $item["campo_valor"];
            }

            $campos = rtrim($campos, ",");
            $marcadores = rtrim($marcadores, ",");
            $query .= $campos.") VALUES (".$marcadores.")";

            $sql = $this->conectar()->prepare($query);
            $sql->execute($valores);
            return $sql;
        }

        /* Actualizar datos genéricos */
        protected function actualizarDatos($tabla, $datos, $condicion) {
            $query = "UPDATE $tabla SET ";
            $valores = [];

            foreach ($datos as $item) {
                $query .= $item["campo_nombre"]."=:".$item["campo_nombre"].",";
                $valores[":".$item["campo_nombre"]] = $item["campo_valor"];
            }

            $query = rtrim($query, ",");
            $query .= " WHERE ".$condicion["condicion_campo"]."=:condicion_valor";
            $valores[":condicion_valor"] = $condicion["condicion_valor"];

            $sql = $this->conectar()->prepare($query);
            $sql->execute($valores);
            return $sql;
        }

        /* Eliminar registros */
        protected function eliminarRegistro($tabla, $campo, $id) {
            $sql = $this->conectar()->prepare("DELETE FROM $tabla WHERE $campo = :id");
            $sql->execute([":id" => $id]);
            return $sql;
        }
    }