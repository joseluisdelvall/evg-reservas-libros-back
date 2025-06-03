<?php
    
    require_once '../config/emailconfig.php';
    require_once '../vendor/autoload.php'; // Asegúrate de tener PHPMailer instalado via Composer

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    class EmailService {
        
        private $mail;
        
        public function __construct() {
            $this->mail = new PHPMailer(true);
            $this->configureSMTP();
        }
        
        private function configureSMTP() {
            try {
                // Configuración del servidor SMTP
                $this->mail->isSMTP();
                $this->mail->Host       = EMAIL_HOST;
                $this->mail->SMTPAuth   = true;
                $this->mail->Username   = EMAIL_USERNAME;
                $this->mail->Password   = EMAIL_PASSWORD;
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->mail->Port       = EMAIL_PORT;
                
                // Configuración del remitente
                $this->mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
                
                // Configuración de caracteres
                $this->mail->CharSet = 'UTF-8';
                
                // Habilitar debug para desarrollo (opcional)
                // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
                
            } catch (Exception $e) {
                throw new Exception("Error configurando SMTP: {$this->mail->ErrorInfo}");
            }
        }
        
        public function sendTestEmail($toEmail, $toName = '') {
            try {
                // Limpiar destinatarios previos
                $this->mail->clearAddresses();
                $this->mail->clearAttachments();
                
                // Destinatario
                $this->mail->addAddress($toEmail, $toName);
                
                // Contenido del correo
                $this->mail->isHTML(true);
                $this->mail->Subject = 'Correo de Prueba - Sistema de Reservas de Libros';
                $this->mail->Body    = '
                    <h2>¡Correo de Prueba Exitoso!</h2>
                    <p>Este es un correo de prueba del sistema de reservas de libros.</p>
                    <p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s') . '</p>
                    <p><strong>Destinatario:</strong> ' . $toEmail . '</p>
                    <br>
                    <p>Si recibes este correo, la configuración del sistema de email está funcionando correctamente.</p>
                    <hr>
                    <p><small>Sistema de Reservas de Libros - EVG</small></p>
                ';
                $this->mail->AltBody = 'Correo de prueba del sistema de reservas de libros. Fecha: ' . date('d/m/Y H:i:s') . '. Si recibes este correo, la configuración está funcionando correctamente.';
                
                $this->mail->send();
                return true;
                
            } catch (Exception $e) {
                throw new Exception("Error enviando correo: {$this->mail->ErrorInfo}");
            }
        }
        
        public function sendCustomEmail($toEmail, $toName, $subject, $htmlBody, $altBody = '') {
            try {
                // Limpiar destinatarios previos
                $this->mail->clearAddresses();
                $this->mail->clearAttachments();
                
                // Destinatario
                $this->mail->addAddress($toEmail, $toName);
                
                // Contenido del correo
                $this->mail->isHTML(true);
                $this->mail->Subject = $subject;
                $this->mail->Body    = $htmlBody;
                $this->mail->AltBody = $altBody ?: strip_tags($htmlBody);
                
                $this->mail->send();
                return true;
                
            } catch (Exception $e) {
                throw new Exception("Error enviando correo: {$this->mail->ErrorInfo}");
            }
        }
    }

?>