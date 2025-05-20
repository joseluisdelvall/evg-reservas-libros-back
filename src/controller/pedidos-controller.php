<?php

require_once '../src/service/pedidos-service.php';
require_once '../src/dto/pedido-dto.php';
require_once '../src/utils/response.php';

class PedidosController {
    
    private $pedidosService;
    
    public function __construct() {
        $this->pedidosService = new PedidosService();
    }
    
    /**
     * Obtiene todos los pedidos
     * 
     * @return array Respuesta con el estado y los datos de los pedidos
     */
    public function getPedidos() {
        try {
            $pedidos = $this->pedidosService->getPedidos();
            
            if(!$pedidos) {
                return response('error', 'No se han encontrado pedidos', null, 404);
            }
            
            // Convertir cada pedido a array antes de enviar la respuesta
            $pedidosArray = array_map(function($pedido) {
                return $pedido->toArray();
            }, $pedidos);
            
            return response('success', 'Pedidos obtenidos correctamente', $pedidosArray);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
}
?> 