<?php

    require_once '../src/service/libros-service.php';
    require_once '../src/dto/libro-dto.php';
    require_once '../src/dto/editorial-min-dto.php';
    require_once '../src/utils/response.php';

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
    
        /**
         * Obtiene los libros de un curso específico
         * 
         * @param int $idCurso ID del curso
         * @return array Respuesta con el estado y los datos de los libros
         */
        public function getLibrosByCurso($idCurso) {
            try {
                $libros = $this->librosService->getLibrosByCurso($idCurso);
                
                if (empty($libros)) {
                    return response('error', 'No se encontraron libros para el curso especificado');
                }
                
                $librosDto = array_map(function($libro) {
                    $editorialDto = new EditorialMinDto(
                        $libro->getEditorial()->getId(),
                        $libro->getEditorial()->getNombre()
                    );
                    
                    return new LibroDto(
                        $libro->getId(),
                        $libro->getNombre(),
                        $libro->getIsbn(),
                        $editorialDto,
                        $libro->getPrecio(),
                        $libro->getEstado()
                    );
                }, $libros);
                
                // Convertir DTO a array
                $libroArray = array_map(function($dto) {
                    return $dto->toArray();
                }, $librosDto);
                
                return response('success', 'Libros del curso obtenidos correctamente', $libroArray);
                
            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }
        }

        /**
         * Crea un nuevo libro
         * 
         * @return array Respuesta con el estado y el mensaje de la operación
         */
        public function addLibro() {
            // Obtener los datos de la solicitud
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $this->librosService->addLibro($data);
                return response('success', 'Libro agregado correctamente.', $result);
            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }

        }

    }

?>