<?php

    require_once '../src/entity/libro-entity.php';
    require_once '../src/entity/editorial-entity.php';

    class LibrosRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getLibros() {
            // Obtener todos los libros
            $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo 
                    FROM LIBRO AS lb 
                    INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial";
            $resultado = $this->conexion->query($sql);
        
            if (!$resultado) {
                // Log the SQL error for debugging
                error_log("SQL Error: " . $this->conexion->error);
                return [];
            }
        
            if ($resultado->num_rows > 0) {
                $libros = [];
                while ($libro = $resultado->fetch_assoc()) {
                    // Crear la entidad Editorial
                    $editorial = new EditorialEntity(
                        $libro['idEditorial'],
                        $libro['editorialNombre']
                    );
                    // Crear la entidad Libro con la entidad Editorial
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'], // Usar el alias correcto
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo']
                    );
                }
                return $libros;
            } else {
                return [];
            }
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
                // Proteger contra SQL injection con prepared statement
                $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo 
                        FROM LIBRO AS lb 
                        INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial 
                        WHERE idLibro = ?";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
                }
                
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if (!$resultado) {
                    throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
                }
                
                if ($resultado->num_rows > 0) {
                    // Solo necesitamos un registro, ya que buscamos por ID único
                    $libroData = $resultado->fetch_assoc();
                    
                    // Crear entidad Editorial de la misma forma que en getLibros()
                    $editorial = new EditorialEntity(
                        $libroData['idEditorial'],
                        $libroData['editorialNombre']
                    );
                    
                    // Crear la entidad Libro igual que en getLibros()
                    return new LibroEntity(
                        $libroData['idLibro'],
                        $libroData['libroNombre'],
                        $libroData['ISBN'],
                        $editorial,
                        $libroData['precio'],
                        $libroData['stock'],
                        $libroData['activo']
                    );
                } else {
                    // No se encontró el libro con ese ID
                    return null;
                }
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log("Error en LibrosRepository::getLibro: " . $e->getMessage());
                // Propagar la excepción
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
            $sql = "SELECT l.idLibro, l.nombre AS libroNombre, l.ISBN, l.precio, l.stock, 
                        l.idEditorial, e.nombre AS editorialNombre, l.activo 
                    FROM LIBRO l
                    INNER JOIN EDITORIAL e ON l.idEditorial = e.idEditorial
                    INNER JOIN CURSO_LIBRO cl ON l.idLibro = cl.idLibro
                    WHERE cl.idCurso = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idCurso);
            $stmt->execute();
            
            $resultado = $stmt->get_result();
            
            if (!$resultado) {
                error_log("SQL Error: " . $this->conexion->error);
                return [];
            }
            
            $libros = [];
            if ($resultado->num_rows > 0) {
                while ($libro = $resultado->fetch_assoc()) {
                    $editorial = new EditorialEntity(
                        $libro['idEditorial'],
                        $libro['editorialNombre']
                    );
                    
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'],
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo']
                    );
                }
            }
            
            return $libros;
        }

        /**
         * Agrega un nuevo libro
         * 
         * @param LibroEntity $libro Entidad del libro a agregar
         * @return LibroEntity|null Libro creado o null si no se pudo crear
         */
        public function addLibro($libro) {
            try {
                $sql = "INSERT INTO LIBRO (nombre, ISBN, idEditorial, precio, stock, activo) VALUES (";
                $sql .= "'" . $libro->getNombre() . "', ";
                $sql .= "'" . $libro->getIsbn() . "', ";
                $sql .= "'" . $libro->getEditorial()->getId() . "', ";
                $sql .= "'" . $libro->getPrecio() . "', ";
                $sql .= "'" . $libro->getStock() . "', ";
                $sql .= "'" . $libro->getEstado() . "')";
        
                $resultado = $this->conexion->query($sql);
        
                if (!$resultado) {
                    throw new Exception("Error en la consulta SQL: " . $this->conexion->error);
                }
        
                if ($this->conexion->affected_rows > 0) {
                    $id = $this->conexion->insert_id;

                    // Recuperar el registro completo con la información de la editorial
                    $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo 
                            FROM LIBRO AS lb 
                            INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial 
                            WHERE idLibro = " . $id;
                    $resultado = $this->conexion->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        $libroData = $resultado->fetch_assoc();
                        $editorial = new EditorialEntity(
                            $libroData['idEditorial'],
                            $libroData['editorialNombre']
                        );
                        return new LibroEntity(
                            $libroData['idLibro'],
                            $libroData['libroNombre'],
                            $libroData['ISBN'],
                            $editorial,
                            $libroData['precio'],
                            $libroData['stock'],
                            $libroData['activo']
                        );
                    } else {
                        throw new Exception("No se pudo recuperar el registro recién insertado.");
                    }
                } else {
                    throw new Exception("No se afectaron filas en la base de datos.");
                }
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar la excepción para que el servicio la maneje
                throw $e;
            }
        }

        /**
         * Actualiza un libro existente
         * 
         * @param int $id ID del libro a actualizar
         * @param LibroEntity $libro Entidad del libro con los datos actualizados
         * @return LibroEntity|null Libro actualizado o null si no existe
         * @throws Exception Si hay errores en la operación
         */
        public function updateLibro($id, $libro) {
            try {
                // Primero verificamos que el libro existe
                $libroExistente = $this->getLibro($id);
                if (!$libroExistente) {
                    return null;
                }
                
                // Actualizamos los datos del libro con una consulta SQL directa
                $sql = "UPDATE LIBRO SET 
                        nombre = ?, 
                        ISBN = ?, 
                        idEditorial = ?, 
                        precio = ?,
                        activo = ? 
                        WHERE idLibro = ?";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
                }
                
                // Obtenemos los datos del objeto LibroEntity
                $nombre = $libro->getNombre();
                $isbn = $libro->getIsbn();
                $idEditorial = $libro->getEditorial()->getId();
                $precio = $libro->getPrecio();
                $estado = $libro->getEstado();
                
                // Enlazamos los parámetros
                $stmt->bind_param(
                    "ssidii", 
                    $nombre, 
                    $isbn, 
                    $idEditorial, 
                    $precio,
                    $estado,
                    $id
                );
                
                // Ejecutamos la consulta
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    return $this->getLibro($id);
                }
                
                return null;
                
            } catch (Exception $e) {
                error_log("Error en updateLibro: " . $e->getMessage());
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
                // Comenzar una transacción
                $this->conexion->begin_transaction();

                // Actualizar el estado del libro en la reserva a "Anulado" (ID 6)
                $sql = "UPDATE RESERVA_LIBRO SET idEstado = 6 WHERE idLibro = ? AND idReserva = ?";
                $stmt = $this->conexion->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
                }
                
                $stmt->bind_param("ii", $idLibro, $idReserva);
                $stmt->execute();
                
                if ($stmt->affected_rows <= 0) {
                    throw new Exception("No se encontró el libro en la reserva especificada");
                }

                // Confirmar la transacción
                $this->conexion->commit();
                
                return true;
                
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $this->conexion->rollback();
                error_log("Error en updateEstadoLibroReserva: " . $e->getMessage());
                throw $e;
            }
        }
    }
    
?>