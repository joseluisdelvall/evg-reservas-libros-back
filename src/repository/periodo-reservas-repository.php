<?php

    include '../conexionTmpBD/conexion.php';

    class PeriodoReservasRepository {
        
        public function __construct() {}

        public function getPeriodoActual() {
            // Solo tiene 1 registro que lo podemos obtener o modificar
            $sql = "SELECT * FROM periodo_reservas";
            $resultado = $conexion->query($sql);

            if($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            } else {
                return null;
            }
        }


    }
?>