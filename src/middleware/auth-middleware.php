<?php
require_once '../src/service/user-service.php';
require_once '../src/utils/response.php';

class AuthMiddleware {
    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    /**
     * Verifica si el token es válido y el usuario está autorizado
     * @return bool|array Retorna true si el token es válido, o un array con el error si no lo es
     */
    public function verificarAutenticacion() {
        try {
            // Obtener el token del header
            $headers = getallheaders();
            $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

            if (empty($auth_header)) {
                return response('error', 'No hay token', null, 401);
            }

            // Extraer el token (eliminar "Bearer ")
            $token = str_replace('Bearer ', '', $auth_header);

            // Decodificar el token JWT
            $decodedToken = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))), true);
            
            if (!$decodedToken) {
                return response('error', 'Token inválido', null, 401);
            }

            // Comprobar si el token ha expirado
            if (isset($decodedToken['exp']) && $decodedToken['exp'] < time()) {
                return response('error', 'El token ha expirado', null, 401);
            }

            // Comprobar si el usuario está autorizado
            if (!isset($decodedToken['email']) || !$this->userService->isUserAuthorized($decodedToken['email'])) {
                return response('error', 'Usuario no autorizado', null, 401);
            }

            // Si todo está bien, retornar true
            return true;

        } catch (Exception $e) {
            return response('error', 'Error al verificar el token: ' . $e->getMessage(), null, 500);
        }
    }
} 