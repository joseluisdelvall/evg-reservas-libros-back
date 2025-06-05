<?php
require_once '../src/service/pedidos-service.php';
require_once '../src/utils/response.php';
require_once '../src/middleware/auth-middleware.php';

class PedidosController {
    private $pedidosService;
    private $authMiddleware;
    
    public function __construct() {
        $this->pedidosService = new PedidosService();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Método privado para verificar la autenticación
     * @return bool|void Retorna true si está autenticado o termina la ejecución si no lo está
     */
    private function verificarAuth() {
        $resultado = $this->authMiddleware->verificarAutenticacion();
        if ($resultado !== true) {
            echo json_encode($resultado);
            exit;
        }
        return true;
    }
    
    public function addPedido() {
        // Verificar autenticación antes de proceder
        $this->verificarAuth();

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
        // Verificar autenticación antes de proceder
        $this->verificarAuth();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->pedidosService->updateUnidadesRecibidas($data);
            return response('success', 'Unidades recibidas actualizadas correctamente', null);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
}