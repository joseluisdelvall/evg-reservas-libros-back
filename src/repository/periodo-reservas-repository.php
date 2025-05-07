<?php

    include '../src/entity/periodo-reservas-entity.php';

    class PeriodoReservasRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getPeriodoActual() {
            // Solo tiene 1 registro que lo podemos obtener o modificar
            $sql = "SELECT * FROM PERIODO_RESERVAS";
            $resultado = $this->conexion->query($sql);

            if($resultado->num_rows > 0) {
                $periodo = $resultado->fetch_assoc();
                return new PeriodoReservasEntity($periodo['fechaIni'], $periodo['fechaFin']);
            } else {
                return null;
            }
        }


    }
?>