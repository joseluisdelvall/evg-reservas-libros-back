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
                    'message' => 'No se han encontrado editoriales'
                ];
            }

            // Crear los DTOs para cada editorial
            $editorialesDto = array_map(function($editorial) {
                return new EditorialDto(
                    $editorial->getId(),
                    $editorial->getNombre(),
                    $editorial->getTelefono1(),
                    $editorial->getTelefono2(),
                    $editorial->getTelefono3(),
                    $editorial->getCorreo1(),
                    $editorial->getCorreo2(),
                    $editorial->getCorreo3(),
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
         * Obtiene una editorial por su ID
         * 
         * @param int $id ID de la editorial
         * @return array Respuesta con el estado y los datos de la editorial
         */
        public function getEditorial($id) {
            $editorial = $this->editorialesService->getEditorial($id);

            if(!$editorial) {
                return [
                    'status' => 'error',
                    'message' => 'No se ha encontrado la editorial'
                ];
            }

            $editorialDto = new EditorialDto(
                $editorial->getId(),
                $editorial->getNombre(),
                $editorial->getTelefono1(),
                $editorial->getTelefono2(),
                $editorial->getTelefono3(),
                $editorial->getCorreo1(),
                $editorial->getCorreo2(),
                $editorial->getCorreo3(),
                $editorial->getEstado()
            );

            return new response('success', 'Editorial obtenida correctamente.', $editorialDto->toArray());
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
                
                // Verificar que los datos estén completos
                if (empty($data['nombre'])) {
                    return response('error', 'El nombre de la editorial es obligatorio.');
                }
                
                // Verificar teléfonos
                if (!isset($data['telefonos']) || !is_array($data['telefonos'])) {
                    return response('error', 'El campo telefonos debe ser un array.');
                }
                if (count($data['telefonos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 teléfonos.');
                }
                if (count($data['telefonos']) < 1) {
                    return response('error', 'La editorial debe tener al menos un teléfono.');
                }
                
                // Verificar correos
                if (!isset($data['correos'])) {
                    $data['correos'] = [];
                }
                if (!is_array($data['correos'])) {
                    return response('error', 'El campo correos debe ser un array.');
                }
                if (count($data['correos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 correos.');
                }
                
                $result = $this->editorialesService->addEditorial($data);
                return response('success', 'Editorial agregada correctamente.', $result->toArray());
            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }
        }
    }

?>