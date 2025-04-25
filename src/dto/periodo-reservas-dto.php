<?php

    class PeriodoReservasDto {
        
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

        public function setFechaInicio($fechaInicio) {
            $this->fechaInicio = $fechaInicio;
        }

        public function setFechaFin($fechaFin) {
            $this->fechaFin = $fechaFin;
        }

        // EN TODOS LOS DTOs se debe hacer un toArray(), para que se pueda enviar al cliente
        public function toArray() {
            return [
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin
            ];
        }        
        
    }

?>