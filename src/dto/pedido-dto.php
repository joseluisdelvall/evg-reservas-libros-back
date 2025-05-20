<?php

require_once '../src/dto/libro-pedido-dto.php';
require_once '../src/dto/editorial-dto.php';

class PedidoDto {
    private $id;
    private $fecha;
    private $estado;
    private $libros;
    private $editorial;
    
    public function __construct($id, $fecha, $estado, $libros, $editorial = null) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->libros = $libros;
        $this->editorial = $editorial;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getFecha() {
        return $this->fecha;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function getLibros() {
        return $this->libros;
    }
    
    public function getEditorial() {
        return $this->editorial;
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'estado' => $this->estado,
            'libros' => array_map(function($libro) {
                return $libro->toArray();
            }, $this->libros),
            'editorial' => $this->editorial ? $this->editorial->toArray() : null
        ];
    }
}
?> 