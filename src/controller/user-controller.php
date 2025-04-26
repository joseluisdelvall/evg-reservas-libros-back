<?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once '../src/service/user-service.php';
    require_once '../src/dto/user-dto.php';

    class UserController {

        private $UserService;

        public function __construct() {
            // Inicializar el servicio
            $this->UserService = new UserService();
        }

        /**
         * Obtiene el usuario por correo electrónico
         * 
         * @return array Respuesta con el usuario encontrado
         */
        public function getUserByEmail() {
            
            //EMAIL DE PRUEBA
            $user = $this->UserService->getUserByEmail('celiamorunoherrojo.guadalupe@alumnado.fundacionloyola.net');

            if(!$user) {
                return [
                    'status' => 'error',
                    'message' => 'El usuario no está autorizado para acceder al sistema'
                ];
                exit;
            }

            $userDto = new UserDto($user->getId(),$user->getGoogleId(), $user->getNombre(), $user->getEmail());

            return [
                'status' => 'success',
                'data' => $userDto->toArray()
            ];
        }

    }
?>