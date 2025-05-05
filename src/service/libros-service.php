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

        /**
         * Obtiene los libros de un curso específico
         * 
         * @param int $idCurso ID del curso
         * @return array Lista de libros del curso
         */
        public function getLibrosByCurso($idCurso) {
            if (!is_numeric($idCurso)) {
                throw new Exception("El ID del curso debe ser un valor numérico");
            }
            
            return $this->librosRepository->getLibrosByCurso($idCurso);
        }

        /**
         * Crea un nuevo libro
         * 
         * @param array $data Datos del libro
         * @return Libro|null Libro creado o null si no se pudo crear
         */
        public function addLibro($data) {
            try {
                // Validar los datos de entrada
                if (empty($data['nombre']) || empty($data['isbn']) || empty($data['editorial']) || empty($data['precio'])) {
                    throw new Exception("Faltan datos obligatorios: nombre, ISBN, editorial o precio.");
                }

                $editorial = new EditorialEntity($data['editorial']['idEditorial']);
        
                // Convertir el DTO a una entidad
                $libroEntity = new LibroEntity(
                    null, // ID se generará automáticamente
                    $data['nombre'],
                    $data['isbn'],
                    $editorial,
                    $data['precio'],
                    $data['stock'] ?? 0,
                    1 // Estado activo por defecto
                );
        
                // Llamar al repositorio para agregar el libro
                $libroEntity = $this->librosRepository->addLibro($libroEntity);

                return new LibroDto(
                    $libroEntity->getId(),
                    $libroEntity->getNombre(),
                    $libroEntity->getIsbn(),
                    $libroEntity->getEditorial(),
                    $libroEntity->getPrecio(),
                    $libroEntity->getEstado()
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