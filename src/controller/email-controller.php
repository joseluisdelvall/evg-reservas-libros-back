<?php
require_once '../src/service/email-service.php';
require_once '../src/utils/response.php';
require_once '../src/middleware/auth-middleware.php';

class EmailController {
    private $emailService;
    private $authMiddleware;
    
    public function __construct() {
        $this->emailService = new EmailService();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Método privado para verificar la autenticación
     * @return bool|void Retorna true si está autenticado o termina la ejecución si no lo está
     */
    private function verificarAuth() {
        $resultado = $this->authMiddleware->verificarAutenticacion();
        if ($resultado !== true) {
            echo json_encode($resultado);
            exit;
        }
        return true;
    }
    
    public function sendTestEmail() {
        // Verificar autenticación antes de proceder
        $this->verificarAuth();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['email'])) {
                throw new Exception('Email es requerido');
            }
            
            $toEmail = $data['email'];
            $toName = $data['name'] ?? '';
            
            $this->emailService->sendTestEmail($toEmail, $toName);
            
            return response('success', 'Correo de prueba enviado correctamente', [
                'email' => $toEmail,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
}
?>
