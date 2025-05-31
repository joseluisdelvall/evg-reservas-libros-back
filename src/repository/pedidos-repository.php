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
            $pedidos[] = [
                'idPedido' => $row['idPedido'],
                'fecha' => $row['fecha'],
                'numLibros' => (int)$row['numLibros']
            ];
        }
        return $pedidos;
    }
} 