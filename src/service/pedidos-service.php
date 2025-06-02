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
    public function getEditorialesConPedidos() {
        return $this->pedidosRepository->getEditorialesConPedidos();
    }
    public function getPedidosByEditorial($idEditorial) {
        return $this->pedidosRepository->getPedidosByEditorial($idEditorial);
    }
    public function getPedido($idPedido) {
        if (empty($idPedido) || !is_numeric($idPedido)) {
            throw new Exception('ID de pedido inválido.');
        }
        
        $pedido = $this->pedidosRepository->getPedido($idPedido);
        
        if ($pedido === null) {
            throw new Exception('Pedido no encontrado.');
        }
        
        return $pedido;
    }
    
    public function updateUnidadesRecibidas($data) {
        if (empty($data['idEditorial']) || empty($data['idPedido']) || empty($data['librosRecibidos']) || !is_array($data['librosRecibidos'])) {
            throw new Exception('Datos incompletos para actualizar unidades recibidas.');
        }
        
        // Validar libros recibidos
        foreach ($data['librosRecibidos'] as $libro) {
            if (!isset($libro['idLibro']) || !isset($libro['cantidadRecibida']) || !is_numeric($libro['cantidadRecibida'])) {
                throw new Exception('Datos de libro incompletos o inválidos.');
            }
            
            if ((int)$libro['cantidadRecibida'] < 0) {
                throw new Exception('La cantidad recibida no puede ser negativa.');
            }
        }
        
        return $this->pedidosRepository->updateUnidadesRecibidas($data['idEditorial'], $data['idPedido'], $data['librosRecibidos']);
    }
}