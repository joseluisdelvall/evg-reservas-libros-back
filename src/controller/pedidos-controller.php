<?php
require_once '../src/service/pedidos-service.php';
require_once '../src/utils/response.php';

class PedidosController {
    private $pedidosService;
    public function __construct() {
        $this->pedidosService = new PedidosService();
    }
    public function addPedido() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $pedido = $this->pedidosService->addPedido($data);
            return response('success', 'Pedido creado correctamente', $pedido->toArray());
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
    public function getEditorialesConPedidos() {
        try {
            $editoriales = $this->pedidosService->getEditorialesConPedidos();
            return response('success', 'Editoriales con pedidos obtenidas correctamente', $editoriales);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
    public function getPedidosByEditorial($idEditorial) {
        try {
            $pedidos = $this->pedidosService->getPedidosByEditorial($idEditorial);
            return response('success', 'Pedidos de la editorial obtenidos correctamente', $pedidos);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
    public function getPedido($idPedido) {
        try {
            $pedido = $this->pedidosService->getPedido($idPedido);
            return response('success', 'Pedido obtenido correctamente', $pedido->toArray());
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
    public function updateUnidadesRecibidas() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->pedidosService->updateUnidadesRecibidas($data);
            return response('success', 'Unidades recibidas actualizadas correctamente', null);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
}