<?php
    
    class EditorialLibrosPendientesDto {
        private $idEditorial;
        private $nombre;
        private $librosPendientes;

        public function __construct($idEditorial, $nombre, $librosPendientes) {
            $this->idEditorial = $idEditorial;
            $this->nombre = $nombre;
            $this->librosPendientes = $librosPendientes;
        }
        
        public function getIdEditorial() {
            return $this->idEditorial;
        }
        
        public function getNombre() {
            return $this->nombre;
        }
        
        public function getLibrosPendientes() {
            return $this->librosPendientes;
        }
        
        public function toArray() {
            return [
                'idEditorial' => $this->getIdEditorial(),
                'nombre' => $this->getNombre(),
                'librosPendientes' => $this->getLibrosPendientes()
            ];
        }
        
        public function toJson() {
            return json_encode($this->toArray());
        }
    }
    
?> 