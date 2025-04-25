<?php

    

    class PeriodoReservasRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getPeriodoActual() {
            // Solo tiene 1 registro que lo podemos obtener o modificar
            $sql = "SELECT * FROM periodo_reservas";
            $resultado = $this->conexion->query($sql);

            if($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            } else {
                return null;
            }
        }


    }
?>