<?php
      require_once __DIR__ . '/../../config/emailconfig.php';
    require_once __DIR__ . '/../../vendor/autoload.php'; // Asegúrate de tener PHPMailer instalado via Composer

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

        /**
         * Envía un correo electrónico utilizando una plantilla HTML.
         *
         * Este método limpia los destinatarios y archivos adjuntos previos, agrega el nuevo destinatario,
         * renderiza la plantilla HTML con los datos proporcionados, y envía el correo electrónico.
         *
         * @param string $emailDestino Email del destinatario
         * @param string $asunto Asunto del correo
         * @param string $plantilla Nombre de la plantilla (sin extensión .html)
         * @param array $datos Array asociativo con los datos para reemplazar en la plantilla
         * @param string $nombreDestino Nombre del destinatario (opcional)
         * @return bool Retorna true si el correo se envía correctamente.
         * @throws Exception Si ocurre un error durante el envío del correo, lanza una excepción con el mensaje de error correspondiente.
         */
        public function sendEmail($emailDestino, $asunto, $plantilla, $datos, $nombreDestino = '') {
            try {
                // Limpiar destinatarios previos
                $this->mail->clearAddresses();
                $this->mail->clearAttachments();
                
                // Destinatario
                $this->mail->addAddress($emailDestino, $nombreDestino);
                
                // Renderizar la plantilla con los datos
                $html = $this->renderPlantilla($plantilla, $datos);
                
                // Contenido del correo
                $this->mail->isHTML(true);
                $this->mail->Subject = $asunto;
                $this->mail->Body    = $html;
                $this->mail->AltBody = strip_tags($html);
                
                $this->mail->send();
                return true;
                
            } catch (Exception $e) {
                throw new Exception("Error enviando correo: {$this->mail->ErrorInfo}");
            }
        }

        private function renderPlantilla($nombrePlantilla, $datos) {
            $ruta = __DIR__ . '/../plantillas-emails/' . $nombrePlantilla . '.html';
            
            if (!file_exists($ruta)) {
                throw new Exception("Plantilla de email no encontrada: " . $nombrePlantilla);
            }
            
            $html = file_get_contents($ruta);
            
            // Procesar condiciones del tutor
            if (isset($datos['tieneTutor']) && $datos['tieneTutor']) {
                // Mostrar sección del tutor
                $html = preg_replace('/\{\{#tieneTutor\}\}(.*?)\{\{\/tieneTutor\}\}/s', '$1', $html);
            } else {
                // Ocultar sección del tutor
                $html = preg_replace('/\{\{#tieneTutor\}\}(.*?)\{\{\/tieneTutor\}\}/s', '', $html);
            }
            
            // Procesar lista de libros
            if (isset($datos['libros']) && is_array($datos['libros'])) {
                $librosHtml = '';
                foreach ($datos['libros'] as $libro) {
                    $precioFormateado = number_format($libro['precio'], 2, ',', '.');
                    $librosHtml .= '
                    <div class="libro-item">
                        <div>
                            <div class="libro-nombre">' . htmlspecialchars($libro['nombre']) . '</div>
                            <div><small>Estado: ' . htmlspecialchars($libro['estado']) . '</small></div>
                        </div>
                        <div class="libro-precio">' . $precioFormateado . '€</div>
                    </div>';
                }
                
                // Reemplazar el bloque de libros
                $html = preg_replace('/\{\{#each libros\}\}(.*?)\{\{\/each\}\}/s', $librosHtml, $html);
            }
            
            // Reemplazar variables simples (excluyendo libros que ya se procesó)
            foreach ($datos as $clave => $valor) {
                if ($clave !== 'libros' && !is_array($valor)) {
                    $html = str_replace('{{' . $clave . '}}', $valor, $html);
                }
            }
            
            return $html;
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