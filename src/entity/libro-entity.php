<?php
    
    class LibroEntity {

        private $id;
        private $nombre;
        private $isbn;
        private $editorial;
        private $precio;
        private $stock;
        private $estado;

        public function __construct($id = null, $nombre = null, $isbn = null, $editorial = null, $precio = null, $stock = null, $estado = null) {
            $this->id = $id;
            $this->nombre = $nombre;
            $this->isbn = $isbn;
            $this->editorial = $editorial;
            $this->precio = $precio;
            $this->stock = $stock;
            $this->estado = $estado;
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
        public function getEstado() {
            return $this->estado;
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
        public function setEstado($estado) {
            $this->estado = $estado;
        }
        public function toArray() {
            return [
                'id' => $this->id,
                'nombre' => $this->nombre,
                'isbn' => $this->isbn,
                'editorial' => $this->editorial,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'estado' => $this->estado
            ];
        }
    }
    
?>