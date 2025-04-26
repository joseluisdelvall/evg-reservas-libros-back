<?php

    include '../src/repository/user-repository.php';

    class UserService {
        
        private $UserRepository;

        public function __construct() {
            $this->UserRepository = new UserRepository();
        }

        public function getUsuarioByCorreo(string $correo): ?UserEntity {
            return $this->UserRepository->getUsuarioByCorreo($correo);
        }
        
    }
?>