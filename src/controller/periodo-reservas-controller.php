<?php

    include '../src/service/periodo-reservas-service.php';

    class PeriodoReservasController {

        private $periodoReservasService;

        public function __construct() {
            // Inicializar el servicio
            $this->periodoReservasService = new PeriodoReservasService();
        }

        /**
         * Obtiene el período actual de reservas
         * 
         * @return array Respuesta con el estado y los datos del período
         */
        public function getPeriodoReservas() {
            
            $periodo = $this->periodoReservasService->getPeriodoActual();

            if(!$periodo) {
                return [
                    'status' => 'error',
                    'message' => 'No se ha encontrado ningún período de reservas'
                ];
            }

            //$periodoDto = new PeriodoReservasDto($periodo);

            return [
                'status' => 'success',
                'data' => $periodo
            ];
        }

        public function updatePeriodoReservas() {
            return 'updatePeriodoReservas';
        }
    }
?>