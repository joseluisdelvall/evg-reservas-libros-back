<?php
    
    require_once '../src/service/periodo-reservas-service.php';
    require_once '../src/dto/periodo-reservas-dto.php';

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

            $periodoDto = new PeriodoReservasDto($periodo->getFechaInicio(), $periodo->getFechaFin());

            return [
                'status' => 'success',
                'data' => $periodoDto->toArray()
            ];
        }

        public function updatePeriodoReservas() {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    return response('error', 'No se recibieron datos para actualizar el período', null, 400);
                }
                
                $result = $this->periodoReservasService->updatePeriodoReservas($data);
                
                return response('success', 'Período actualizado correctamente', $result);
            } catch (Exception $e) {
                error_log("Error en updatePeriodoReservas: " . $e->getMessage());
                return response('error', 'Error al actualizar el período: ' . $e->getMessage(), null, 500);
            }
        }   
    }
?>