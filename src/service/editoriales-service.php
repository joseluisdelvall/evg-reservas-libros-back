<?php
    
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

    }
    
?>