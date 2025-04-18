<?php
// Configuración de encabezados CORS para permitir solicitudes desde Angular
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // 24 horas de caché para solicitudes preflight

// Activar registro de errores en un archivo de log
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar la salida de errores para no romper el JSON
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/auth_errors.log');

// Crear un archivo de registro de autenticación
$logFile = __DIR__ . '/../logs/auth_activity.log';
$logDir = dirname($logFile);

// Crear directorio de logs si no existe
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Función para registrar actividad
function logAuthActivity($message, $data = []) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}";
    
    if (!empty($data)) {
        $logEntry .= " - " . json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    
    file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND);
}

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

logAuthActivity('Inicio de solicitud de login');

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
    logAuthActivity('Error: No se proporcionó token ID');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No ID token provided']);
    exit();
}

logAuthActivity('Token ID recibido, procediendo a verificar');

$client = new \Google_Client(['client_id' => $CLIENT_ID]);  // Especificar el CLIENT_ID
try {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        $userid = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
        
        logAuthActivity('Token verificado correctamente', ['email' => $email]);
        
        // Conectar a la base de datos directamente para verificar si el usuario existe
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=reservalibros;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            logAuthActivity('Conexión a base de datos establecida');
            
            // Verificar si el correo está en la tabla de usuarios autorizados
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Registrar resultado de la consulta
            if ($user) {
                logAuthActivity('Usuario encontrado en la base de datos', ['id' => $user['id'], 'email' => $user['email']]);
                
                // Actualizar Google ID si es necesario
                if ($user['google_id'] !== $userid) {
                    $updateStmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                    $updateStmt->execute([$userid, $user['id']]);
                    logAuthActivity('Google ID actualizado para el usuario', ['id' => $user['id']]);
                }
                
                // Generar JWT
                $key = 'clave_secreta_temporal_para_pruebas';
                $payload = [
                    'iss' => 'evg-reservas-libros',
                    'aud' => 'app-usuarios',
                    'iat' => time(),
                    'nbf' => time(),
                    'exp' => time() + (60 * 60), // 1 hora de expiración
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['nombre'],
                ];
                
                $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
                logAuthActivity('JWT generado correctamente para el usuario', ['id' => $user['id']]);
                
                // Enviar respuesta exitosa al frontend
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful', 
                    'token' => $jwt, 
                    'user' => [
                        'id' => $user['id'], 
                        'email' => $user['email'], 
                        'name' => $user['nombre']
                    ]
                ]);
                logAuthActivity('Respuesta de éxito enviada');
                exit();
            } else {
                logAuthActivity('Usuario NO encontrado en la base de datos', ['email' => $email]);
                
                // Usuario no autorizado
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'El correo electrónico no está autorizado para acceder al sistema',
                    'email' => $email,
                    'error_code' => 'unauthorized_email'
                ]);
                logAuthActivity('Respuesta de no autorizado enviada');
                exit();
            }
        } catch (PDOException $e) {
            // Error de base de datos
            logAuthActivity('Error de base de datos', ['error' => $e->getMessage()]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'error_code' => 'database_error'
            ]);
            exit();
        }
    } else {
        // Token ID inválido
        logAuthActivity('Token ID inválido');
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid ID token']);
        exit();
    }
} catch (\Exception $e) {
    logAuthActivity('Error al verificar token', ['error' => $e->getMessage()]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error verifying ID token: ' . $e->getMessage()]);
    exit();
}
?>
