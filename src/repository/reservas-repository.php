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
     * Obtiene todas las reservas con información de los libros y el curso
     * 
     * @return array Lista de reservas con detalles de libros y curso
     */
    public function getReservas() {
        // Obtener todas las reservas con información del curso
        $sql = "SELECT r.idReserva, r.nombreAlumno, r.apellidosAlumno, r.nombreTutorLegal, 
                       r.apellidosTutorLegal, r.correo, r.dni, r.telefono, r.justificante, 
                       r.fecha, r.verificado, r.totalPagado, r.idCurso,
                       c.nombre as nombreCurso, e.nombre as nombreEtapa
                FROM RESERVA r
                INNER JOIN CURSO c ON r.idCurso = c.idCurso
                INNER JOIN ETAPA e ON c.idEtapa = e.idEtapa
                WHERE EXISTS (
                    SELECT 1 FROM RESERVA_LIBRO rl WHERE rl.idReserva = r.idReserva AND rl.idEstado = 4
                )
                ORDER BY r.fecha DESC, r.idReserva DESC";
        
        $result = $this->conexion->query($sql);
        $reservas = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Obtener solo los libros con idEstado = 4 (Recibido) de cada reserva
                $sqlLibros = "SELECT rl.idLibro, rl.fechaRecogida, rl.precioPagado, rl.idEstado,
                                     l.nombre, l.ISBN, l.precio,
                                     tm.nombre as nombreEstado, tm.descripcion as descripcionEstado
                              FROM RESERVA_LIBRO rl
                              INNER JOIN LIBRO l ON rl.idLibro = l.idLibro
                              INNER JOIN TM_ESTADO tm ON rl.idEstado = tm.idEstado
                              WHERE rl.idReserva = ? AND rl.idEstado = 4";
                
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
                
                // Solo agregar la reserva si tiene libros con estado 4
                if (!empty($libros)) {
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
                        'curso' => [
                            'idCurso' => (int)$row['idCurso'],
                            'nombreCurso' => $row['nombreCurso'],
                            'nombreEtapa' => $row['nombreEtapa']
                        ],
                        'libros' => $libros
                    ];
                }
            }
        }
        
        return $reservas;
    }
}
?>