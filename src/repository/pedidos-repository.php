<?php
require_once '../src/entity/pedido-entity.php';

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
} 