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
         * Comprueba si el token es válido y si el usuario tiene acceso al sistema.
         * 
         * @return array Respuesta con el estado de la operación y los datos del usuario.
         *               Si el token es válido y el usuario tiene acceso, se devuelve un array con los datos del usuario.
        */
        public function userLogin() {

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

            global $config;
            $DOMINIO_CORREO = $config['dominio_correo'];

            // Comprobar si el correo tiene alguno de los dominios permitidos
            /*if ( !isset($decodedToken['hd']) || $decodedToken['hd'] !== $DOMINIO_CORREO) {
                return [
                    'status' => 'error',
                    'message' => 'El correo electrónico no es válido'
                ];
                exit;
            }*/
            $dominiosPermitidos = explode(';', $DOMINIO_CORREO);
            $dominioValido = false;

            if (isset($decodedToken['hd'])) {
                foreach ($dominiosPermitidos as $dominio) {
                    if (trim($dominio) === $decodedToken['hd']) {
                        $dominioValido = true;
                        break;
                    }
                }
            }

            if (!$dominioValido) {
                return [
                    'status' => 'error',
                    'message' => 'El correo electrónico no es válido'
                ];
            }

            // Comprobar si el token ha expirado
            if (isset($decodedToken['exp']) && $decodedToken['exp'] < time()) {
                return [
                    'status' => 'error',
                    'message' => 'El token ha expirado'
                ];
            }

            // Comprobar rol de usuario en base de datos
            if (!$this->UserService->isUserAuthorized($decodedToken['email'])) {
                return [
                    'status' => 'error',
                    'message' => 'El usuario no está autorizado para acceder al sistema ROL'
                ];
            }

            // Verificar si el correo está registrado en la base de datos
            $user = $this->UserService->isUserRegister($decodedToken['email']);

            if(!$user) {
                return [
                    'status' => 'error',
                    'message' => 'El usuario no está autorizado para acceder al sistema'
                ];
                exit;
            }

            // Crear un nuevo objeto UserDto con los datos del usuario
            $userDto = new UserDto($user->getIdUsuario(), $user->getNombre(), $user->getEmail());

            // Devolver la respuesta con los datos del usuario y el token
            return [
                'success' => true,
                'user' => $userDto->toArray(),
                'token' => $token
            ];

        }

    }
?>