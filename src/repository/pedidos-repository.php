<?php

require_once '../src/entity/pedido-entity.php';
require_once '../src/entity/libro-pedido-entity.php';
require_once '../src/entity/libro-entity.php';
require_once '../src/entity/editorial-entity.php';

class PedidosRepository {
    private $conexion;
    
    public function __construct() {
        require_once '../conexionTmpBD/conexion.php';
    }
    
    /**
     * Obtiene todos los pedidos con sus libros asociados
     * 
     * @return array Lista de pedidos
     */
    public function getPedidos() {
        try {
            $sql = "SELECT 
                        p.idPedido, 
                        p.fecha, 
                        e.idEditorial, 
                        e.nombre as editorial_nombre,
                        l.idLibro,
                        l.nombre as libro_nombre,
                        l.ISBN,
                        l.precio,
                        l.stock,
                        l.activo as libro_activo,
                        le.idEditorial as libro_editorial_id,
                        le.nombre as libro_editorial_nombre,
                        lp.unidades
                    FROM PEDIDO p
                    INNER JOIN EDITORIAL e ON p.idEditorial = e.idEditorial
                    LEFT JOIN LIBRO_PEDIDO lp ON p.idPedido = lp.idPedido
                    LEFT JOIN LIBRO l ON lp.idLibro = l.idLibro
                    LEFT JOIN EDITORIAL le ON l.idEditorial = le.idEditorial
                    ORDER BY p.idPedido";
            
            $result = $this->conexion->query($sql);
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            $pedidos = [];
            $pedidoActual = null;
            
            while ($row = $result->fetch_assoc()) {
                // Si es un nuevo pedido o el primer registro
                if ($pedidoActual === null || $pedidoActual->getId() !== $row['idPedido']) {
                    // Crear la editorial del pedido
                    $editorial = new EditorialEntity(
                        $row['idEditorial'],
                        $row['editorial_nombre']
                    );
                    
                    // Crear el nuevo pedido
                    $pedidoActual = new PedidoEntity(
                        $row['idPedido'],
                        $row['fecha'],
                        true,
                        [],
                        $editorial
                    );
                    
                    $pedidos[] = $pedidoActual;
                }
                
                // Si hay libro asociado, añadirlo al pedido
                if ($row['idLibro'] !== null) {
                    // Crear la editorial del libro
                    $editorialLibro = new EditorialEntity(
                        $row['libro_editorial_id'],
                        $row['libro_editorial_nombre']
                    );
                    
                    // Crear el libro
                    $libro = new LibroEntity(
                        $row['idLibro'],
                        $row['libro_nombre'],
                        $row['ISBN'],
                        $editorialLibro,
                        $row['libro_activo'],
                        $row['precio'],
                        $row['stock']
                    );
                    
                    // Crear el libro-pedido
                    $libroPedido = new LibroPedidoEntity(
                        $libro,
                        $row['unidades']
                    );
                    
                    // Modificar esta línea para obtener la referencia al array
                    $librosArray = &$pedidoActual->getLibros();
                    $librosArray[] = $libroPedido;
                }
            }
            
            return $pedidos;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?> 