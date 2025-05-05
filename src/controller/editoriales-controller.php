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

            // Crear los DTOs para cada editorial
            $editorialesDto = array_map(function($editorial) {
                return new EditorialDto(
                    $editorial->getId(),
                    $editorial->getNombre(),
                    $editorial->getTelefono(),
                    $editorial->getCorreo(),
                    $editorial->getEstado()
                );
            }, $editoriales);

            // Convertir cada DTO a array antes de enviar la respuesta
            $editorialesArray = array_map(function($dto) {
                return $dto->toArray();
            }, $editorialesDto);

            return response('success', 'Editoriales obtenidas correctamente.', $editorialesArray);
        }

        /**
         * Agrega una nueva editorial
         * 
         * @return array Respuesta con el estado y el mensaje de la operación
         */
        public function addEditorial() {
            // Obtener los datos de la solicitud
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $this->editorialesService->addEditorial($data);
                return response('success', 'Editorial agregada correctamente.', $result);
            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }
        }
    }

?>