<?php

    class LibroDto {
            
            private $id;
            private $nombre;
            private $isbn;
            private $editorial;
            private $precio;
            private $estado;
            private $etapa;

            public function __construct($id = null, $nombre = null, $isbn = null, $editorial = null, $precio = null, $estado = null, $etapa = null) {
                $this->id = $id;
                $this->nombre = $nombre;
                $this->isbn = $isbn;
                $this->editorial = $editorial;
                $this->precio = $precio;
                $this->estado = $estado;
                $this->etapa = $etapa;
            }
    
            public function getId() {
                return $this->id;
            }
    
            public function getNombre() {
                return $this->nombre;
            }

            public function getIsbn() {
                return $this->isbn;
            }
    
            public function getEditorial() {
                return $this->editorial;
            }
    
            public function getPrecio() {
                return $this->precio;
            }
    
            public function getEstado() {
                return $this->estado;
            }
    
            public function getEtapa() {
                return $this->etapa;
            }
    
            public function setId($id) {
                $this->id = $id;
            }
    
            public function setNombre($nombre) {
                $this->nombre = $nombre;
            }

            public function setIsbn($isbn) {
                $this->isbn = $isbn;
            }       
    
            public function setEditorial($editorial) {
                $this->editorial = $editorial;
            }
    
            public function setPrecio($precio) {
                $this->precio = $precio;
            }
    
            public function setEstado($estado) {
                $this->estado = $estado;
            }

            public function setEtapa($etapa) {
                $this->etapa = $etapa;
            }
    
            // EN TODOS LOS DTOs se debe hacer un toArray(), para que se pueda enviar al cliente
            public function toArray() {
                return [
                    'id' => (int)$this->id,
                    'nombre' => (string)$this->nombre,
                    'isbn' => (string)$this->isbn,
                    'editorial' => $this->editorial->toArray(),
                    'estado' => (boolean)$this->estado,
                    'precio' => (float)$this->precio,
                    'etapa' => $this->etapa->toArray()
                ];
            }
    }

?>