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
                return response('error', 'No se han encontrado editoriales', null, 404);            
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
            try {
                $editorial = $this->editorialesService->getEditorial($id);

                if(!$editorial) {
                    return response('error', 'No se ha encontrado la editorial', null, 404);
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

                return response('success', 'Editorial obtenida correctamente.', $editorialDto->toArray());
            } catch (Exception $e) {
                return response('error', $e->getMessage(), null, 500);
            }
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
                    return response('error', 'El nombre de la editorial es obligatorio.', null, 400);
                }
                
                // Verificar teléfonos
                if (!isset($data['telefonos']) || !is_array($data['telefonos'])) {
                    return response('error', 'El campo telefonos debe ser un array.', null, 400);
                }
                if (count($data['telefonos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 teléfonos.', null, 400);
                }
                
                // Verificar correos
                if (!isset($data['correos'])) {
                    $data['correos'] = [];
                }
                if (!is_array($data['correos'])) {
                    return response('error', 'El campo correos debe ser un array.', null, 400);
                }
                if (count($data['correos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 correos.', null, 400);
                }
                
                // Nueva validación: al menos un teléfono o un correo no vacío
                if (count(array_filter($data['telefonos'])) < 1 && count(array_filter($data['correos'])) < 1) {
                    return response('error', 'Debe proporcionar al menos un teléfono o un correo.', null, 400);
                }

                $result = $this->editorialesService->addEditorial($data);
                return response('success', 'Editorial agregada correctamente.', $result->toArray());
            } catch (Exception $e) {
                return response('error', $e->getMessage(), null, 500);
            }
        }

        /**
         * Actualiza una editorial existente
         * 
         * @param int $id ID de la editorial a actualizar
         * @return array Respuesta con el estado y el mensaje de la operación
         */
        public function updateEditorial($id) {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Verificar que los datos estén completos
                if (empty($data['nombre'])) {
                    return response('error', 'El nombre de la editorial es obligatorio.', null, 400);
                }
                
                // Verificar teléfonos
                if (!isset($data['telefonos']) || !is_array($data['telefonos'])) {
                    return response('error', 'El campo telefonos debe ser un array.', null, 400);
                }
                if (count($data['telefonos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 teléfonos.', null, 400);
                }
                
                // Verificar correos
                if (!isset($data['correos']) || !is_array($data['correos'])) {
                    return response('error', 'El campo correos debe ser un array.', null, 400);
                }

                if (count($data['correos']) > 3) {
                    return response('error', 'Una editorial no puede tener más de 3 correos.', null, 400);
                }

                // Nueva validación: al menos un teléfono o un correo no vacío
                if (count(array_filter($data['telefonos'])) < 1 && count(array_filter($data['correos'])) < 1) {
                    return response('error', 'Debe proporcionar al menos un teléfono o un correo.', null, 400);
                }

                $result = $this->editorialesService->updateEditorial($id, $data);
                return response('success', 'Editorial actualizada correctamente.', $result->toArray());
            } catch (Exception $e) {
                return response('error', $e->getMessage(), null, 500);
            }
        }

    /**
     * Desactiva una editorial existente
     * 
     * @param int $id ID de la editorial a desactivar
     * @return array Respuesta con el estado y el mensaje de la operación
     */
    public function cambiarEstadoEditorial($id) {
        try {
            $result = $this->editorialesService->cambiarEstadoEditorial($id);
            return response('success', 'Estado de la editorial actualizado correctamente.', $result->toArray());
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
        
        /**
         * Obtiene las editoriales que tienen libros reservados pendientes de pedir
         * 
         * @return array Respuesta con el estado y los datos de las editoriales con libros pendientes
         */
        public function getEditorialesConLibrosPendientes() {
            try {
                $editoriales = $this->editorialesService->getEditorialesConLibrosPendientes();

                if(empty($editoriales)) {
                    return response('success', 'No se encontraron editoriales con libros pendientes de pedir.', []);            
                }

                // Convertir cada DTO a array antes de enviar la respuesta
                $editorialesArray = array_map(function($dto) {
                    return $dto->toArray();
                }, $editoriales);

                return response('success', 'Editoriales con libros pendientes obtenidas correctamente.', $editorialesArray);
            } catch (Exception $e) {
                return response('error', $e->getMessage(), null, 500);
            }
        }
        
        /**
         * Obtiene los libros reservados pendientes de pedir para una editorial específica.
         *
         * @param int $idEditorial ID de la editorial
         * @return array Respuesta con el estado y la lista de libros pendientes
         */
        public function getLibrosPendientesPorEditorial($idEditorial) {
            try {
                $librosPendientes = $this->editorialesService->getLibrosPendientesPorEditorial($idEditorial);

                if (empty($librosPendientes)) {
                    return response('success', 'No se encontraron libros pendientes de pedir para esta editorial.', []);
                }

                $librosArray = array_map(function($dto) {
                    return $dto->toArray();
                }, $librosPendientes);

                return response('success', 'Libros pendientes de pedir obtenidos correctamente.', $librosArray);
            } catch (Exception $e) {
                // Manejar excepciones específicas como "No se encontró la editorial"
                if (strpos($e->getMessage(), "No se encontró la editorial") !== false) {
                    return response('error', $e->getMessage(), null, 404);
                }
                return response('error', $e->getMessage(), null, 500);
            }
        }
        
    }

?>