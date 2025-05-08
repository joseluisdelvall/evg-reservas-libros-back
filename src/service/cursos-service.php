<?php

    require_once '../src/repository/cursos-repository.php';

    class CursosService {

        private $cursosRepository;

        public function __construct() {
            $this->cursosRepository = new CursosRepository();
        }

        /**
         * Obtiene todos los cursos
         * 
         * @return array Lista de cursos
         */
        public function getCursos() {
            
            return $this->cursosRepository->getCursos();

        }
    }

?>