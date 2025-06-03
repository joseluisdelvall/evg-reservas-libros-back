<?php

require_once '../src/entity/reserva-entity.php';

class ReservasRepository {
    private $conexion;
    private $controlador;
    
    public function __construct() {
        require_once '../conexionTmpBD/conexion.php';
    }

    /**
     * Crea una nueva reserva en la base de datos
     * 
     * @param ReservaEntity $reserva Datos de la reserva
     * @return int|null ID de la reserva creada o null si falla
     */
    public function createReserva($reserva) {
        try {
            // Comenzar una transacción
            $this->conexion->begin_transaction();
            
            // Variable para ir calculando el total pagado
            $totalPagado = 0;
            
            // Consultar los precios de los libros primero para calcular el total
            $libros = $reserva->getLibros();
            $preciosLibros = [];
            
            if (!empty($libros)) {
                foreach ($libros as $idLibro) {
                    // Obtener el precio del libro
                    $sqlPrecio = "SELECT precio FROM LIBRO WHERE idLibro = ?";
                    $stmtPrecio = $this->conexion->prepare($sqlPrecio);
                    $stmtPrecio->bind_param("i", $idLibro);
                    $stmtPrecio->execute();
                    $resultPrecio = $stmtPrecio->get_result();
                    
                    if ($resultPrecio->num_rows > 0) {
                        $row = $resultPrecio->fetch_assoc();
                        $precio = $row['precio'];
                        $preciosLibros[$idLibro] = $precio;
                        $totalPagado += $precio;
                    } else {
                        throw new Exception("No se encontró el libro con ID: " . $idLibro);
                    }
                }
            }
            
            // Insertar la reserva con el totalPagado calculado
            $sql = "INSERT INTO RESERVA 
                    (nombreAlumno, apellidosAlumno, nombreTutorLegal, apellidosTutorLegal, 
                    correo, dni, telefono, justificante, fecha, verificado, totalPagado, idCurso) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Valores por defecto para campos opcionales
            $nombreTutor = $reserva->getNombreTutorLegal() ?: null;
            $apellidosTutor = $reserva->getApellidosTutorLegal() ?: null;
            $verificado = $reserva->getVerificado() ?: 0;
            $fecha = $reserva->getFecha();
            
            // Guardamos los valores en variables antes de pasarlos a bind_param
            $nombreAlumno = $reserva->getNombreAlumno();
            $apellidosAlumno = $reserva->getApellidosAlumno();
            $correo = $reserva->getCorreo();
            $dni = $reserva->getDni();
            $telefono = $reserva->getTelefono();
            $justificante = $reserva->getJustificante();
            $idCurso = $reserva->getIdCurso();
            
            $stmt->bind_param(
                "sssssssssddi",
                $nombreAlumno,
                $apellidosAlumno,
                $nombreTutor,
                $apellidosTutor,
                $correo,
                $dni,
                $telefono,
                $justificante,
                $fecha,
                $verificado,
                $totalPagado,  // Ahora utilizamos el totalPagado calculado
                $idCurso
            );
            
            $stmt->execute();
            
            if ($stmt->affected_rows <= 0) {
                throw new Exception("No se pudo crear la reserva");
            }
            
            $idReserva = $this->conexion->insert_id;
            
            // Insertar cada libro en RESERVA_LIBRO usando los precios ya consultados
            if (!empty($libros)) {
                // Estado por defecto: "Sin Verificar" (ID 1)
                $estadoDefault = 1;
                
                foreach ($libros as $idLibro) {
                    $precioPagado = $preciosLibros[$idLibro];
                    
                    // Insertar en RESERVA_LIBRO
                    $sqlReservaLibro = "INSERT INTO RESERVA_LIBRO 
                                        (idReserva, idLibro, precioPagado, idEstado) 
                                        VALUES (?, ?, ?, ?)";
                    $stmtReservaLibro = $this->conexion->prepare($sqlReservaLibro);
                    $stmtReservaLibro->bind_param("iidi", $idReserva, $idLibro, $precioPagado, $estadoDefault);
                    $stmtReservaLibro->execute();
                    
                    if ($stmtReservaLibro->affected_rows <= 0) {
                        throw new Exception("Error al insertar libro en la reserva");
                    }
                }
            }
            
            // Confirmar la transacción
            $this->conexion->commit();
            
            return $idReserva;
            
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $this->conexion->rollback();
            error_log("Error en la creación de la reserva: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el detalle de una reserva por su ID
     * 
     * @param int $idReserva ID de la reserva
     * @return array|null Datos de la reserva o null si no existe
     */
    public function getReservaById($idReserva) {
        $sql = "SELECT r.idReserva,
                       r.nombreAlumno,
                       r.apellidosAlumno,
                       r.correo,
                       r.telefono,
                       r.fecha,
                       r.verificado,
                       r.totalPagado,
                       r.idCurso,
                       c.nombre AS nombreCurso
                FROM RESERVA r
                INNER JOIN CURSO c ON r.idCurso = c.idCurso 
                WHERE r.idReserva = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idReserva);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $reservaData = $resultado->fetch_assoc();
            return [
                'idReserva' => $reservaData['idReserva'],
                'nombreAlumno' => $reservaData['nombreAlumno'],
                'apellidosAlumno' => $reservaData['apellidosAlumno'],
                'correo' => $reservaData['correo'],
                'telefono' => $reservaData['telefono'],
                'fecha' => $reservaData['fecha'],
                'verificado' => $reservaData['verificado'],
                'totalPagado' => $reservaData['totalPagado'],
                'idCurso' => $reservaData['idCurso'],
                'nombreCurso' => $reservaData['nombreCurso']
            ];
        }
        
        return null;
    }

    /**
     * Obtiene el detalle de una reserva por su ID
     * 
     * @param int $idReserva ID de la reserva
     * @return ReservaEntity|null Datos de la reserva o null si no existe
     */
    public function getReservaByIdCreate($idReserva) {
        $sql = "SELECT r.*, c.nombre AS nombreCurso 
                FROM RESERVA r
                INNER JOIN CURSO c ON r.idCurso = c.idCurso 
                WHERE r.idReserva = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idReserva);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $reservaData = $resultado->fetch_assoc();
            
            // Obtener los libros de la reserva
            $sqlLibros = "SELECT rl.idLibro, l.nombre, rl.precioPagado, e.nombre AS estado 
                        FROM RESERVA_LIBRO rl
                        INNER JOIN LIBRO l ON rl.idLibro = l.idLibro
                        INNER JOIN TM_ESTADO e ON rl.idEstado = e.idEstado
                        WHERE rl.idReserva = ?";
            
            $stmtLibros = $this->conexion->prepare($sqlLibros);
            $stmtLibros->bind_param("i", $idReserva);
            $stmtLibros->execute();
            
            $resultadoLibros = $stmtLibros->get_result();
            $libros = [];
            
            while ($libroData = $resultadoLibros->fetch_assoc()) {
                $libros[] = [
                    'id' => $libroData['idLibro'],
                    'nombre' => $libroData['nombre'],
                    'precio' => $libroData['precioPagado'],
                    'estado' => $libroData['estado']
                ];
            }
            
            // Crear la entidad Reserva
            return new ReservaEntity(
                $reservaData['idReserva'],
                $reservaData['nombreAlumno'],
                $reservaData['apellidosAlumno'],
                $reservaData['nombreTutorLegal'],
                $reservaData['apellidosTutorLegal'],
                $reservaData['correo'],
                $reservaData['dni'],
                $reservaData['telefono'],
                $reservaData['justificante'],
                $reservaData['fecha'],
                $reservaData['verificado'],
                $reservaData['totalPagado'],
                $reservaData['idCurso'],
                $libros,
                $reservaData['nombreCurso']
            );
        }
        
