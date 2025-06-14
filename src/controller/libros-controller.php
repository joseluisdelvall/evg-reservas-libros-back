<?php

    require_once '../src/service/libros-service.php';
    require_once '../src/dto/libro-dto.php';
    require_once '../src/dto/editorial-min-dto.php';
    require_once '../src/dto/etapa-dto.php';
    require_once '../src/utils/response.php';
    require_once '../src/middleware/auth-middleware.php';

    class LibrosController {

        private $librosService;
        private $authMiddleware;

        public function __construct() {
            // Inicializar el servicio
            $this->librosService = new LibrosService();
            $this->authMiddleware = new AuthMiddleware();
        }

        /**
         * Método privado para verificar la autenticación
         * @return bool|void Retorna true si está autenticado o termina la ejecución si no lo está
         */
        private function verificarAuth() {
            $resultado = $this->authMiddleware->verificarAutenticacion();
            if ($resultado !== true) {
                echo json_encode($resultado);
                exit;
            }
            return true;
        }

        /**
         * Obtiene todos los libros
         * 
         * @return array Respuesta con el estado y los datos de los libros
         */
        public function getLibros() {
            
            $libros = $this->librosService->getLibros();

            if(!$libros) {
                return response('error', 'No se han encontrado libros', null, 404);
            }

            $librosDto = array_map(function($libro) {
                $editorialDto = new EditorialMinDto($libro->getEditorial()->getId(), $libro->getEditorial()->getNombre());
                $etapaDto = new EtapaDto($libro->getEtapa()->getId(), $libro->getEtapa()->getNombre());
                return new LibroDto(
                    $libro->getId(), 
                    $libro->getNombre(), 
                    $libro->getIsbn(), 
                    $editorialDto, 
                    $libro->getPrecio(), 
                    $libro->getEstado(), 
                    $etapaDto,
                    $libro->getStock()
                );
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
                    return response('error', 'No se ha encontrado el libro con ID: ' . $id, null, 404);
                }
                
                // Crear el DTO de la editorial
                $editorialDto = new EditorialMinDto(
                    $libro->getEditorial()->getId(), 
                    $libro->getEditorial()->getNombre()
                );

                $etapaDto = new EtapaDto(
                    $libro->getEtapa()->getId(),
                    $libro->getEtapa()->getNombre()
                );
                
                // Crear el DTO del libro
                $libroDto = new LibroDto(
                    $libro->getId(),
                    $libro->getNombre(),
                    $libro->getIsbn(),
                    $editorialDto,
                    $libro->getPrecio(),
                    $libro->getEstado(),
                    $etapaDto
                );
                
                // Devolver el libro encontrado como DTO
                return response('success', 'Libro obtenido correctamente', $libroDto->toArray());
            } catch (\Exception $e) {
                // Registrar el error
                error_log("Error en getLibro: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al obtener el libro: ' . $e->getMessage(), null, 500);
            }
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

                    $etapaDto = new EtapaDto(
                        $libro->getEtapa()->getId(),
                        $libro->getEtapa()->getNombre()
                    );

                    return new LibroDto(
                        $libro->getId(),
                        $libro->getNombre(),
                        $libro->getIsbn(),
                        $editorialDto,
                        $libro->getPrecio(),
                        $libro->getEstado(),
                        $etapaDto
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
            // Verificar autenticación antes de proceder
            $this->verificarAuth();

            // Obtener los datos de la solicitud
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $this->librosService->addLibro($data);
                return response('success', 'Libro agregado correctamente', $result);
            } catch (Exception $e) {
                return response('error', $e->getMessage(), null, 500);
            }
        }

        /**
         * Actualiza un libro existente
         * 
         * @param int $id ID del libro a actualizar
         * @return array Respuesta con el estado y los datos del libro actualizado
         */
        public function updateLibro($id) {
            // Verificar autenticación antes de proceder
            $this->verificarAuth();

            try {
                // Obtener los datos de la solicitud
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validar que se recibieron datos
                if (!$data) {
                    return response('error', 'No se recibieron datos para actualizar el libro', null, 400);
                }
                
                // Actualizar el libro
                $result = $this->librosService->updateLibro($id, $data);
                
                // Verificar si se encontró y actualizó el libro
                if (!$result) {
                    return response('error', 'No se ha encontrado el libro con ID: ' . $id, null, 404);
                }
                
                return response('success', 'Libro actualizado correctamente', $result);
            } catch (Exception $e) {
                // Registrar el error
                error_log("Error en updateLibro: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al actualizar el libro: ' . $e->getMessage(), null, 500);
            }
        }

        /**
         * Cambia el estado de un libro
         * 
         * @param int $id ID del libro a cambiar el estado
         * @return array Respuesta con el estado y el mensaje de la operación
         */
        public function cambiarEstadoLibro($id) {
            // Verificar autenticación antes de proceder
            $this->verificarAuth();

            try {
                
                // Cambiar el estado del libro
                $result = $this->librosService->cambiarEstadoLibro($id);

                return response('success', 'Estado del libro cambiado correctamente', $result);
            } catch (Exception $e) {
                // Registrar el error
                error_log("Error en cambiarEstadoLibro: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al cambiar el estado del libro: ' . $e->getMessage(), null, 500);
            }
        }

        /**
         * Obtiene los libros de una etapa específica
         * 
         * @param int $idEtapa ID de la etapa
         * @return array Respuesta con el estado y los datos de los libros
         */
        public function getLibrosByEtapa($idEtapa) {
            try {
                $libros = $this->librosService->getLibrosByEtapa($idEtapa);

                if (empty($libros)) {
                    return response('error', 'No se encontraron libros para la etapa especificada');
                }

                $librosDto = array_map(function($libro) {
                    $editorialDto = new EditorialMinDto(
                        $libro->getEditorial()->getId(),
                        $libro->getEditorial()->getNombre()
                    );
                    $etapaDto = new EtapaDto(
                        $libro->getEtapa()->getId(),
                        $libro->getEtapa()->getNombre()
                    );
                    return new LibroDto(
                        $libro->getId(),
                        $libro->getNombre(),
                        $libro->getIsbn(),
                        $editorialDto,
                        $libro->getPrecio(),
                        $libro->getEstado(),
                        $etapaDto
                    );
                }, $libros);

                $libroArray = array_map(function($dto) {
                    return $dto->toArray();
                }, $librosDto);

                return response('success', 'Libros de la etapa obtenidos correctamente', $libroArray);

            } catch (Exception $e) {
                return response('error', $e->getMessage());
            }
        }

        /**
         * Actualiza el estado de un libro en una reserva específica a "Anulado"
         * 
         * @param array $params Array con los parámetros idLibro e idReserva
         * @return array Respuesta con el estado y el mensaje de la operación
         */
        public function updateEstadoLibroReserva($params) {
            // Verificar autenticación antes de proceder
            $this->verificarAuth();

            try {
                // Validar que se recibieron los parámetros necesarios
                if (!isset($params['idLibro']) || !isset($params['idReserva'])) {
                    return response('error', 'Faltan parámetros requeridos: idLibro e idReserva', null, 400);
                }

                // Actualizar el estado del libro en la reserva
                $result = $this->librosService->updateEstadoLibroReserva($params['idLibro'], $params['idReserva']);

                return response('success', 'Estado del libro en la reserva actualizado correctamente', $result);
            } catch (Exception $e) {
                // Registrar el error
                error_log("Error en updateEstadoLibroReserva: " . $e->getMessage());
                
                // Devolver mensaje de error
                return response('error', 'Error al actualizar el estado del libro en la reserva: ' . $e->getMessage(), null, 500);
            }
        }
    }

?>