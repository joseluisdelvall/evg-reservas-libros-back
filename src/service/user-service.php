<?php

    include '../src/repository/user-repository.php';

    class UserService {
        
        private $UserRepository;

        public function __construct() {
            $this->UserRepository = new UserRepository();
        }

        public function isUserRegister(string $email): ?UserEntity {
            return $this->UserRepository->isUserRegister($email);
        }

        public function isUserAuthorized(string $email): bool {
            return $this->UserRepository->isUserAuthorized($email);
        }
        
    }
?>