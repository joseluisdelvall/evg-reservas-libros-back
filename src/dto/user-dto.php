<?php

    class UserDto {
        
        private $idUsuario;
        private $email;
        private $nombre;

        public function __construct($idUsuario, $email, $nombre) {
            $this->idUsuario = $idUsuario;
            $this->email = $email;
            $this->nombre = $nombre;
        }

        public function getIdUsuario() {
            return $this->idUsuario;
        }

        public function getEmail() {
            return $this->email;
        }

        public function getNombre() {
            return $this->nombre;
        }

        public function setIdUsuario($idUsuario) {
            $this->idUsuario = $idUsuario;
        }

        public function setEmail($email) {
            $this->email = $email;
        }

        public function setNombre($nombre) {
            $this->nombre = $nombre;
        }

        // EN TODOS LOS DTOs se debe hacer un toArray(), para que se pueda enviar al cliente
        public function toArray() {
            return [
                'idUsuario' => $this->idUsuario,
                'email' => $this->email,
                'nombre' => $this->nombre
            ];
        }        
        
    }

?>