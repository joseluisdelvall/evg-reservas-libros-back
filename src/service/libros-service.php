<?php

    require_once '../src/repository/libros-repository.php';
    require_once '../src/entity/libro-entity.php';
    require_once '../src/entity/editorial-entity.php';

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
         * Obtiene un libro por su ID
         * 
         * @param int $id ID del libro a obtener
         * @return LibroEntity|null Libro encontrado o null si no existe
         * @throws Exception Si hay errores en la operación
         */
        public function getLibro($id) {
            try {
                // Validar que el ID sea un número entero positivo
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID del libro debe ser un número entero positivo.");
                }
                
                // Llamar al repositorio para obtener el libro
                return $this->librosRepository->getLibro($id);
                
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log("Error en LibrosService::getLibro: " . $e->getMessage());
                // Propagar la excepción al controlador
                throw $e;
            }
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

                $etapa = new EtapaEntity($data['etapa']['id']);

                // Convertir el DTO a una entidad
                $libroEntity = new LibroEntity(
                    null, // ID se generará automáticamente
                    $data['nombre'],
                    $data['isbn'],
                    $editorial,
                    $data['precio'],
                    $data['stock'] ?? 0,
                    1, // Estado activo por defecto
                    $etapa
                );
        
                // Llamar al repositorio para agregar el libro
                $this->librosRepository->addLibro($libroEntity);

                return new LibroDto(
                    $libroEntity->getId(),
                    $libroEntity->getNombre(),
                    $libroEntity->getIsbn(),
                    $libroEntity->getEditorial(),
                    $libroEntity->getPrecio(),
                    $libroEntity->getEstado(),
                    $libroEntity->getEtapa()
                );
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        public function updateLibro($id, $data) {
            try {
                // Validar que el ID sea un número entero positivo
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID del libro debe ser un número entero positivo.");
                }

                $editorial = new EditorialEntity(
                    $data['editorial']['idEditorial']
                );

                $etapa = new EtapaEntity(
                    $data['etapa']['id']
                );

                $libroEntity = $this->getLibro($id);

                if (!$libroEntity) {
                    throw new Exception("No se ha encontrado el libro con ID: " . $id);
                }

                $libroEntity->setNombre($data['nombre']);
                $libroEntity->setIsbn($data['isbn']);
                $libroEntity->setEditorial($editorial);
                $libroEntity->setPrecio($data['precio']);
                $libroEntity->setEtapa($etapa);
                
                // Llamar al repositorio para actualizar el libro
                $this->librosRepository->updateLibro($id, $libroEntity);

                return new LibroDto(
                    $libroEntity->getId(),
                    $libroEntity->getNombre(),
                    $libroEntity->getIsbn(),
                    $libroEntity->getEditorial(),
                    $libroEntity->getPrecio(),
                    $libroEntity->getEstado(),
                    $libroEntity->getEtapa()
                );

            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        public function cambiarEstadoLibro($id) {
            try {
                // Validar que el ID sea un número entero positivo
                if (!is_numeric($id) || $id <= 0) {
                    throw new Exception("El ID del libro debe ser un número entero positivo.");
                }

                $libroEntity = $this->librosRepository->getLibro($id);

                if (!$libroEntity) {
                    throw new Exception("No se ha encontrado el libro con ID: " . $id);
                }

                $libroEntity->setEstado(!$libroEntity->getEstado());

                $libroEntity = $this->librosRepository->updateLibro($id, $libroEntity);

                return new LibroDto(
                    $libroEntity->getId(),
                    $libroEntity->getNombre(),
                    $libroEntity->getIsbn(),
                    $libroEntity->getEditorial(),
                    $libroEntity->getPrecio(),
                    $libroEntity->getEstado(),
                    $libroEntity->getEtapa()
                );

            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        /**
         * Actualiza el estado de un libro en una reserva específica a "Anulado"
         * 
         * @param int $idLibro ID del libro
         * @param int $idReserva ID de la reserva
         * @return bool true si se actualizó correctamente
         * @throws Exception Si hay errores en la operación
         */
        public function updateEstadoLibroReserva($idLibro, $idReserva) {
            try {
                // Validar que los IDs sean números enteros positivos
                if (!is_numeric($idLibro) || $idLibro <= 0 || !is_numeric($idReserva) || $idReserva <= 0) {
                    throw new Exception("Los IDs deben ser números enteros positivos.");
                }

                // Verificar que el libro existe
                $libroEntity = $this->librosRepository->getLibro($idLibro);
                if (!$libroEntity) {
                    throw new Exception("No se ha encontrado el libro con ID: " . $idLibro);
                }

                // Actualizar el estado del libro en la reserva
                return $this->librosRepository->updateEstadoLibroReserva($idLibro, $idReserva);

            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar el error al controlador
                throw $e;
            }
        }

        /**
         * Obtiene los libros de una etapa específica
         * 
         * @param int $idEtapa ID de la etapa
         * @return array Lista de libros de la etapa
         */
        public function getLibrosByEtapa($idEtapa) {
            if (!is_numeric($idEtapa)) {
                throw new Exception("El ID de la etapa debe ser un valor numérico");
            }
            return $this->librosRepository->getLibrosByEtapa($idEtapa);
        }
    }

?>