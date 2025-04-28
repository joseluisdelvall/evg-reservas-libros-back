<?php

    require_once '../src/service/editoriales-service.php';
    require_once '../src/dto/editorial-dto.php';

    class EditorialesController {

        private $editorialesService;

        public function __construct() {
            // Inicializar el servicio
            $this->editorialesService = new EditorialesService();
        }

        /**
         * Obtiene todas las editoriales
         * 
         * @return array Respuesta con el estado y los datos de las editoriales
         */
        public function getEditoriales() {
            
            $editoriales = $this->editorialesService->getEditoriales();

            if(!$editoriales) {
                return [
                    'status' => 'error',
                    'message' => 'No se han encontrado libros'
                ];
            }

            $editorialesDto = array_map(function($editorial) {
                return new EditorialDto(
                    $editorial->getId(),
                    $editorial->getNombre(),
                    $editorial->getTelefono(),
                    $editorial->getCorreo(),
                    $editorial->getEstado()
                );
            }, $editoriales);

            return [
                'status' => 'success',
                'data' => array_map(function($dto) { return $dto->toArray(); }, $editorialesDto)
            ];
        }

    }

?>