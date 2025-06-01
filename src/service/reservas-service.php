<?php

require_once '../src/repository/reservas-repository.php';
require_once '../src/entity/reserva-entity.php';
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
            $reservaCreada = $this->reservasRepository->getReservaById($idReserva);
            
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
                $reservaCreada->getCorreo(),
                $reservaCreada->getFecha(),
                $reservaCreada->getVerificado(),
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
}
?>