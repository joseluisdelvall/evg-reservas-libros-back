<?php

require_once '../src/service/reservas-service.php';
require_once '../src/dto/reserva-curso-dto.php';
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

    /**
     * Obtiene todas las reservas
     * 
     * @return array Respuesta con el estado de la operación
     */
    public function getReservas() {
        $reservas = $this->reservasService->getAllReservas();

        if(!$reservas) {
            return response('error', 'No se han encontrado reservas', null, 404);
        }
        
        $reservasDto = array_map(function($reserva) {
            return new ReservaCursoDto(
                $reserva['id'],
                $reserva['nombreAlumno'],
                $reserva['apellidosAlumno'],
                $reserva['correo'],
                $reserva['telefono'],
                $reserva['fecha'],
                $reserva['verificado'],
                $reserva['totalPagado'],
                $reserva['nombreCurso']
            );
        }, $reservas);

        return response('success', 'Reservas obtenidas correctamente', array_map(function($dto) { 
            return $dto->toArray(); 
        }, $reservasDto));
    }

    /**
     * Obtiene los libros de una reserva por su ID
     * @param int $id ID de la reserva
     * @return array Respuesta con los libros
     */
    public function getLibrosByReservaId($id) {
        $libros = $this->reservasService->getLibrosByReservaId($id);
        if (!$libros) {
            return response('error', 'No se encontraron libros para la reserva', null, 404);
        }
        return response('success', 'Libros de la reserva obtenidos correctamente', $libros);
    }

    /**
     * Elimina una reserva por su ID
     * @param int $id ID de la reserva
     * @return array Respuesta con el estado de la operación
     */
    public function deleteReserva($id) {
        $resultado = $this->reservasService->deleteReserva($id);
        if ($resultado) {
            return response('success', 'Reserva eliminada correctamente');
        } else {
            return response('error', 'No se pudo eliminar la reserva', null, 500);
        }
    }

    /**
     * Obtiene una reserva por su ID
     * @param int $id ID de la reserva
     * @return array Respuesta con la reserva
     */
    public function getReservaById($id) {
        try {
            $reserva = $this->reservasService->getReservaById($id);
            if (!$reserva) {
                return response('error', 'No se encontró la reserva', null, 404);
            }

            $reservaDto = new ReservaCursoDto(
                $reserva['id'],
                $reserva['nombreAlumno'],
                $reserva['apellidosAlumno'],
                $reserva['correo'],
                $reserva['telefono'],
                $reserva['fecha'],
                $reserva['verificado'],
                $reserva['totalPagado'],
                $reserva['nombreCurso']
            );

            return response('success', 'Reserva obtenida correctamente', $reservaDto->toArray());
        } catch (Exception $e) {
            return response('error', $e->getMessage());
        }
    }
    
    /**
     * Cambia el estado de verificación de una reserva
     * @param int $id ID de la reserva
     * @return array Respuesta con el estado de la operación
     */
    public function cambiarEstadoReserva($id) {
        try {
            $result = $this->reservasService->cambiarEstadoReserva($id);
            return response('success', 'Estado de la reserva actualizado correctamente', $result);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }    /**
     * Anula una reserva por su ID
     * @param int $id ID de la reserva
     * @return array Respuesta con el estado de la operación
     */
    public function anularReservaById($id) {
        try {
            $result = $this->reservasService->anularReserva($id);
            return response('success', 'Reserva anulada correctamente', $result);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
      /**
     * Actualiza los datos básicos de una reserva: nombreAlumno, apellidosAlumno, correo y telefono
     * @param int $id ID de la reserva
     * @return array Respuesta con el estado de la operación
     */
    public function updateReservaById($id) {
        try {
            // Obtener los datos enviados en el cuerpo de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verificar que se recibieron datos
            if (empty($data)) {
                return response('error', 'No se recibieron datos para actualizar', null, 400);
            }
            
            // Verificar que solo se incluyan los campos permitidos
            $camposPermitidos = ['nombreAlumno', 'apellidosAlumno', 'correo', 'telefono'];
            foreach (array_keys($data) as $campo) {
                if (!in_array($campo, $camposPermitidos)) {
                    return response('error', "Campo no permitido: {$campo}. Solo se pueden actualizar: nombreAlumno, apellidosAlumno, correo y telefono", null, 400);
                }
            }
            
            // Llamar al servicio para actualizar los datos
            $reservaActualizada = $this->reservasService->updateReservaById($id, $data);
            
            return response('success', 'Datos de la reserva actualizados correctamente', $reservaActualizada);
            
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
    
    /**
     * Obtiene el justificante de una reserva por su ID
     * @param int $id ID de la reserva
     * @return array Respuesta con el justificante en formato base64
     */
    public function getJustificanteByReservaId($id) {
        try {
            $justificante = $this->reservasService->getJustificanteByReservaId($id);
            
            // El frontend espera solo la cadena base64, sin el formato JSON completo
            return response('success', 'Justificante obtenido correctamente', $justificante['base64']);
            
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 404);
        }
    }
}
?> 