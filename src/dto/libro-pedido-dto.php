<?php

require_once '../src/dto/libro-dto.php';

class LibroPedidoDto {
    private $libro;
    private $cantidad;
    
    public function __construct($libro, $cantidad) {
        $this->libro = $libro;
        $this->cantidad = $cantidad;
    }
    
    public function getLibro() {
        return $this->libro;
    }
    
    public function getCantidad() {
        return $this->cantidad;
    }
    
    public function toArray() {
        return [
            'libro' => $this->libro->toArray(),
            'cantidad' => $this->cantidad
        ];
    }
}
?> 