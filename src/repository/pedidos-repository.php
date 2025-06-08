<?php
require_once '../src/entity/pedido-entity.php';
require_once '../src/service/email-service.php';

class PedidosRepository {
    private $conexion;
    public function __construct() {
        require_once '../conexionTmpBD/conexion.php';
    }

    public function addPedido($idEditorial, $libros) {
        $fecha = date('Y-m-d');
        $sql = "INSERT INTO PEDIDO (fecha, idEditorial) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('si', $fecha, $idEditorial);
        if (!$stmt->execute()) {
            throw new Exception('Error al insertar el pedido: ' . $stmt->error);
        }
        $idPedido = $this->conexion->insert_id;
        // Insertar los libros
        foreach ($libros as $libro) {
            $sqlLibro = "INSERT INTO LIBRO_PEDIDO (idPedido, idLibro, unidades) VALUES (?, ?, ?)";
            $stmtLibro = $this->conexion->prepare($sqlLibro);
            $stmtLibro->bind_param('iii', $idPedido, $libro['idLibro'], $libro['cantidad']);
            if (!$stmtLibro->execute()) {
                throw new Exception('Error al insertar libro en el pedido: ' . $stmtLibro->error);
            }
            // ACTUALIZAR RESERVA_LIBRO: solo los que tengan idEstado = 2
            $sqlSelect = "SELECT rl.idReserva, rl.idLibro FROM RESERVA_LIBRO rl INNER JOIN RESERVA r ON rl.idReserva = r.idReserva WHERE rl.idLibro = ? AND rl.idEstado = 2 ORDER BY r.fecha ASC, rl.idReserva ASC LIMIT ?";
            $stmtSelect = $this->conexion->prepare($sqlSelect);
            $stmtSelect->bind_param('ii', $libro['idLibro'], $libro['cantidad']);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();
            $idsActualizar = [];
            while ($row = $result->fetch_assoc()) {
                $idsActualizar[] = $row['idReserva'];
            }
            if (!empty($idsActualizar)) {
                // Actualizar el estado a 3 (Pedido)
                $in = implode(',', array_fill(0, count($idsActualizar), '?'));
                $types = str_repeat('i', count($idsActualizar));
                $sqlUpdate = "UPDATE RESERVA_LIBRO SET idEstado = 3 WHERE idLibro = ? AND idReserva IN ($in)";
                $stmtUpdate = $this->conexion->prepare($sqlUpdate);
                $params = array_merge([$libro['idLibro']], $idsActualizar);
                $stmtUpdate->bind_param(str_repeat('i', count($params)), ...$params);
                if (!$stmtUpdate->execute()) {
                    throw new Exception('Error al actualizar estado de reservas: ' . $stmtUpdate->error);
                }
            }
        }
        return new PedidoEntity($idPedido, $fecha, $idEditorial, $libros);
    }

