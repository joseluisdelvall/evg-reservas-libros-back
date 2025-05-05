<?php

    require_once '../src/service/cursos-service.php';
    require_once '../src/dto/curso-dto.php';
    require_once '../src/utils/response.php';

    class CursosController {

        private $cursosService;

        public function __construct() {
            // Inicializar el servicio
            $this->cursosService = new CursosService();
        }

        /**
         * Obtiene todos los cursos
         * 
         * @return array Respuesta con el estado y los datos de los cursos
         */
        public function getCursos() {
            
            $cursos = $this->cursosService->getCursos();

            if(!$cursos) {
                return [
                    'status' => 'error',
                    'message' => 'No se han encontrado cursos'
                ];
            }

            $cursosDto = array_map(function($curso) {
                return new CursoDto(
                    $curso->getId(),
                    $curso->getNombre(),
                    $curso->getEtapa()
                );
            }, $cursos);

            // Convertir cada DTO a array antes de enviar la respuesta
            $cursosArray = array_map(function($dto) {
                return $dto->toArray();
            }, $cursosDto);

            return response('success', 'Cursos obtenidos correctamente.', $cursosArray);
        }

    }

?>