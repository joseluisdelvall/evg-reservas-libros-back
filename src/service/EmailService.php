<?php

namespace ProyectoFinal\ReservasLibros\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mail;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;

    public function __construct(
        string $smtpHost = 'smtp.gmail.com',
        int $smtpPort = 587,
        string $smtpUsername = '',
        string $smtpPassword = '',
        string $fromEmail = '',
        string $fromName = 'Sistema de Reservas de Libros'
    ) {
        $this->smtpHost = $smtpHost;
        $this->smtpPort = $smtpPort;
        $this->smtpUsername = $smtpUsername;
        $this->smtpPassword = $smtpPassword;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        
        $this->initializePHPMailer();
    }

    private function initializePHPMailer(): void
    {
        $this->mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host = $this->smtpHost;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->smtpUsername;
            $this->mail->Password = $this->smtpPassword;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $this->smtpPort;

            // Configuración del remitente
            $this->mail->setFrom($this->fromEmail, $this->fromName);
            
            // Configuración de caracteres
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML(true);

        } catch (Exception $e) {
            throw new \Exception("Error al configurar PHPMailer: {$this->mail->ErrorInfo}");
        }
    }

    /**
     * Envía un correo electrónico
     */
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = null,
        array $attachments = []
    ): bool {
        try {
            // Limpiar destinatarios anteriores
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            // Configurar destinatario
            $this->mail->addAddress($toEmail, $toName);

            // Configurar asunto y cuerpo
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            
            if ($textBody) {
                $this->mail->AltBody = $textBody;
            }

            // Agregar archivos adjuntos si los hay
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $name = $attachment['name'] ?? basename($attachment['path']);
                    $this->mail->addAttachment($attachment['path'], $name);
                }
            }

            return $this->mail->send();

        } catch (Exception $e) {
            error_log("Error al enviar correo: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Envía un correo de confirmación de reserva
     */
    public function sendReservationConfirmation(string $userEmail, string $userName, array $bookDetails): bool
    {
        $subject = 'Confirmación de Reserva de Libro';
        
        $htmlBody = $this->generateReservationConfirmationHTML($userName, $bookDetails);
        $textBody = $this->generateReservationConfirmationText($userName, $bookDetails);

        return $this->sendEmail($userEmail, $userName, $subject, $htmlBody, $textBody);
    }

    /**
     * Envía un correo de recordatorio de devolución
     */
    public function sendReturnReminder(string $userEmail, string $userName, array $bookDetails, string $dueDate): bool
    {
        $subject = 'Recordatorio: Devolución de Libro Próxima';
        
        $htmlBody = $this->generateReturnReminderHTML($userName, $bookDetails, $dueDate);
        $textBody = $this->generateReturnReminderText($userName, $bookDetails, $dueDate);

        return $this->sendEmail($userEmail, $userName, $subject, $htmlBody, $textBody);
    }

    /**
     * Genera el HTML para la confirmación de reserva
     */
    private function generateReservationConfirmationHTML(string $userName, array $bookDetails): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .book-details { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4CAF50; }
                .footer { padding: 20px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Reserva Confirmada!</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$userName}</strong>,</p>
                    <p>Tu reserva ha sido confirmada exitosamente.</p>
                    
                    <div class='book-details'>
                        <h3>Detalles del libro:</h3>
                        <p><strong>Título:</strong> {$bookDetails['title']}</p>
                        <p><strong>Autor:</strong> {$bookDetails['author']}</p>
                        <p><strong>ISBN:</strong> {$bookDetails['isbn']}</p>
                        <p><strong>Fecha de reserva:</strong> {$bookDetails['reservation_date']}</p>
                        <p><strong>Fecha límite de recogida:</strong> {$bookDetails['pickup_deadline']}</p>
                    </div>
                    
                    <p>Por favor, recuerda recoger el libro antes de la fecha límite indicada.</p>
                </div>
                <div class='footer'>
                    <p>Sistema de Reservas de Libros</p>
                    <p>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Genera el texto plano para la confirmación de reserva
     */
    private function generateReservationConfirmationText(string $userName, array $bookDetails): string
    {
        return "
¡Reserva Confirmada!

Estimado/a {$userName},

Tu reserva ha sido confirmada exitosamente.

Detalles del libro:
- Título: {$bookDetails['title']}
- Autor: {$bookDetails['author']}
- ISBN: {$bookDetails['isbn']}
- Fecha de reserva: {$bookDetails['reservation_date']}
- Fecha límite de recogida: {$bookDetails['pickup_deadline']}

Por favor, recuerda recoger el libro antes de la fecha límite indicada.

Sistema de Reservas de Libros
Este es un correo automático, por favor no responder.
        ";
    }

    /**
     * Genera el HTML para el recordatorio de devolución
     */
    private function generateReturnReminderHTML(string $userName, array $bookDetails, string $dueDate): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF9800; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .book-details { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #FF9800; }
                .footer { padding: 20px; text-align: center; color: #666; }
                .warning { color: #FF9800; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Recordatorio de Devolución</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$userName}</strong>,</p>
                    <p class='warning'>Este es un recordatorio de que tienes un libro que debe ser devuelto pronto.</p>
                    
                    <div class='book-details'>
                        <h3>Detalles del libro:</h3>
                        <p><strong>Título:</strong> {$bookDetails['title']}</p>
                        <p><strong>Autor:</strong> {$bookDetails['author']}</p>
                        <p><strong>ISBN:</strong> {$bookDetails['isbn']}</p>
                        <p><strong>Fecha límite de devolución:</strong> <span class='warning'>{$dueDate}</span></p>
                    </div>
                    
                    <p>Por favor, devuelve el libro antes de la fecha límite para evitar penalizaciones.</p>
                </div>
                <div class='footer'>
                    <p>Sistema de Reservas de Libros</p>
                    <p>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Genera el texto plano para el recordatorio de devolución
     */
    private function generateReturnReminderText(string $userName, array $bookDetails, string $dueDate): string
    {
        return "
Recordatorio de Devolución

Estimado/a {$userName},

Este es un recordatorio de que tienes un libro que debe ser devuelto pronto.

Detalles del libro:
- Título: {$bookDetails['title']}
- Autor: {$bookDetails['author']}
- ISBN: {$bookDetails['isbn']}
- Fecha límite de devolución: {$dueDate}

Por favor, devuelve el libro antes de la fecha límite para evitar penalizaciones.

Sistema de Reservas de Libros
Este es un correo automático, por favor no responder.
        ";
    }

    /**
     * Obtiene información del último error
     */
    public function getLastError(): string
    {
        return $this->mail->ErrorInfo;
    }
} 