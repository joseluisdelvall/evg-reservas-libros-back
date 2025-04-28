<?php
    
    class EditorialEntity {
        private $idEditorial;
        private $nombre;
        private $telefono;
        private $correo;
        
        public function __construct($idEditorial, $nombre, $telefono = null, $correo = null) {
            $this->idEditorial = $idEditorial;
            $this->nombre = $nombre;
            $this->telefono = $telefono;
            $this->correo = $correo;
        }
        
        public function getId() {
            return $this->idEditorial;
        }
        
        public function getNombre() {
            return $this->nombre;
        }

        public function getTelefono() {
            return $this->telefono;
        }

        public function getCorreo() {
            return $this->correo;
        }

        public function toArray() {
            return [
                'idEditorial' => $this->getId(),
                'nombre' => $this->getNombre(),
                'telefono' => $this->getTelefono(),
                'correo' => $this->getCorreo()
            ];
        }
    }
    
?>