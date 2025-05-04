<?php

    include '../src/repository/user-repository.php';

    class UserService {
        
        private $UserRepository;

        public function __construct() {
            $this->UserRepository = new UserRepository();
        }

        public function getUserByEmail(string $email): ?UserEntity {
            return $this->UserRepository->getUserByEmail($email);
        }

        public function isUserAuthorized(string $email): bool {
            return $this->UserRepository->isUserAuthorized($email);
        }
        
    }
?>