    public function getEditorialesConPedidos() {
        $sql = "SELECT e.idEditorial, e.nombre, COUNT(p.idPedido) as numPedidos FROM EDITORIAL e INNER JOIN PEDIDO p ON e.idEditorial = p.idEditorial GROUP BY e.idEditorial, e.nombre";
        $result = $this->conexion->query($sql);
        $editoriales = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $editoriales[] = [
                    'idEditorial' => $row['idEditorial'],
                    'nombre' => $row['nombre'],
                    'numPedidos' => (int)$row['numPedidos']
                ];
            }
        }
        return $editoriales;
    }

    public function getPedidosByEditorial($idEditorial) {
        $sql = "SELECT p.idPedido, p.fecha, COUNT(lp.idLibro) as numLibros FROM PEDIDO p INNER JOIN LIBRO_PEDIDO lp ON p.idPedido = lp.idPedido WHERE p.idEditorial = ? GROUP BY p.idPedido, p.fecha ORDER BY p.fecha DESC, p.idPedido DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $idEditorial);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            // Calcular el estado del pedido basado en las unidades recibidas
            $sqlEstado = "SELECT 
                            SUM(unidades) as totalUnidades,
                            SUM(unidadesRecibidas) as totalRecibidas,
                            COUNT(CASE WHEN unidadesRecibidas > 0 THEN 1 END) as librosConRecibidos,
                            COUNT(*) as totalLibros
                          FROM LIBRO_PEDIDO 
                          WHERE idPedido = ?";
            $stmtEstado = $this->conexion->prepare($sqlEstado);
            $stmtEstado->bind_param('i', $row['idPedido']);
            $stmtEstado->execute();
            $resultEstado = $stmtEstado->get_result();
            $estadoData = $resultEstado->fetch_assoc();
            
            $totalUnidades = (int)$estadoData['totalUnidades'];
            $totalRecibidas = (int)$estadoData['totalRecibidas'];
            $librosConRecibidos = (int)$estadoData['librosConRecibidos'];
            
            // Determinar el estado
            $estado = 'pendiente'; // Por defecto
            if ($totalRecibidas == $totalUnidades && $totalUnidades > 0) {
                $estado = 'completado';
            } elseif ($librosConRecibidos > 0) {
                $estado = 'medioPendiente';
            }
            
            $pedidos[] = [
                'idPedido' => $row['idPedido'],
                'fecha' => $row['fecha'],
                'numLibros' => (int)$row['numLibros'],
                'estado' => $estado
            ];
        }
        return $pedidos;
    }

    public function getPedido($idPedido) {
        // Obtener información del pedido
        $sql = "SELECT p.idPedido, p.fecha, p.idEditorial FROM PEDIDO p WHERE p.idPedido = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $idPedido);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $pedidoData = $result->fetch_assoc();
        
        // Obtener los libros del pedido con sus datos
        $sqlLibros = "SELECT lp.idLibro, lp.unidades as cantidad, lp.unidadesRecibidas, l.nombre, l.ISBN, l.precio 
                      FROM LIBRO_PEDIDO lp 
                      INNER JOIN LIBRO l ON lp.idLibro = l.idLibro 
                      WHERE lp.idPedido = ?";
        $stmtLibros = $this->conexion->prepare($sqlLibros);
        $stmtLibros->bind_param('i', $idPedido);
        $stmtLibros->execute();
        $resultLibros = $stmtLibros->get_result();
        
        $libros = [];
        while ($row = $resultLibros->fetch_assoc()) {
            $libros[] = [
                'idLibro' => (int)$row['idLibro'],
                'cantidad' => (int)$row['cantidad'],
                'unidadesRecibidas' => (int)$row['unidadesRecibidas'],
                'nombre' => $row['nombre'],
                'ISBN' => $row['ISBN'],
                'precio' => (float)$row['precio']
            ];
        }
        
        return new PedidoEntity(
            (int)$pedidoData['idPedido'],
            $pedidoData['fecha'],
            (int)$pedidoData['idEditorial'],
            $libros
        );
    }

    public function updateUnidadesRecibidas($idEditorial, $idPedido, $librosRecibidos) {
        // Validar que el pedido pertenece a la editorial
        $sqlValidate = "SELECT p.idPedido FROM PEDIDO p WHERE p.idPedido = ? AND p.idEditorial = ?";
        $stmtValidate = $this->conexion->prepare($sqlValidate);
        $stmtValidate->bind_param('ii', $idPedido, $idEditorial);
        $stmtValidate->execute();
        $result = $stmtValidate->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('El pedido no pertenece a la editorial especificada.');
        }
        
        foreach ($librosRecibidos as $libro) {
            // Obtener unidades actuales y unidades recibidas actuales
            $sqlCheck = "SELECT unidades, unidadesRecibidas FROM LIBRO_PEDIDO WHERE idPedido = ? AND idLibro = ?";
            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bind_param('ii', $idPedido, $libro['idLibro']);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows === 0) {
                throw new Exception('El libro ' . $libro['idLibro'] . ' no está en este pedido.');
            }
            
            $row = $resultCheck->fetch_assoc();
            $unidadesTotales = (int)$row['unidades'];
            $unidadesRecibidas = (int)$row['unidadesRecibidas'];
            $nuevasUnidadesRecibidas = $unidadesRecibidas + (int)$libro['cantidadRecibida'];
            
            // Validar que no exceda las unidades pedidas
            if ($nuevasUnidadesRecibidas > $unidadesTotales) {
                throw new Exception('Las unidades recibidas (' . $nuevasUnidadesRecibidas . ') no pueden ser mayores que las unidades pedidas (' . $unidadesTotales . ') para el libro ' . $libro['idLibro'] . '.');
            }
            
            // Solo proceder si hay cantidad recibida mayor que 0
            if ((int)$libro['cantidadRecibida'] > 0) {
                // Actualizar unidades recibidas
                $sqlUpdate = "UPDATE LIBRO_PEDIDO SET unidadesRecibidas = ? WHERE idPedido = ? AND idLibro = ?";
                $stmtUpdate = $this->conexion->prepare($sqlUpdate);
                $stmtUpdate->bind_param('iii', $nuevasUnidadesRecibidas, $idPedido, $libro['idLibro']);
                
                if (!$stmtUpdate->execute()) {
                    throw new Exception('Error al actualizar unidades recibidas: ' . $stmtUpdate->error);
                }
                
                
                
                // Actualizar estado de reservas de idEstado = 3 (Pedido) a idEstado = 4 (Recibido)
                // Obtener las reservas con estado 3 ordenadas por fecha de reserva y luego por ID
                $sqlSelectReservas = "SELECT rl.idReserva, rl.idLibro 
                                     FROM RESERVA_LIBRO rl 
                                     INNER JOIN RESERVA r ON rl.idReserva = r.idReserva 
                                     WHERE rl.idLibro = ? AND rl.idEstado = 3 
                                     ORDER BY r.fecha ASC, rl.idReserva ASC 
                                     LIMIT ?";
                $stmtSelectReservas = $this->conexion->prepare($sqlSelectReservas);
                $stmtSelectReservas->bind_param('ii', $libro['idLibro'], $libro['cantidadRecibida']);
                $stmtSelectReservas->execute();
                $resultReservas = $stmtSelectReservas->get_result();
                
                $idsReservasActualizar = [];
                while ($rowReserva = $resultReservas->fetch_assoc()) {
                    $idsReservasActualizar[] = $rowReserva['idReserva'];
                }
                  // Actualizar el estado de las reservas seleccionadas a 4 (Recibido)
                if (!empty($idsReservasActualizar)) {
                    $in = implode(',', array_fill(0, count($idsReservasActualizar), '?'));
                    $sqlUpdateReservas = "UPDATE RESERVA_LIBRO SET idEstado = 4 WHERE idLibro = ? AND idReserva IN ($in)";
                    $stmtUpdateReservas = $this->conexion->prepare($sqlUpdateReservas);
                    $params = array_merge([$libro['idLibro']], $idsReservasActualizar);
                    $stmtUpdateReservas->bind_param(str_repeat('i', count($params)), ...$params);
                    
                    if (!$stmtUpdateReservas->execute()) {
                        throw new Exception('Error al actualizar estado de reservas: ' . $stmtUpdateReservas->error);
                    }
                    
                    // Enviar notificaciones por email a los usuarios cuyas reservas cambiaron a estado "Recibido"
                    $this->enviarNotificacionesLibroRecibido($idsReservasActualizar, $libro['idLibro']);
                }
            }
        }        
        return true;
    }
    
    /**
     * Envía notificaciones por email a usuarios cuando sus libros están listos para recoger
     * @param array $idsReservas Array de IDs de reservas que cambiaron a estado "Recibido"
     * @param int $idLibro ID del libro que se recibió
     */
    private function enviarNotificacionesLibroRecibido($idsReservas, $idLibro) {
        try {
            if (empty($idsReservas)) {
                return;
            }
            
            $emailService = new EmailService();
            
            // Obtener información de las reservas y el libro
            $in = implode(',', array_fill(0, count($idsReservas), '?'));
            $sql = "SELECT r.idReserva, r.nombreAlumno, r.apellidosAlumno, r.correo, l.nombre as nombreLibro
                    FROM RESERVA r 
                    INNER JOIN RESERVA_LIBRO rl ON r.idReserva = rl.idReserva
                    INNER JOIN LIBRO l ON rl.idLibro = l.idLibro
                    WHERE r.idReserva IN ($in) AND rl.idLibro = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $params = array_merge($idsReservas, [$idLibro]);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $fechaActual = date('d/m/Y');
            
            while ($row = $result->fetch_assoc()) {
                try {
                    // Preparar datos para el email
                    $datosEmail = [
                        'nombreAlumno' => $row['nombreAlumno'],
                        'apellidosAlumno' => $row['apellidosAlumno'],
                        'nombreLibro' => $row['nombreLibro'],
                        'estadoLibro' => 'Recibido',
                        'fecha' => $fechaActual
                    ];                    // Enviar email usando el servicio existente
                    $emailService->sendEmail(
                        $row['correo'],
                        '¡Su libro ya está disponible para recoger!',
                        'libroRecibido',
                        $datosEmail,
                        $row['nombreAlumno'] . ' ' . $row['apellidosAlumno']
                    );
                    
                } catch (Exception $emailException) {
                    // Log del error pero no fallar toda la operación
                    error_log("Error enviando notificación de libro recibido para reserva {$row['idReserva']}: " . $emailException->getMessage());
                }
            }
            
        } catch (Exception $e) {
            // Log del error pero no fallar toda la operación de actualización de pedidos
            error_log("Error en enviarNotificacionesLibroRecibido: " . $e->getMessage());
        }
    }
}