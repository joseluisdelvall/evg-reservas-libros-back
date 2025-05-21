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
     * @return ReservaEntity|null Datos de la reserva o null si no existe
     */
    public function getReservaById($idReserva) {
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
                $libros
            );
        }
        
        return null;
    }

    /**
     * Obtiene todas las reservas con los datos del curso y los libros
     * 
     * @return array Respuesta con el estado de la operación
     */
    public function getAllReservas() {
        $sql = " SELECT DISTINCT 
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
}
?> 