<?php
    
    require_once '../src/entity/editorial-entity.php';
    require_once '../src/repository/editoriales-repository.php';
    
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
         * Agrega una nueva editorial
         * 
         * @param EditorialDto $editorialDto Datos de la editorial a agregar
         * @return bool Resultado de la operación
         */
        public function addEditorial($data) {
            try {
                // Validar los datos de entrada
                if (empty($data['nombre']) || empty($data['telefono']) || empty($data['correo'])) {
                    throw new Exception("Faltan datos obligatorios: nombre, teléfono o correo.");
                }
        
                // Convertir el DTO a una entidad
                $editorialEntity = new EditorialEntity(
                    null, // ID se generará automáticamente
                    $data['nombre'],
                    $data['telefono'],
                    $data['correo'],
                    1 // Estado activo por defecto
                );
        
                // Llamar al repositorio para agregar la editorial
                $editorialEntity = $this->editorialesRepository->addEditorial($editorialEntity);

                return new EditorialDto(
                    $editorialEntity->getId(),
                    $editorialEntity->getNombre(),
                    $editorialEntity->getTelefono(),
                    $editorialEntity->getCorreo(),
                    $editorialEntity->getEstado()
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