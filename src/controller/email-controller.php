<?php
require_once '../src/service/email-service.php';
require_once '../src/utils/response.php';

class EmailController {
    private $emailService;
    
    public function __construct() {
        $this->emailService = new EmailService();
    }
    
    public function sendTestEmail() {
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
