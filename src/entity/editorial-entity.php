<?php
    
    class EditorialEntity {
        private $idEditorial;
        private $nombre;
        private $telefono1;
        private $telefono2;
        private $telefono3;
        private $correo1;
        private $correo2;
        private $correo3;
        private $estado;
        
        public function __construct(
            $idEditorial = null, 
            $nombre = null, 
            $telefono1 = null, 
            $telefono2 = null, 
            $telefono3 = null, 
            $correo1 = null, 
            $correo2 = null, 
            $correo3 = null, 
            $estado = null
        ) {
            $this->idEditorial = $idEditorial;
            $this->nombre = $nombre;
            $this->telefono1 = $telefono1;
            $this->telefono2 = $telefono2;
            $this->telefono3 = $telefono3;
            $this->correo1 = $correo1;
            $this->correo2 = $correo2;
            $this->correo3 = $correo3;
            $this->estado = $estado;
        }
        
        public function getId() {
            return $this->idEditorial;
        }
        
        public function getNombre() {
            return $this->nombre;
        }

        public function getTelefono1() {
            return $this->telefono1;
        }

        public function getTelefono2() {
            return $this->telefono2;
        }

        public function getTelefono3() {
            return $this->telefono3;
        }

        public function getCorreo1() {
            return $this->correo1;
        }

        public function getCorreo2() {
            return $this->correo2;
        }

        public function getCorreo3() {
            return $this->correo3;
        }

        public function getEstado() {
            return $this->estado;
        }
        
        public function setId($idEditorial) {
            $this->idEditorial = $idEditorial;
            return $this;
        }
        
        public function setNombre($nombre) {
            $this->nombre = $nombre;
            return $this;
        }
        
        public function setTelefono1($telefono1) {
            $this->telefono1 = $telefono1;
            return $this;
        }
        
        public function setTelefono2($telefono2) {
            $this->telefono2 = $telefono2;
            return $this;
        }
        
        public function setTelefono3($telefono3) {
            $this->telefono3 = $telefono3;
            return $this;
        }
        
        public function setCorreo1($correo1) {
            $this->correo1 = $correo1;
            return $this;
        }
        
        public function setCorreo2($correo2) {
            $this->correo2 = $correo2;
            return $this;
        }
        
        public function setCorreo3($correo3) {
            $this->correo3 = $correo3;
            return $this;
        }
        
        public function setEstado($estado) {
            $this->estado = $estado;
            return $this;
        }
        
        // Métodos helper para obtener arrays de teléfonos y correos
        // para mantener compatibilidad con el código existente
        public function getTelefonos() {
            $telefonos = [];
            if (!empty($this->telefono1)) $telefonos[] = $this->telefono1;
            if (!empty($this->telefono2)) $telefonos[] = $this->telefono2;
            if (!empty($this->telefono3)) $telefonos[] = $this->telefono3;
            return $telefonos;
        }
        
        public function getCorreos() {
            $correos = [];
            if (!empty($this->correo1)) $correos[] = $this->correo1;
            if (!empty($this->correo2)) $correos[] = $this->correo2;
            if (!empty($this->correo3)) $correos[] = $this->correo3;
            return $correos;
        }

        public function toArray() {
            return [
                'idEditorial' => $this->getId(),
                'nombre' => $this->getNombre(),
                'telefonos' => $this->getTelefonos(),
                'correos' => $this->getCorreos(),
                'estado' => $this->getEstado()
            ];
        }
    }
    
?>