<?php

require_once '../src/service/reservas-service.php';
require_once '../src/utils/response.php';

class ReservasController {
    private $reservasService;
    
    public function __construct() {
        $this->reservasService = new ReservasService();
    }
    
    /**
     * Crea una nueva reserva a partir de los datos enviados desde el formulario
     * 
     * @return array Respuesta con el estado de la operación
     */
    public function createReserva() {
        try {
            // Obtener los datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar que haya datos
            if (empty($data)) {
                return response('error', 'No se recibieron datos del formulario');
            }
            
            // Llamar al servicio para crear la reserva
            $reservaDto = $this->reservasService->createReserva($data);
            
            // Enviar correo de confirmación
            $this->sendConfirmationEmail($reservaDto);
            
            // Devolver respuesta exitosa
            return response('success', 'Reserva creada correctamente', $reservaDto->toArray());
            
        } catch (Exception $e) {
            return response('error', $e->getMessage());
        }
    }
    
    /**
     * Envía un correo de confirmación al usuario
     * 
     * @param ReservaDto $reserva Datos de la reserva
     * @return bool Verdadero si el correo se envió correctamente
     */
    private function sendConfirmationEmail($reserva) {
        try {
            // Implementación básica de envío de correo
            $to = $reserva->toArray()['correo'];
            $subject = "Confirmación de Reserva - EVG Reservas de Libros";
            
            $message = "<html><body>";
            $message .= "<h2>Confirmación de Reserva</h2>";
            $message .= "<p>Estimado/a " . $reserva->toArray()['nombreAlumno'] . " " . $reserva->toArray()['apellidosAlumno'] . ",</p>";
            $message .= "<p>Su reserva ha sido registrada correctamente.</p>";
            $message .= "<p>Detalles de la reserva:</p>";
            $message .= "<ul>";
            $message .= "<li>Fecha de reserva: " . $reserva->toArray()['fecha'] . "</li>";
            $message .= "<li>Estado: Pendiente de verificación</li>";
            $message .= "</ul>";
            $message .= "<p>Nos pondremos en contacto con usted para confirmar la disponibilidad de los libros.</p>";
            $message .= "<p>Gracias por confiar en nuestro servicio.</p>";
            $message .= "<p>Atentamente,<br>Equipo de Reservas de Libros EVG</p>";
            $message .= "</body></html>";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: reservas@evg.es' . "\r\n";
            
            return mail($to, $subject, $message, $headers);
            
        } catch (Exception $e) {
            error_log("Error al enviar el correo de confirmación: " . $e->getMessage());
            return false;
        }
    }
}
?> 