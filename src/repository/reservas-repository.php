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
}
?> 