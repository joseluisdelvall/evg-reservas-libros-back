<?php

    require_once '../src/repository/libros-repository.php';

    class LibrosService {

        private $librosRepository;

        public function __construct() {
            $this->librosRepository = new LibrosRepository();
        }

        /**
         * Obtiene todos los libros
         * 
         * @return array Lista de libros
         */
        public function getLibros() {
            
            return $this->librosRepository->getLibros();

        }

        public function addLibro($libro) {
            return $this->librosRepository->addLibro($libro);
        }
    }

?>