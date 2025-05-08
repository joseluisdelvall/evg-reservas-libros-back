<?php
    
    require_once '../src/entity/editorial-entity.php';
    require_once '../src/repository/editoriales-repository.php';
    require_once '../src/dto/editorial-dto.php';
    
    class EditorialesService {

        private $editorialesRepository;
    
        public function __construct() {
            // Inicializar el repositorio
            $this->editorialesRepository = new EditorialesRepository();
        }
    
        /**
         * Obtiene todas las editoriales
         * 
         * @return array Lista de editoriales
         */
        public function getEditoriales() {
            return $this->editorialesRepository->getEditoriales();
        }

        /** 
         * Obtiene una editorial por su ID
         * 
         * @param int $id ID de la editorial
         * @return EditorialEntity|null Entidad de la editorial o null si no existe
         */
        public function getEditorial($id) {
            try {
                // Validar que el ID sea un número entero
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID de la editorial debe ser un número entero positivo.");
                }

                // Llamar al repositorio para obtener la editorial
                $editorial = $this->editorialesRepository->getEditorial($id);

                // Devolver la entidad o null si no existe
                return $editorial;
            } catch (Exception $e) {
                // Propagar el error al controlador
                throw $e;
            }
        }   

        /**
         * Agrega una nueva editorial
         * 
         * @param array $data Datos de la editorial a agregar
         * @return EditorialDto Datos de la editorial agregada
         * @throws Exception Si hay errores en los datos
         */
        public function addEditorial($data) {
            try {
                // Validar los datos de entrada
                if (empty($data['nombre'])) {
                    throw new Exception("El nombre de la editorial es obligatorio.");
                }
        
                // Validar teléfonos
                $telefonos = isset($data['telefonos']) ? $data['telefonos'] : [];
                if (!is_array($telefonos)) {
                    throw new Exception("El campo 'telefonos' debe ser un array.");
                }
                if (count($telefonos) > 3) {
                    throw new Exception("Una editorial no puede tener más de 3 teléfonos.");
                }
                if (count($telefonos) < 1) {
                    throw new Exception("La editorial debe tener al menos un teléfono.");
                }
                
                // Validar correos
                $correos = isset($data['correos']) ? $data['correos'] : [];
                if (!is_array($correos)) {
                    throw new Exception("El campo 'correos' debe ser un array.");
                }
                if (count($correos) > 3) {
                    throw new Exception("Una editorial no puede tener más de 3 correos.");
                }
                
                // Validar formato de correos si existen
                foreach ($correos as $correo) {
                    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("El correo '$correo' no tiene un formato válido.");
                    }
                }

                // Obtener los valores individuales para teléfonos y correos
                $telefono1 = isset($telefonos[0]) ? $telefonos[0] : null;
                $telefono2 = isset($telefonos[1]) ? $telefonos[1] : null;
                $telefono3 = isset($telefonos[2]) ? $telefonos[2] : null;
                
                $correo1 = isset($correos[0]) ? $correos[0] : null;
                $correo2 = isset($correos[1]) ? $correos[1] : null;
                $correo3 = isset($correos[2]) ? $correos[2] : null;

                // Convertir a una entidad
                $editorialEntity = new EditorialEntity(
                    null, // ID se generará automáticamente
                    $data['nombre'],
                    $telefono1,
                    $telefono2,
                    $telefono3,
                    $correo1,
                    $correo2,
                    $correo3,
                    1 // Estado activo por defecto
                );
        
                // Llamar al repositorio para agregar la editorial
                $editorialEntity = $this->editorialesRepository->addEditorial($editorialEntity);

                // Convertir la entidad a DTO
                return new EditorialDto(
                    $editorialEntity->getId(),
                    $editorialEntity->getNombre(),
                    $editorialEntity->getTelefono1(),
                    $editorialEntity->getTelefono2(),
                    $editorialEntity->getTelefono3(),
                    $editorialEntity->getCorreo1(),
                    $editorialEntity->getCorreo2(),
                    $editorialEntity->getCorreo3(),
                    $editorialEntity->getEstado()
                );
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        /**
         * Actualiza una editorial existente
         * 
         * @param int $id ID de la editorial a actualizar
         * @param array $data Datos de la editorial a actualizar
         * @return EditorialDto Datos de la editorial actualizada
         * @throws Exception Si hay errores en los datos
         */
        public function updateEditorial($id, $data) {
            try {
                // Validar que el ID sea un número entero
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID de la editorial debe ser un número entero positivo.");
                }

                $editorial = $this->getEditorial($id);
                if (!$editorial) {
                    throw new Exception("No se encontró la editorial con ID $id.");
                }

                // Actualizar los datos de la editorial
                $editorial->setNombre($data['nombre']);
                
                // Manejar teléfonos - comprobar si existen antes de asignar
                $telefonos = isset($data['telefonos']) ? $data['telefonos'] : [];
                $editorial->setTelefono1(isset($telefonos[0]) ? $telefonos[0] : null);
                $editorial->setTelefono2(isset($telefonos[1]) ? $telefonos[1] : null);
                $editorial->setTelefono3(isset($telefonos[2]) ? $telefonos[2] : null);
                
                // Manejar correos - comprobar si existen antes de asignar
                $correos = isset($data['correos']) ? $data['correos'] : [];
                $editorial->setCorreo1(isset($correos[0]) ? $correos[0] : null);
                $editorial->setCorreo2(isset($correos[1]) ? $correos[1] : null);
                $editorial->setCorreo3(isset($correos[2]) ? $correos[2] : null);
                
                // Llamar al repositorio para actualizar la editorial
                $editorial = $this->editorialesRepository->updateEditorial($editorial);

                // Convertir la entidad a DTO
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
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        /**
         * Desactiva una editorial existente
         * 
         * @param int $id ID de la editorial a desactivar
         * @return EditorialDto Datos de la editorial actualizada
         * @throws Exception Si hay errores en los datos
         */
        public function cambiarEstadoEditorial($id) {
            try {
                // Validar que el ID sea un número entero
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID de la editorial debe ser un número entero positivo.");
                }

                // Obtener la editorial actual
                $editorial = $this->getEditorial($id);
                if (!$editorial) {
                    throw new Exception("No se encontró la editorial con ID $id.");
                }   

                // Actualizar el estado a inactivo
                $editorial->setEstado(!$editorial->getEstado());
                
                // Guardar los cambios
                $editorial = $this->editorialesRepository->updateEditorial($editorial); 

                // Convertir la entidad a DTO
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
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }
    }
    
?>