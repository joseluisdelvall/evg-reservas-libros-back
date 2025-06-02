<?php

    include '../src/repository/periodo-reservas-repository.php';

    class PeriodoReservasService {
        
        private $periodoReservasRepository;

        public function __construct() {
            $this->periodoReservasRepository = new PeriodoReservasRepository();
        }

        public function getPeriodoActual() {
            return $this->periodoReservasRepository->getPeriodoActual();
        }

        /**
         * Actualiza el período de reservas
         * 
         * @param array $periodo Datos del nuevo período de reservas
         * @return PeriodoReservasEntity Nuevo período de reservas
         * @throws Exception Si las fechas no son válidas o no se puede actualizar el período
         */
        public function updatePeriodoReservas($periodo) {
            // Validar que las fechas sean válidas
            if (!isset($periodo['fechaInicio']) || !isset($periodo['fechaFin'])) {
                throw new Exception('Las fechas de inicio y fin son requeridas');
            }

            // Validar el formato de las fechas
            $fechaInicio = date('Y-m-d', strtotime($periodo['fechaInicio']));
            $fechaFin = date('Y-m-d', strtotime($periodo['fechaFin']));

            if ($fechaInicio === false || $fechaFin === false) {
                throw new Exception('El formato de las fechas no es válido');
            }

            // Validar que la fecha de inicio sea menor que la fecha de fin
            if (strtotime($fechaInicio) >= strtotime($fechaFin)) {
                throw new Exception('La fecha de inicio debe ser menor que la fecha de fin');
            }

            // Validar que las fechas no sean anteriores a la fecha actual
            if (strtotime($fechaInicio) < strtotime(date('Y-m-d'))) {
                throw new Exception('La fecha de inicio no puede ser anterior a la fecha actual');
            }

            return $this->periodoReservasRepository->updatePeriodoReservas($fechaInicio, $fechaFin);
        }
    }
?>