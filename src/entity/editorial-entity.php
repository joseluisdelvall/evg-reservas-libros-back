<?php
    
    class EditorialEntity {
        private $idEditorial;
        private $nombre;
        private $telefono;
        private $correo;
        private $estado;
        
        public function __construct($idEditorial = null, $nombre = null, $telefono = null, $correo = null, $estado = null) {
            $this->idEditorial = $idEditorial;
            $this->nombre = $nombre;
            $this->telefono = $telefono;
            $this->correo = $correo;
            $this->estado = $estado;
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

        public function getEstado() {
            return $this->estado;
        }

        public function toArray() {
            return [
                'idEditorial' => $this->getId(),
                'nombre' => $this->getNombre(),
                'telefono' => $this->getTelefono(),
                'correo' => $this->getCorreo(),
                'estado' => $this->getEstado()
            ];
        }
    }
    
?>