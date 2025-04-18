<?php
// Configuración de encabezados CORS para permitir solicitudes desde Angular
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // 24 horas de caché para solicitudes preflight

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Activar el reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar la salida de errores para no romper el JSON

require_once __DIR__ . '/../vendor/autoload.php'; // Ruta autoloader

// Cliente ID de Google
$CLIENT_ID = '660176374148-klpm52u3brlqsmpjvqci3ruk5qk1ofnl.apps.googleusercontent.com';

// Verificación del token
// Obtener datos JSON de la solicitud POST
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$id_token = $input['id_token'] ?? '';

// Si no hay token, devolver error
if (empty($id_token)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No ID token provided']);
    exit();
}

$client = new \Google_Client(['client_id' => $CLIENT_ID]);  // Especificar el CLIENT_ID
try {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        $userid = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
        
        // Lista de correos electrónicos autorizados (para pruebas)
        // En una implementación real, consultarías la base de datos
        $authorized_emails = [
            // Ya no autorizamos automáticamente cualquier email
            'celiamorunoherrojo.guadalupe@alumnado.fundacionloyola.net',
            'usuario@ejemplo.com'
        ];
        
        if (in_array($email, $authorized_emails)) {
            // Generar un JWT simple
            $key = 'clave_secreta_temporal_para_pruebas';
            $payload = [
                'iss' => 'evg-reservas-libros',
                'aud' => 'app-usuarios',
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + (60 * 60), // 1 hora de expiración
                'user_id' => $userid,
                'email' => $email,
                'name' => $name,
            ];
            
            $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
            
            // Enviar respuesta exitosa al frontend con el JWT
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful', 
                'token' => $jwt, 
                'user' => [
                    'id' => $userid, 
                    'email' => $email, 
                    'name' => $name
                ]
            ]);
            exit();
        } else {
            // El correo electrónico no está autorizado
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'El correo electrónico no está autorizado para acceder al sistema',
                'email' => $email,
                'error_code' => 'unauthorized_email'
            ]);
            exit();
        }
    } else {
        // Token ID inválido
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid ID token']);
        exit();
    }
} catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error verifying ID token: ' . $e->getMessage()]);
    exit();
}
?>