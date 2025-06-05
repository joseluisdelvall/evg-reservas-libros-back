<?php
    
    require_once '../src/service/periodo-reservas-service.php';
    require_once '../src/dto/periodo-reservas-dto.php';
    require_once '../src/middleware/auth-middleware.php';

    class PeriodoReservasController {

        private $periodoReservasService;
        private $authMiddleware;

        public function __construct() {
            // Inicializar el servicio
            $this->periodoReservasService = new PeriodoReservasService();
            $this->authMiddleware = new AuthMiddleware();
        }

        /**
         * Método privado para verificar la autenticación
         * @return bool|void Retorna true si está autenticado o termina la ejecución si no lo está
         */
        private function verificarAuth() {
            $resultado = $this->authMiddleware->verificarAutenticacion();
            if ($resultado !== true) {
                echo json_encode($resultado);
                exit;
            }
            return true;
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

        /**
         * Actualiza el período de reservas
         * 
         * @return array Respuesta con el estado y los datos del período
         */
        public function updatePeriodoReservas() {
            // Verificar autenticación antes de proceder
            $this->verificarAuth();

            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    return response('error', 'No se recibieron datos para actualizar el período', null, 400);
                }
                
                $result = $this->periodoReservasService->updatePeriodoReservas($data);

                if(!$result) {
                    return response('error', 'No se ha podido actualizar el período', null, 400);
                }
                
                return response('success', 'Período actualizado correctamente', $result);
            } catch (Exception $e) {
                error_log("Error en updatePeriodoReservas: " . $e->getMessage());
                return response('error', 'Error al actualizar el período: ' . $e->getMessage(), null, 500);
            }
        }   
    }
?>