        return null;
    }

    /**
     * Obtiene todas las reservas, con los datos del curso, que no están anuladas
     * 
     * @return array Respuesta con el estado de la operación
     */
    public function getAllReservas() {
        $sql = "SELECT DISTINCT 
                    r.idReserva,
                    r.nombreAlumno,
                    r.apellidosAlumno,
                    r.correo,
                    r.telefono,
                    r.fecha,
                    r.verificado,
                    r.totalPagado,
                    r.idCurso,
                    c.nombre AS nombreCurso
                 FROM RESERVA r
                    INNER JOIN CURSO c ON r.idCurso = c.idCurso
                    INNER JOIN RESERVA_LIBRO rl ON r.idReserva = rl.idReserva
                 WHERE rl.idEstado != 6
                 GROUP BY r.idReserva
                 ORDER BY r.fecha DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if (!$resultado) {
            return [];
        }

        $reservas = [];
        if ($resultado->num_rows > 0) {
            while ($reservaData = $resultado->fetch_assoc()) {
                $reservas[] = [
                    'id' => $reservaData['idReserva'],
                    'nombreAlumno' => $reservaData['nombreAlumno'],
                    'apellidosAlumno' => $reservaData['apellidosAlumno'],
                    'correo' => $reservaData['correo'],
                    'telefono' => $reservaData['telefono'],
                    'fecha' => $reservaData['fecha'],
                    'verificado' => $reservaData['verificado'],
                    'totalPagado' => $reservaData['totalPagado'],
                    'idCurso' => $reservaData['idCurso'],
                    'nombreCurso' => $reservaData['nombreCurso']
                ];
            }
            return $reservas;
        }
        return [];
    }

    /**
     * Obtiene los libros de una reserva por su ID
     * @param int $idReserva
     * @return array Lista de libros
     */
    public function getLibrosByReservaId($idReserva) {
        $sql = "SELECT rl.idLibro, l.ISBN, l.nombre, edt.nombre AS editorial, rl.precioPagado, e.nombre AS estado 
                FROM RESERVA_LIBRO rl
                INNER JOIN LIBRO l ON rl.idLibro = l.idLibro
                INNER JOIN TM_ESTADO e ON rl.idEstado = e.idEstado
                INNER JOIN EDITORIAL edt ON edt.idEditorial = l.idEditorial
                WHERE rl.idReserva = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idReserva);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $libros = [];
        while ($libroData = $resultado->fetch_assoc()) {
            $libros[] = [
                'id' => $libroData['idLibro'],
                'isbn' => $libroData['ISBN'],
                'nombre' => $libroData['nombre'],
                'editorial' => $libroData['editorial'],
                'precio' => $libroData['precioPagado'],
                'estado' => $libroData['estado']
            ];
        }
        return $libros;
    }

    /**
     * Elimina una reserva y sus libros asociados por ID
     * @param int $idReserva
     * @return bool true si se eliminó correctamente
     */
    public function deleteReserva($idReserva) {
        try {
            // Eliminar primero los libros asociados
            $sqlLibros = "DELETE FROM RESERVA_LIBRO WHERE idReserva = ?";
            $stmtLibros = $this->conexion->prepare($sqlLibros);
            $stmtLibros->bind_param("i", $idReserva);
            $stmtLibros->execute();

            // Eliminar la reserva principal
            $sqlReserva = "DELETE FROM RESERVA WHERE idReserva = ?";
            $stmtReserva = $this->conexion->prepare($sqlReserva);
            $stmtReserva->bind_param("i", $idReserva);
            $stmtReserva->execute();

            return $stmtReserva->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error al eliminar la reserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de verificación de una reserva y sus libros asociados
     * @param int $idReserva ID de la reserva
     * @param bool $verificado Nuevo estado de verificación
     * @return bool true si se actualizó correctamente
     */
    public function updateReservaVerificado($idReserva, $verificado) {
        try {
            // Comenzar una transacción
            $this->conexion->begin_transaction();

            // Actualizar el estado de verificación de la reserva
            $sql = "UPDATE RESERVA SET verificado = ? WHERE idReserva = ?";
            $stmt = $this->conexion->prepare($sql);
            $verificadoInt = $verificado ? 1 : 0;
            $stmt->bind_param("ii", $verificadoInt, $idReserva);
            $stmt->execute();

            // Actualizar el estado de los libros asociados
            // Si la reserva se verifica, los libros pasan a estado "Verificado" (ID 2)
            // Si la reserva se desverifica, los libros vuelven a estado "Sin Verificar" (ID 1)
            $nuevoEstadoLibros = $verificado ? 2 : 1;
            $sqlLibros = "UPDATE RESERVA_LIBRO SET idEstado = ? WHERE idReserva = ?";
            $stmtLibros = $this->conexion->prepare($sqlLibros);
            $stmtLibros->bind_param("ii", $nuevoEstadoLibros, $idReserva);
            $stmtLibros->execute();

            // Confirmar la transacción
            $this->conexion->commit();

            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $this->conexion->rollback();
            error_log("Error al actualizar el estado de la reserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anula una reserva y sus libros asociados
     * @param int $idReserva ID de la reserva
     * @return bool true si se anuló correctamente
     */    public function anularReserva($idReserva) {
        try {
            // Comenzar una transacción
            $this->conexion->begin_transaction();

            // Actualizar el estado de los libros a "Anulado" (ID 6)
            $sqlLibros = "UPDATE RESERVA_LIBRO SET idEstado = 6 WHERE idReserva = ?";
            $stmtLibros = $this->conexion->prepare($sqlLibros);
            $stmtLibros->bind_param("i", $idReserva);
            $stmtLibros->execute();

            // Actualizar el estado de verificación de la reserva a false
            $sqlReserva = "UPDATE RESERVA SET verificado = 0 WHERE idReserva = ?";
            $stmtReserva = $this->conexion->prepare($sqlReserva);
            $stmtReserva->bind_param("i", $idReserva);
            $stmtReserva->execute();

            // Confirmar la transacción
            $this->conexion->commit();
            
            return true;
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $this->conexion->rollback();
            error_log("Error al anular la reserva: " . $e->getMessage());
            return false;
        }
    }
      /**
     * Actualiza los datos de una reserva (nombre, apellidos, correo y teléfono)
     * @param int $idReserva ID de la reserva
     * @param array $datos Datos a actualizar (nombreAlumno, apellidosAlumno, correo, telefono)
     * @return bool true si se actualizó correctamente
     */
    public function updateReservaData($idReserva, $datos) {
        try {
            // Preparar la consulta SQL para actualizar solo los campos permitidos
            $sql = "UPDATE RESERVA 
                    SET nombreAlumno = ?, 
                        apellidosAlumno = ?, 
                        correo = ?, 
                        telefono = ? 
                    WHERE idReserva = ?";
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "ssssi",
                $datos['nombreAlumno'],
                $datos['apellidosAlumno'],
                $datos['correo'],
                $datos['telefono'],
                $idReserva
            );
            
            $stmt->execute();
            
            return $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Error al actualizar los datos de la reserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el justificante de una reserva por su ID
     * @param int $idReserva ID de la reserva
     * @return array|null Nombre y ruta del justificante o null si no existe
     */
    public function getJustificanteByReservaId($idReserva) {
        try {
            $sql = "SELECT justificante FROM RESERVA WHERE idReserva = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idReserva);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $row = $resultado->fetch_assoc();
                return $row['justificante'];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error al obtener el justificante: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todas las reservas con información de los libros y el curso
     * 
     * @return array Lista de reservas con detalles de libros y curso
     */
    public function getReservas() {
        // Obtener todas las reservas con información del curso que tengan libros con estado 4 o 5
        $sql = "SELECT r.idReserva, r.nombreAlumno, r.apellidosAlumno, r.nombreTutorLegal, 
                       r.apellidosTutorLegal, r.correo, r.dni, r.telefono, r.justificante, 
                       r.fecha, r.verificado, r.totalPagado, r.idCurso,
                       c.nombre as nombreCurso, e.nombre as nombreEtapa,
                       CASE WHEN EXISTS (
                           SELECT 1 FROM RESERVA_LIBRO rl WHERE rl.idReserva = r.idReserva AND rl.idEstado = 4
                       ) THEN 1 ELSE 0 END as tieneLibrosRecibidos
                FROM RESERVA r
                INNER JOIN CURSO c ON r.idCurso = c.idCurso
                INNER JOIN ETAPA e ON c.idEtapa = e.idEtapa
                WHERE EXISTS (
                    SELECT 1 FROM RESERVA_LIBRO rl WHERE rl.idReserva = r.idReserva AND (rl.idEstado = 4 OR rl.idEstado = 5)
                )
                ORDER BY tieneLibrosRecibidos DESC, r.fecha DESC, r.idReserva DESC";
        
        $result = $this->conexion->query($sql);
        $reservas = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Obtener todos los libros de cada reserva (no solo los de estado 4 o 5)
                $sqlLibros = "SELECT rl.idLibro, rl.fechaRecogida, rl.precioPagado, rl.idEstado,
                                     l.nombre, l.ISBN, l.precio,
                                     tm.nombre as nombreEstado, tm.descripcion as descripcionEstado
                              FROM RESERVA_LIBRO rl
                              INNER JOIN LIBRO l ON rl.idLibro = l.idLibro
                              INNER JOIN TM_ESTADO tm ON rl.idEstado = tm.idEstado
                              WHERE rl.idReserva = ?";
                
                $stmtLibros = $this->conexion->prepare($sqlLibros);
                $stmtLibros->bind_param('i', $row['idReserva']);
                $stmtLibros->execute();
                $resultLibros = $stmtLibros->get_result();
                
                $libros = [];
                while ($rowLibro = $resultLibros->fetch_assoc()) {
                    $libros[] = [
                        'idLibro' => (int)$rowLibro['idLibro'],
                        'nombre' => $rowLibro['nombre'],
                        'ISBN' => $rowLibro['ISBN'],
                        'precio' => (float)$rowLibro['precio'],
                        'fechaRecogida' => $rowLibro['fechaRecogida'],
                        'precioPagado' => (float)$rowLibro['precioPagado'],
                        'idEstado' => (int)$rowLibro['idEstado'],
                        'nombreEstado' => $rowLibro['nombreEstado'],
                        'descripcionEstado' => $rowLibro['descripcionEstado']
                    ];
                }
                
                $reservas[] = [
                    'idReserva' => (int)$row['idReserva'],
                    'nombreAlumno' => $row['nombreAlumno'],
                    'apellidosAlumno' => $row['apellidosAlumno'],
                    'nombreTutorLegal' => $row['nombreTutorLegal'],
                    'apellidosTutorLegal' => $row['apellidosTutorLegal'],
                    'correo' => $row['correo'],
                    'dni' => $row['dni'],
                    'telefono' => $row['telefono'],
                    'justificante' => $row['justificante'],
                    'fecha' => $row['fecha'],
                    'verificado' => (bool)$row['verificado'],
                    'totalPagado' => (float)$row['totalPagado'],
                    'tieneLibrosRecibidos' => (bool)$row['tieneLibrosRecibidos'],
                    'curso' => [
                        'idCurso' => (int)$row['idCurso'],
                        'nombreCurso' => $row['nombreCurso'],
                        'nombreEtapa' => $row['nombreEtapa']
                    ],
                    'libros' => $libros
                ];
            }
        }
        
        return $reservas;
    }

    /**
     * Actualiza el estado de los libros en una reserva a "Recogido" y establece la fecha de recogida
     * 
     * @param int $idReserva ID de la reserva
     * @param array $librosEntregados Lista de IDs de los libros entregados
     * @return bool Verdadero si la operación fue exitosa, falso en caso contrario
     */
    public function entregarLibros($idReserva, $librosEntregados) {
        // Validar que la reserva existe
        $sqlValidate = "SELECT idReserva FROM RESERVA WHERE idReserva = ?";
        $stmtValidate = $this->conexion->prepare($sqlValidate);
        $stmtValidate->bind_param('i', $idReserva);
        $stmtValidate->execute();
        $result = $stmtValidate->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('La reserva no existe.');
        }
        
        foreach ($librosEntregados as $idLibro) {
            // Verificar que el libro está en la reserva y tiene estado 4 (Recibido)
            $sqlCheck = "SELECT idEstado FROM RESERVA_LIBRO WHERE idReserva = ? AND idLibro = ?";
            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bind_param('ii', $idReserva, $idLibro);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows === 0) {
                throw new Exception('El libro ' . $idLibro . ' no está en esta reserva.');
            }
            
            $row = $resultCheck->fetch_assoc();
            $estadoActual = (int)$row['idEstado'];
            
            if ($estadoActual !== 4) {
                throw new Exception('El libro ' . $idLibro . ' no está disponible para entregar. Estado actual: ' . $estadoActual);
            }
            
            // Actualizar el estado a 5 (Recogido) y establecer la fecha de recogida
            $fechaRecogida = date('Y-m-d');
            $sqlUpdate = "UPDATE RESERVA_LIBRO SET idEstado = 5, fechaRecogida = ? WHERE idReserva = ? AND idLibro = ?";
            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param('sii', $fechaRecogida, $idReserva, $idLibro);
            
            if (!$stmtUpdate->execute()) {
                throw new Exception('Error al actualizar el estado del libro ' . $idLibro . ': ' . $stmtUpdate->error);
            }
            
            if ($stmtUpdate->affected_rows === 0) {
                throw new Exception('No se pudo actualizar el estado del libro ' . $idLibro);
            }
            
            // Reducir el stock del libro
            $sqlUpdateStock = "UPDATE LIBRO SET stock = stock - 1 WHERE idLibro = ? AND stock > 0";
            $stmtUpdateStock = $this->conexion->prepare($sqlUpdateStock);
            $stmtUpdateStock->bind_param('i', $idLibro);
            
            if (!$stmtUpdateStock->execute()) {
                throw new Exception('Error al actualizar stock del libro ' . $idLibro . ': ' . $stmtUpdateStock->error);
            }
        }
        
        return true;
    }
}
?>