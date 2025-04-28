<?php
    
    class EditorialMinDto {
        private $idEditorial;
        private $nombre;

        public function __construct($idEditorial, $nombre) {
            $this->idEditorial = $idEditorial;
            $this->nombre = $nombre;
        }
        public function getId() {
            return $this->idEditorial;
        }
        
        public function getNombre() {
            return $this->nombre;
        }
        public function setNombre($nombre) {
            $this->nombre = $nombre;
        }
        public function toArray() {
            return [
                'idEditorial' => $this->getId(),
                'nombre' => $this->getNombre()
            ];
        }
        public function toJson() {
            return json_encode($this->toArray());
        }
    }
    
?>