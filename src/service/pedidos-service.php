<?php
require_once '../src/repository/pedidos-repository.php';
require_once '../src/entity/pedido-entity.php';

class PedidosService {
    private $pedidosRepository;
    public function __construct() {
        $this->pedidosRepository = new PedidosRepository();
    }
    public function addPedido($data) {
        if (empty($data['idEditorial']) || empty($data['libros']) || !is_array($data['libros'])) {
            throw new Exception('Datos de pedido incompletos.');
        }
        // Validar libros
        foreach ($data['libros'] as $libro) {
            if (empty($libro['idLibro']) || empty($libro['cantidad'])) {
                throw new Exception('Datos de libro incompletos.');
            }
        }
        return $this->pedidosRepository->addPedido($data['idEditorial'], $data['libros']);
    }
} 