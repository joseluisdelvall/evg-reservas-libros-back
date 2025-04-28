<?php

    require_once '../src/service/libros-service.php';
    require_once '../src/dto/libro-dto.php';
    require_once '../src/dto/editorial-min-dto.php';

    class LibrosController {

        private $librosService;

        public function __construct() {
            // Inicializar el servicio
            $this->librosService = new LibrosService();
        }

        /**
         * Obtiene todos los libros
         * 
         * @return array Respuesta con el estado y los datos de los libros
         */
        public function getLibros() {
            
            $libros = $this->librosService->getLibros();

            if(!$libros) {
                return [
                    'status' => 'error',
                    'message' => 'No se han encontrado libros'
                ];
            }

            $librosDto = array_map(function($libro) {
                $editorialDto = new EditorialMinDto($libro->getEditorial()->getId(), $libro->getEditorial()->getNombre());
                return new LibroDto($libro->getId(), $libro->getNombre(), $libro->getIsbn(), $editorialDto, $libro->getPrecio(), $libro->getEstado());
            }, $libros);

            return [
                'status' => 'success',
                'data' => array_map(function($dto) { return $dto->toArray(); }, $librosDto)
            ];
        }
    
        public function getLibrosById($id) {
            
        }

        public function addLibro() {

            // Obtener los datos del libro desde la solicitud
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar los datos recibidos
            if (!isset($data['nombre']) || !isset($data['isbn']) || !isset($data['idEditorial']) || !isset($data['precio'])) {
                return [
                    'status' => 'error',
                    'message' => 'Faltan datos requeridos'
                ];
            }

            // Crear el libro usando el servicio
            $libro = $this->librosService->addLibro($data['nombre'], $data['isbn'], $data['idEditorial'], $data['precio']);

            if ($libro) {
                return [
                    'status' => 'success',
                    'message' => 'Libro creado exitosamente',
                    'data' => $libro->toArray()
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Error al crear el libro'
                ];
            }

        }

    }

?>