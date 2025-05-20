<?php

require_once '../src/entity/libro-entity.php';

class LibroPedidoEntity {
    private $libro;
    private $cantidad;
    
    public function __construct($libro = null, $cantidad = null) {
        $this->libro = $libro;
        $this->cantidad = $cantidad;
    }
    
    public function getLibro() {
        return $this->libro;
    }
    
    public function getCantidad() {
        return $this->cantidad;
    }
    
    public function setLibro($libro) {
        $this->libro = $libro;
    }
    
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function toArray() {
        return [
            'libro' => $this->libro->toArray(),
            'cantidad' => $this->cantidad
        ];
    }
}
?> 