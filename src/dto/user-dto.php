<?php

    class UserDto {
        
        private $id;
        private $googleId;
        private $email;
        private $nombre;

        public function __construct($id, $googleId, $email, $nombre) {
            $this->id = $id;
            $this->googleId = $googleId;
            $this->email = $email;
            $this->nombre = $nombre;
        }

        public function getId() {
            return $this->id;
        }

        public function getGoogleId() {
            return $this->googleId;
        }

        public function getEmail() {
            return $this->email;
        }

        public function getNombre() {
            return $this->nombre;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function setGoogleId($googleId) {
            $this->googleId = $googleId;
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
                'id' => $this->id,
                'googleId' => $this->googleId,
                'email' => $this->email,
                'nombre' => $this->nombre
            ];
        }        
        
    }

?>