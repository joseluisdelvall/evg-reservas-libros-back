<?php
    
    class LibroEntity {

        private $id;
        private $nombre;
        private $isbn;
        private $editorial;
        private $precio;
        private $stock;
        private $activo;

        public function __construct($id = null, $nombre = null, $isbn = null, $editorial = null, $precio = null, $stock = null, $activo = null) {
            $this->id = $id;
            $this->nombre = $nombre;
            $this->isbn = $isbn;
            $this->editorial = $editorial;
            $this->precio = $precio;
            $this->stock = $stock;
            $this->activo = $activo;
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
        public function getStock() {
            return $this->stock;
        }
        public function getActivo() {
            return $this->activo;
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
        public function setStock($stock) {
            $this->stock = $stock;
        }
        public function setActivo($activo) {
            $this->activo = $activo;
        }
        public function toArray() {
            return [
                'id' => $this->id,
                'nombre' => $this->nombre,
                'isbn' => $this->isbn,
                'editorial' => $this->editorial ? $this->editorial->toArray() : null,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'activo' => $this->activo
            ];
        }
    }
    
?>