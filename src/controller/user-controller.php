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

            // Obtener el cuerpo de la solicitud como JSON
            $jsonData = file_get_contents('php://input');
            $requestData = json_decode($jsonData, true);
            
            // Verificar si se recibió el token
            if (!isset($requestData['id_token']) || empty($requestData['id_token'])) {
                return [
                    'status' => 'error',
                    'message' => 'No se proporcionó un token de autenticación'
                ];
            }
            
            // Extraer el token
            $token = $requestData['id_token'];

            // Decodificar el token JWT
            $decodedToken = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))), true);
            
            if (!$decodedToken) {
                return [
                    'status' => 'error',
                    'message' => 'Token inválido'
                ];
            }

            // Obtener el correo electrónico del token decodificado
            $email = $decodedToken['email'] ?? null;

            //EMAIL DE PRUEBA
            $user = $this->UserService->getUserByEmail($email);

            if(!$user) {
                return [
                    'status' => 'error',
                    'message' => 'El usuario no está autorizado para acceder al sistema'
                ];
                exit;
            }

            $userDto = new UserDto($user->getId(),$user->getGoogleId(), $user->getNombre(), $user->getEmail());

            return [
                'success' => true,
                'user' => $userDto->toArray(),
                'token' => $token
            ];
        }

    }
?>