<?php

require_once '../src/repository/reservas-repository.php';
require_once '../src/entity/reserva-entity.php';
require_once '../src/dto/reserva-min-dto.php';
require_once '../src/dto/reserva-dto.php';

class ReservasService {
    private $reservasRepository;
    
    public function __construct() {
        $this->reservasRepository = new ReservasRepository();
    }
    
    /**
     * Crea una nueva reserva a partir de los datos del formulario
     * 
     * @param array $formData Datos del formulario
     * @return ReservaDto|null DTO con los datos de la reserva creada
     */
    public function createReserva($formData) {
        try {
            // Validar datos obligatorios
            $this->validateFormData($formData);
            
            // Procesar y guardar el justificante si existe
            $justificantePath = $this->processUploadedFile($formData);
            
            // Crear la entidad de reserva
            $reservaEntity = new ReservaEntity(
                null, // ID (se asignará automáticamente)
                $formData['nombreAlumno'],
                $formData['apellidosAlumno'],
                $formData['nombreTutor'] ?? null,
                $formData['apellidosTutor'] ?? null,
                $formData['correo'],
                $formData['dni'],
                $formData['telefono'],
                $justificantePath,
                date('Y-m-d'), // Fecha actual
                false, // Verificado (por defecto false)
                0, // Total pagado (se calculará en el repositorio)
                $formData['curso'],
                $formData['libro'] // Array de IDs de libros
            );
            
            // Guardar la reserva en la base de datos
            $idReserva = $this->reservasRepository->createReserva($reservaEntity);
            
            if (!$idReserva) {
                throw new Exception("No se pudo crear la reserva");
            }
            
            // Obtener la reserva completa con sus libros
            $reservaCreada = $this->reservasRepository->getReservaByIdCreate($idReserva);
            
            if (!$reservaCreada) {
                throw new Exception("No se pudo recuperar la reserva creada");
            }
            
            // Preparar los datos de los libros en el formato esperado
            $librosFormateados = [];
            foreach ($reservaCreada->getLibros() as $libro) {
                if (is_array($libro)) {
                    $librosFormateados[] = [
                        'id' => $libro['id'],
                        'nombre' => $libro['nombre'],
                        'precio' => $libro['precio'],
                        'estado' => $libro['estado']
                    ];
                }
            }
            
            // Convertir a DTO para la respuesta con el formato esperado
            return new ReservaDto(
                $reservaCreada->getId(),
                $reservaCreada->getNombreAlumno(),
                $reservaCreada->getApellidosAlumno(),
                $reservaCreada->getNombreTutorLegal(),
                $reservaCreada->getApellidosTutorLegal(),
                $reservaCreada->getCorreo(),
                $reservaCreada->getDni(),
                $reservaCreada->getTelefono(),
                $reservaCreada->getJustificante(),
                $reservaCreada->getFecha(),
                $reservaCreada->getVerificado(),
                $reservaCreada->getTotalPagado(),
                $reservaCreada->getIdCurso(),
                $librosFormateados
            );
            
        } catch (Exception $e) {
            error_log("Error en la creación de la reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene todas las reservas
     * 
     * @return array Lista de reservas
     */
    public function getReservas() {
        return $this->reservasRepository->getReservas();
    }
    
    /**
     * Valida los datos del formulario
     * 
     * @param array $formData Datos del formulario
     * @throws Exception Si algún dato obligatorio está ausente
     */
    private function validateFormData($formData) {
        $requiredFields = ['nombreAlumno', 'apellidosAlumno', 'dni', 'correo', 'telefono', 'curso', 'libro'];
        
        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                throw new Exception("Campo obligatorio faltante: $field");
            }
        }
        
        // Validar curso
        if (!is_numeric($formData['curso'])) {
            throw new Exception("El curso debe ser un ID válido");
        }
        
        // Validar libros
        if (!is_array($formData['libro']) || count($formData['libro']) === 0) {
            throw new Exception("Debe seleccionar al menos un libro");
        }
        
        // Validar formato DNI (9 caracteres)
        if (strlen($formData['dni']) !== 9) {
            throw new Exception("El DNI debe tener 9 caracteres");
        }
        
        // Validar formato de correo
        if (!filter_var($formData['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido");
        }
    }
    
    /**
     * Procesa y guarda el archivo de justificante
     * 
     * @param array $formData Datos del formulario con el archivo
     * @return string Ruta donde se guardó el archivo
     * @throws Exception Si hay un error al procesar el archivo
     */
    private function processUploadedFile($formData) {
        // Verificar si existe información de justificante en base64
        if (empty($formData['justificante']) || !isset($formData['justificanteNombre'])) {
            throw new Exception("El justificante de pago es obligatorio");
        }
        
        try {
            // Directorio donde se guardarán los justificantes
            $uploadDir = __DIR__ . '/../../justificantes/';
            
            // Crear el directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generar un nombre único para el archivo
            $fechaHora = date('YmdHis');
            $nombreArchivo = $fechaHora . '_' . basename($formData['justificanteNombre']);
            $rutaCompleta = $uploadDir . $nombreArchivo;
            
            // Decodificar el archivo desde base64
            $base64String = $formData['justificante'];
            // Si contiene la cadena "data:image/png;base64," o similar, eliminar esa parte
            if (preg_match('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+);base64,/', $base64String, $matches)) {
                $base64String = preg_replace('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+);base64,/', '', $base64String);
            }
            
            $decodedFile = base64_decode($base64String);
            
            // Guardar el archivo
            if (file_put_contents($rutaCompleta, $decodedFile) === false) {
                throw new Exception("Error al guardar el archivo");
            }
            
            // Devolver la ruta relativa
            return $nombreArchivo;
            
        } catch (Exception $e) {
            error_log("Error al procesar el archivo: " . $e->getMessage());
            throw new Exception("Error al procesar el justificante: " . $e->getMessage());
        }
    }
    
    /**
     * Entrega los libros de una reserva
     * 
     * @param int $idReserva ID de la reserva
     * @param array $data Datos de los libros a entregar
     * @return bool Resultado de la operación
     * @throws Exception Si hay un error en los datos o en la operación
     */
    public function entregarLibros($idReserva, $data) {
        if (empty($idReserva) || !is_numeric($idReserva)) {
            throw new Exception('ID de reserva inválido.');
        }
        
        if (empty($data['libros']) || !is_array($data['libros'])) {
            throw new Exception('Datos de libros incompletos o inválidos.');
        }
        
        // Validar que todos los IDs de libros sean numéricos
        foreach ($data['libros'] as $idLibro) {
            if (!is_numeric($idLibro)) {
                throw new Exception('ID de libro inválido: ' . $idLibro);
            }
        }
        
        return $this->reservasRepository->entregarLibros($idReserva, $data['libros']);
    }

    /**
     * Obtiene todas las reservas
     * 
     * @return array Respuesta con el estado de la operación
     */
    public function getAllReservas() {
        return $this->reservasRepository->getAllReservas();
    }

    /**
     * Obtiene los libros de una reserva por su ID
     * @param int $idReserva
     * @return array Lista de libros
     */
    public function getLibrosByReservaId($idReserva) {
        return $this->reservasRepository->getLibrosByReservaId($idReserva);
    }

    /**
     * Elimina una reserva por su ID
     * @param int $idReserva
     * @return bool true si se eliminó correctamente
     */
    public function deleteReserva($idReserva) {
        return $this->reservasRepository->deleteReserva($idReserva);
    }

    /**
     * Obtiene una reserva por su ID
     * @param int $idReserva
     * @return array|null Datos de la reserva o null si no existe
     */
    public function getReservaById($idReserva) {
        try {
            $reserva = $this->reservasRepository->getReservaById($idReserva);
            
            if (!$reserva) {
                return null;
            }

            // Devolver en el mismo formato que getAllReservas
            return [
                'id' => $reserva['idReserva'],
                'nombreAlumno' => $reserva['nombreAlumno'],
                'apellidosAlumno' => $reserva['apellidosAlumno'],
                'correo' => $reserva['correo'],
                'telefono' => $reserva['telefono'],
                'fecha' => $reserva['fecha'],
                'verificado' => $reserva['verificado'],
                'totalPagado' => $reserva['totalPagado'],
                'idCurso' => $reserva['idCurso'],
                'nombreCurso' => $reserva['nombreCurso']
            ];
        } catch (Exception $e) {
            error_log("Error al obtener la reserva: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cambia el estado de verificación de una reserva
     * @param int $idReserva
     * @return array Datos de la reserva actualizada
     * @throws Exception si no se encuentra la reserva o hay un error
     */
    public function cambiarEstadoReserva($idReserva) {
        try {
            // Primero verificamos que la reserva exista
            $reserva = $this->getReservaById($idReserva);
            if (!$reserva) {
                throw new Exception("No se encontró la reserva");
            }

            // Llamar al repositorio para actualizar el estado
            $resultado = $this->reservasRepository->updateReservaVerificado($idReserva, !$reserva['verificado']);
            
            if (!$resultado) {
                throw new Exception("No se pudo actualizar el estado de la reserva");
            }

            // Obtener la reserva actualizada y devolverla en el formato correcto
            $reservaActualizada = $this->getReservaById($idReserva);
            if (!$reservaActualizada) {
                throw new Exception("No se pudo obtener la reserva actualizada");
            }

            return $reservaActualizada;
            
        } catch (Exception $e) {
            error_log("Error al cambiar el estado de la reserva: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Anula una reserva y sus libros asociados
     * @param int $idReserva
     * @return array Datos de la reserva actualizada
     * @throws Exception si no se encuentra la reserva o hay un error
     */    public function anularReserva($idReserva) {
        try {
            // Primero verificamos que la reserva exista
            $reserva = $this->getReservaById($idReserva);
            if (!$reserva) {
                throw new Exception("No se encontró la reserva");
            }

            // Llamar al repositorio para anular la reserva
            $resultado = $this->reservasRepository->anularReserva($idReserva);
            
            if (!$resultado) {
                throw new Exception("No se pudo anular la reserva");
            }

            // Obtener la reserva actualizada y devolverla en el formato correcto
            $reservaActualizada = $this->getReservaById($idReserva);
            if (!$reservaActualizada) {
                throw new Exception("No se pudo obtener la reserva actualizada");
            }

            return $reservaActualizada;
            
        } catch (Exception $e) {
            error_log("Error al anular la reserva: " . $e->getMessage());
            throw $e;
        }
    }    /**
     * Actualiza los datos de una reserva (solo nombre, apellidos, correo y teléfono)
     * @param int $idReserva ID de la reserva
     * @param array $datos Datos a actualizar
     * @return array Datos de la reserva actualizada
     * @throws Exception si hay algún error o la reserva no existe
     */
    public function updateReservaById($idReserva, $datos) {
        try {
            // Verificar que la reserva exista
            $reserva = $this->getReservaById($idReserva);
            if (!$reserva) {
                throw new Exception("No se encontró la reserva con ID: " . $idReserva);
            }
            
            // Preparar datos para actualizar
            $datosActualizados = [];
            
            // Solo incluir los campos que se quieren actualizar
            if (isset($datos['nombreAlumno'])) {
                $datosActualizados['nombreAlumno'] = $datos['nombreAlumno'];
            } else {
                $datosActualizados['nombreAlumno'] = $reserva['nombreAlumno'];
            }
            
            if (isset($datos['apellidosAlumno'])) {
                $datosActualizados['apellidosAlumno'] = $datos['apellidosAlumno'];
            } else {
                $datosActualizados['apellidosAlumno'] = $reserva['apellidosAlumno'];
            }
            
            if (isset($datos['correo'])) {
                // Validar formato de correo si se va a actualizar
                if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El formato del correo electrónico no es válido");
                }
                $datosActualizados['correo'] = $datos['correo'];
            } else {
                $datosActualizados['correo'] = $reserva['correo'];
            }
            
            if (isset($datos['telefono'])) {
                $datosActualizados['telefono'] = $datos['telefono'];
            } else {
                $datosActualizados['telefono'] = $reserva['telefono'];
            }
            
            // Llamar al repositorio para actualizar los datos
            $resultado = $this->reservasRepository->updateReservaData($idReserva, $datosActualizados);
            
            if (!$resultado) {
                throw new Exception("No se pudieron actualizar los datos de la reserva");
            }
            
            // Obtener la reserva actualizada y devolverla
            $reservaActualizada = $this->getReservaById($idReserva);
            if (!$reservaActualizada) {
                throw new Exception("No se pudo obtener la reserva actualizada");
            }
            
            return $reservaActualizada;
            
        } catch (Exception $e) {
            error_log("Error al actualizar la reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene el justificante de una reserva por su ID
     * @param int $idReserva ID de la reserva
     * @return array Datos del justificante en formato base64
     * @throws Exception si hay algún error o no se encuentra el justificante
     */
    public function getJustificanteByReservaId($idReserva) {
        try {
            // Verificar que la reserva exista
            $reserva = $this->getReservaById($idReserva);
            if (!$reserva) {
                throw new Exception("No se encontró la reserva con ID: " . $idReserva);
            }
            
            // Obtener el nombre del archivo de justificante
            $nombreJustificante = $this->reservasRepository->getJustificanteByReservaId($idReserva);
            
            if (!$nombreJustificante) {
                throw new Exception("La reserva no tiene un justificante asociado");
            }
            
            // Ruta completa del archivo
            $rutaJustificante = __DIR__ . '/../../justificantes/' . $nombreJustificante;
            
            // Verificar que el archivo existe
            if (!file_exists($rutaJustificante)) {
                throw new Exception("No se encontró el archivo de justificante en el servidor");
            }
            
            // Obtener la extensión del archivo para determinar el tipo MIME
            $extension = pathinfo($nombreJustificante, PATHINFO_EXTENSION);
            $tipoMime = $this->getMimeType($extension);
            
            // Leer el contenido del archivo y codificarlo en base64
            $contenidoArchivo = file_get_contents($rutaJustificante);
            $base64 = base64_encode($contenidoArchivo);
            
            // Devolver la información del justificante
            return [
                'nombre' => $nombreJustificante,
                'tipo' => $tipoMime,
                'base64' => $base64
            ];
            
        } catch (Exception $e) {
            error_log("Error al obtener el justificante: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Determina el tipo MIME basado en la extensión del archivo
     * @param string $extension Extensión del archivo
     * @return string Tipo MIME correspondiente
     */
    private function getMimeType($extension) {
        $extension = strtolower($extension);
        
        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return 'application/octet-stream'; // Tipo genérico si no se reconoce la extensión
        }
    }
}
?>