<?php

    // NO SE COMO FUNCIONA AQUI
    class PeriodoReservasEntity {
        
        private $fechaInicio;
        private $fechaFin;

        public function __construct($fechaInicio, $fechaFin) {
            $this->fechaInicio = $fechaInicio;
            $this->fechaFin = $fechaFin;
        }

        public function getFechaInicio() {
            return $this->fechaInicio;
        }

        public function getFechaFin() {
            return $this->fechaFin;
        }
    }
?>