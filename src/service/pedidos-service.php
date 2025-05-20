<?php

require_once '../src/entity/pedido-entity.php';
require_once '../src/repository/pedidos-repository.php';
require_once '../src/dto/pedido-dto.php';
require_once '../src/dto/editorial-dto.php';
require_once '../src/dto/libro-dto.php';
require_once '../src/dto/libro-pedido-dto.php';

class PedidosService {
    
    private $pedidosRepository;
    
    public function __construct() {
        $this->pedidosRepository = new PedidosRepository();
    }
    
    /**
     * Obtiene todos los pedidos
     * 
     * @return array Lista de pedidos
     */
    public function getPedidos() {
        try {
            $pedidosEntity = $this->pedidosRepository->getPedidos();
            
            if (!$pedidosEntity) {
                return null;
            }

            // Convertir las entidades a DTOs
            return array_map(function($pedido) {
                // Convertir la editorial a DTO si existe
                $editorialDto = null;
                if ($pedido->getEditorial()) {
                    $editorialDto = new EditorialDto(
                        $pedido->getEditorial()->getId(),
                        $pedido->getEditorial()->getNombre()
                    );
                }
                
                // Convertir los libros del pedido a DTOs
                $librosDto = [];
                
                foreach ($pedido->getLibros() as $libroPedidoEntity) {
                    $libroEntity = $libroPedidoEntity->getLibro();
                    
                    // Convertir la editorial del libro a DTO si existe
                    $editorialLibroDto = null;
                    if ($libroEntity->getEditorial()) {
                        $editorialLibroDto = new EditorialDto(
                            $libroEntity->getEditorial()->getId(),
                            $libroEntity->getEditorial()->getNombre()
                        );
                    }
                    
                    // Crear el DTO del libro
                    $libroDto = new LibroDto(
                        $libroEntity->getId(),
                        $libroEntity->getNombre(),
                        $libroEntity->getIsbn(),
                        $editorialLibroDto,
                        $libroEntity->getPrecio(),
                        $libroEntity->getStock(),
                        $libroEntity->getActivo()
                    );
                    
                    // Crear el DTO de la relación libro-pedido
                    $librosDto[] = new LibroPedidoDto(
                        $libroDto,
                        $libroPedidoEntity->getCantidad()
                    );
                }
                
                return new PedidoDto(
                    $pedido->getId(),
                    $pedido->getFecha(),
                    $pedido->getEstado(),
                    $librosDto,
                    $editorialDto
                );
            }, $pedidosEntity);
            
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?> 