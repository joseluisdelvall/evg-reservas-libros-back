<?php

    require_once '../src/entity/libro-entity.php';
    require_once '../src/entity/editorial-entity.php';
    require_once '../src/entity/etapa-entity.php';

    class LibrosRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getLibros() {
            // Obtener todos los libros
            $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo, lb.idEtapa, et.nombre AS nombreEtapa
                    FROM LIBRO AS lb 
                    INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial
                    INNER JOIN ETAPA AS et ON lb.idEtapa = et.idEtapa";
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

                    // Crear la entidad Etapa
                    $etapa = new EtapaEntity(
                        $libro['idEtapa'],
                        $libro['nombreEtapa']
                    );

                    // Crear la entidad Libro con la entidad Editorial
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'], // Usar el alias correcto
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo'],
                        $etapa
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
                $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo, lb.idEtapa, et.nombre AS nombreEtapa
                        FROM LIBRO AS lb 
                        INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial 
                        INNER JOIN ETAPA AS et ON lb.idEtapa = et.idEtapa
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

                    // Crear la entidad Etapa
                    $etapa = new EtapaEntity(
                        $libroData['idEtapa'],
                        $libroData['nombreEtapa']
                    );
                    
                    // Crear la entidad Libro igual que en getLibros()
                    return new LibroEntity(
                        $libroData['idLibro'],
                        $libroData['libroNombre'],
                        $libroData['ISBN'],
                        $editorial,
                        $libroData['precio'],
                        $libroData['stock'],
                        $libroData['activo'],
                        $etapa
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
                        l.idEditorial, e.nombre AS editorialNombre, l.activo, l.idEtapa, et.nombre AS nombreEtapa
                    FROM LIBRO l
                    INNER JOIN EDITORIAL e ON l.idEditorial = e.idEditorial
                    INNER JOIN ETAPA et ON l.idEtapa = et.idEtapa
                    INNER JOIN CURSO_LIBRO cl ON l.idLibro = cl.idLibro
                    WHERE cl.idCurso = ? AND l.activo = 1";
            
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

                    $etapa = new EtapaEntity(
                        $libro['idEtapa'],
                        $libro['nombreEtapa']
                    );
                    
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'],
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo'],
                        $etapa
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
                $sql = "INSERT INTO LIBRO (nombre, ISBN, idEditorial, precio, stock, activo, idEtapa) VALUES (";
                $sql .= "'" . $libro->getNombre() . "', ";
                $sql .= "'" . $libro->getIsbn() . "', ";
                $sql .= "'" . $libro->getEditorial()->getId() . "', ";
                $sql .= "'" . $libro->getPrecio() . "', ";
                $sql .= "'" . $libro->getStock() . "', ";
                $sql .= "'" . $libro->getEstado() . "', ";
                $sql .= "'" . $libro->getEtapa()->getId() . "')";
        
                $resultado = $this->conexion->query($sql);
        
                if (!$resultado) {
                    throw new Exception("Error en la consulta SQL: " . $this->conexion->error);
                }
        
                if ($this->conexion->affected_rows > 0) {
                    $id = $this->conexion->insert_id;

                    // Recuperar el registro completo con la información de la editorial
                    $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo, lb.idEtapa, et.nombre AS nombreEtapa
                            FROM LIBRO AS lb 
                            INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial 
                            INNER JOIN ETAPA AS et ON lb.idEtapa = et.idEtapa
                            WHERE idLibro = " . $id;
                    $resultado = $this->conexion->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        $libroData = $resultado->fetch_assoc();
                        $editorial = new EditorialEntity(
                            $libroData['idEditorial'],
                            $libroData['editorialNombre']
                        );

                        $etapa = new EtapaEntity(
                            $libroData['idEtapa'],
                            $libroData['nombreEtapa']
                        );

                        return new LibroEntity(
                            $libroData['idLibro'],
                            $libroData['libroNombre'],
                            $libroData['ISBN'],
                            $editorial,
                            $libroData['precio'],
                            $libroData['stock'],
                            $libroData['activo'],
                            $etapa
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
                        activo = ?,
                        idEtapa = ?
                        WHERE idLibro = ?";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
                }
                
                // Obtenemos los datos del objeto LibroEntity
                $nombre = $libro->getNombre();
                $isbn = $libro->getIsbn();
                $idEditorial = (int) $libro->getEditorial()->getId(); // Cast to integer
                $precio = (float) $libro->getPrecio(); // Cast to float for 'd' type
                $estado = (int) $libro->getEstado(); // Cast to integer
                $idEtapaValue = (int) $libro->getEtapa()->getId(); // Cast to integer
                $idLibroForWhere = (int) $id; // This is the ID for the WHERE clause, cast to integer

                // Enlazamos los parámetros
                $stmt->bind_param(
                    "ssidiii", // Corrected: s,s,i,d,i,i,i for 7 parameters
                    $nombre, 
                    $isbn, 
                    $idEditorial, 
                    $precio,
                    $estado,
                    $idEtapaValue, // Use the casted integer value for idEtapa
                    $idLibroForWhere // Added idLibro for the WHERE clause
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
         * Se actualiza el stock del libro en caso de que el estado actual del 
         * libro en la reserva a anular sea 4 [Recibido] o 5 [Recogido] y no exista una 
         * reserva pendiente para este libro. Si existe una reserva pendiente para este libro, 
         * se actualiza el estado de la siguiente reserva a 4 [Recibido].
         * 
         * En caso de que el estado actual del libro en la reserva a anular sea 3 [Pedido], 
         * se actualiza el estado de la siguiente reserva a 3 [Pedido].
         * 
         * @param int $idLibro ID del libro
         * @param int $idReserva ID de la reserva
         * @return bool true si se actualizó correctamente
         * @throws Exception Si hay errores en la operación
         */
        public function updateEstadoLibroReserva($idLibro, $idReserva) {
            try {
                // Comenzar transacción
                $this->conexion->begin_transaction();

                // Obtener el ESTADO ACTUAL del LIBRO en la reserva a ANULAR
                $sqlEstado = "SELECT idEstado FROM RESERVA_LIBRO WHERE idLibro = ? AND idReserva = ?";
                $stmtEstado = $this->conexion->prepare($sqlEstado);
                $stmtEstado->bind_param("ii", $idLibro, $idReserva);
                $stmtEstado->execute();
                $resultEstado = $stmtEstado->get_result();
                
                if ($resultEstado->num_rows === 0) {
                    throw new Exception("No se encontró el libro en la reserva especificada");
                }
                
                $rowEstado = $resultEstado->fetch_assoc();
                // Estado actual del libro en la reserva a anular
                $estadoActualRervAnular = (int)$rowEstado['idEstado'];

                // Actualizar el estado del libro en la reserva a "Anulado" [ID 6]
                $sql = "UPDATE RESERVA_LIBRO SET idEstado = 6 WHERE idLibro = ? AND idReserva = ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("ii", $idLibro, $idReserva);
                $stmt->execute();

                // En el caso de que el estado actual de la reserva a anular sea 1 [Pendiente] o 2 [Pedido], 
                // no se actualiza el estado de la siguiente reserva.
                if ($estadoActualRervAnular != 1 && $estadoActualRervAnular != 2) {
                    // Buscar la SIGUIENTE RESERVA con este LIBRO en estado PENDIENTE
                    $sqlSiguienteReserva = "SELECT rl.idReserva 
                                      FROM RESERVA_LIBRO rl 
                                      INNER JOIN RESERVA r ON rl.idReserva = r.idReserva 
                                      WHERE rl.idLibro = ? AND rl.idEstado = 2 
                                      ORDER BY r.fecha ASC, rl.idReserva ASC 
                                      LIMIT 1";
                    $stmtSiguienteReserva = $this->conexion->prepare($sqlSiguienteReserva);
                    $stmtSiguienteReserva->bind_param("i", $idLibro);
                    $stmtSiguienteReserva->execute();
                    $resultSiguienteReserva = $stmtSiguienteReserva->get_result();

                        // Si hay una reserva pendiente para este libro, se actualiza el estado de la siguiente reserva
                    if ($resultSiguienteReserva->num_rows > 0) {
                        $rowSiguienteReserva = $resultSiguienteReserva->fetch_assoc();
                        
                        // Determinar el nuevo estado del libro dela SIGUIENTE RESERVA según el estado actual
                        if ($estadoActualRervAnular === 3) { // Si el ESTADO actual es PEDIDO
                            $nuevoEstado = 3; // Mantener como PEDIDO
                        } else if (in_array($estadoActualRervAnular, [4, 5])) { // Si el estado es Recibido o Recogido
                            $nuevoEstado = 4; // Asignar como Recibido
                        }
                        
                        // Actualizar el estado de la siguiente reserva
                        $sqlActualizarSiguiente = "UPDATE RESERVA_LIBRO SET idEstado = ? 
                                                WHERE idLibro = ? AND idReserva = ?";
                        $stmtActualizarSiguiente = $this->conexion->prepare($sqlActualizarSiguiente);
                        $stmtActualizarSiguiente->bind_param("iii", $nuevoEstado, $idLibro, $rowSiguienteReserva['idReserva']);
                        $stmtActualizarSiguiente->execute();
                    } else if ($estadoActualRervAnular == 5) {
                        // Si no hay reservas pendientes y el estado era Recibido o Recogido, aumentar el stock
                        $sqlUpdateStock = "UPDATE LIBRO SET stock = stock + 1 WHERE idLibro = ?";
                        $stmtUpdateStock = $this->conexion->prepare($sqlUpdateStock);
                        $stmtUpdateStock->bind_param("i", $idLibro);
                        $stmtUpdateStock->execute();
                    }

                }
                // Confirmar la transacción
                $this->conexion->commit();
                // Devolver true si la transacción se ha realizado correctamente
                return true;
                
            } catch (Exception $e) {
                // Revertir la transacción en caso de error                
                $this->conexion->rollback();
                error_log("Error en updateEstadoLibroReserva: " . $e->getMessage());
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
            $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo, lb.idEtapa, et.nombre AS nombreEtapa
                    FROM LIBRO AS lb 
                    INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial
                    INNER JOIN ETAPA AS et ON lb.idEtapa = et.idEtapa
                    WHERE lb.idEtapa = ? AND lb.activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idEtapa);
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
                    $etapa = new EtapaEntity(
                        $libro['idEtapa'],
                        $libro['nombreEtapa']
                    );
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'],
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo'],
                        $etapa
                    );
                }
            }
            return $libros;
        }
    }
    
?>