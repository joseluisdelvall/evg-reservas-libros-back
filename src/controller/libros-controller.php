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
                return response('error', 'No se han encontrado libros');
            }

            $librosDto = array_map(function($libro) {
                $editorialDto = new EditorialMinDto($libro->getEditorial()->getId(), $libro->getEditorial()->getNombre());
                return new LibroDto($libro->getId(), $libro->getNombre(), $libro->getIsbn(), $editorialDto, $libro->getPrecio(), $libro->getEstado());
            }, $libros);

            return response('success', 'Libros obtenidos correctamente', array_map(function($dto) { 
                return $dto->toArray(); 
            }, $librosDto));
        }
    
        /**
         * Obtiene un libro por su ID
         * 
         * @param int $id ID del libro a obtener
         * @return array Respuesta con el estado y los datos del libro
         */
        public function getLibro($id) {
            try {
                // Obtener el libro por su ID
                $libro = $this->librosService->getLibro($id);
                
                // Verificar si se encontró el libro
                if(!$libro) {
                    return response('error', 'No se ha encontrado el libro con ID: ' . $id);
                }
                
                // Crear el DTO de la editorial
                $editorialDto = new EditorialMinDto(
                    $libro->getEditorial()->getId(), 
                    $libro->getEditorial()->getNombre()
                );
                
                // Crear el DTO del libro
                $libroDto = new LibroDto(
                    $libro->getId(),
                    $libro->getNombre(),
                    $libro->getIsbn(),
                    $editorialDto,
                    $libro->getPrecio(),
                    $libro->getEstado()
                );
                
                // Devolver el libro encontrado como DTO
                return response('success', 'Libro obtenido correctamente', $libroDto->toArray());
            } catch (\Exception $e) {
                // Registrar el error
                error_log("Error en getLibro: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al obtener el libro: ' . $e->getMessage());
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
                return response('success', 'Libro agregado correctamente', $result);
            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }
        }

        /**
         * Actualiza un libro existente
         * 
         * @param int $id ID del libro a actualizar
         * @return array Respuesta con el estado y los datos del libro actualizado
         */
        public function updateLibro($id) {
            try {
                // Obtener los datos de la solicitud
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validar que se recibieron datos
                if (!$data) {
                    return response('error', 'No se recibieron datos para actualizar el libro');
                }
                
                // Actualizar el libro
                $result = $this->librosService->updateLibro($id, $data);
                
                // Verificar si se encontró y actualizó el libro
                if (!$result) {
                    return response('error', 'No se ha encontrado el libro con ID: ' . $id);
                }
                
                return response('success', 'Libro actualizado correctamente', $result);
            } catch (\Exception $e) {
                // Registrar el error
                error_log("Error en updateLibro: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al actualizar el libro: ' . $e->getMessage());
            }
        }

    }

?>