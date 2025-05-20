<?php

require_once '../src/service/libros-cursos-service.php';
require_once '../src/dto/libro-curso-dto.php';
require_once '../src/utils/response.php';

class LibrosCursosController {
    
    private $librosCursosService;
    
    public function __construct() {
        $this->librosCursosService = new LibrosCursosService();
    }
    
    /**
     * Obtiene todas las asignaciones de libros a cursos
     * 
     * @return array Respuesta con el estado y los datos de las asignaciones
     */
    public function getLibrosCursos() {
        try {
            $asignaciones = $this->librosCursosService->getLibrosCursos();
            
            if (empty($asignaciones)) {
                return response('warning', 'No hay asignaciones de libros a cursos');
            }
            
            $asignacionesDto = array_map(function($asignacion) {
                return new LibroCursoDto(
                    null, // No hay ID único, usamos null
                    $asignacion->getIdLibro(),
                    $asignacion->getIdCurso(),
                    $asignacion->getLibroNombre(),
                    $asignacion->getCursoNombre()
                );
            }, $asignaciones);
            
            $asignacionesArray = array_map(function($dto) {
                return $dto->toArray();
            }, $asignacionesDto);
            
            return response('success', 'Asignaciones obtenidas correctamente', $asignacionesArray);
            
        } catch (Exception $e) {
            return response('error', $e->getMessage());
        }
    }
    
    /**
     * Obtiene los libros asignados a un curso específico
     * 
     * @param int $idCurso ID del curso
     * @return array Respuesta con el estado y los datos de las asignaciones
     */
    public function getLibrosByCurso($idCurso) {
        try {
            $asignaciones = $this->librosCursosService->getLibrosByCurso($idCurso);
            
            if (empty($asignaciones)) {
                return response('warning', 'No hay libros asignados a este curso');
            }
            
            $asignacionesDto = array_map(function($asignacion) {
                return new LibroCursoDto(
                    null, // No hay ID único, usamos null
                    $asignacion->getIdLibro(),
                    $asignacion->getIdCurso(),
                    $asignacion->getLibroNombre(),
                    $asignacion->getCursoNombre()
                );
            }, $asignaciones);
            
            $asignacionesArray = array_map(function($dto) {
                return $dto->toArray();
            }, $asignacionesDto);
            
            return response('success', 'Libros del curso obtenidos correctamente', $asignacionesArray);
            
        } catch (Exception $e) {
            return response('error', $e->getMessage());
        }
    }
    
    /**
     * Asigna un libro a un curso
     * 
     * @return array Respuesta con el estado y el mensaje de la operación
     */
    public function asignarLibroACurso() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                return response('error', 'Datos de asignación no válidos');
            }
            
            $asignacion = $this->librosCursosService->asignarLibroACurso($data);
            
            $asignacionDto = new LibroCursoDto(
                null, // No hay ID único, usamos null
                $asignacion->getIdLibro(),
                $asignacion->getIdCurso(),
                $asignacion->getLibroNombre(),
                $asignacion->getCursoNombre()
            );
            
            return response('success', 'Libro asignado al curso correctamente', $asignacionDto->toArray());
            
        } catch (Exception $e) {
            return response('error', $e->getMessage());
        }
    }
    
    /**
     * Elimina una asignación de libro a curso
     * 
     * @param array $params Parámetros de la solicitud (opcional)
     * @return array Respuesta con el estado y el mensaje de la operación
     */
    public function eliminarAsignacion($params = null) {
        try {
            // En lugar de recibir un ID, ahora necesitamos recibir los IDs del libro y curso
            // Estos podrían venir como parámetros en la URL o en el cuerpo de la solicitud
            
            $data = [];
            
            // Si recibimos parámetros en la URL
            if ($params && isset($params['idLibro']) && isset($params['idCurso'])) {
                $data['idLibro'] = $params['idLibro'];
                $data['idCurso'] = $params['idCurso'];
            } 
            // Si los parámetros vienen en el cuerpo de la solicitud
            else {
                $requestData = json_decode(file_get_contents('php://input'), true);
                if ($requestData && isset($requestData['idLibro']) && isset($requestData['idCurso'])) {
                    $data = $requestData;
                } else {
                    return response('error', 'Faltan parámetros para eliminar la asignación. Se requieren idLibro e idCurso.');
                }
            }
            
            error_log("Intentando eliminar asignación - idLibro: " . $data['idLibro'] . ", idCurso: " . $data['idCurso']);
            
            $resultado = $this->librosCursosService->eliminarAsignacion($data);
            
            if ($resultado) {
                return response('success', 'Asignación eliminada correctamente');
            } else {
                return response('error', 'No se pudo eliminar la asignación');
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminarAsignacion: " . $e->getMessage());
            return response('error', $e->getMessage());
        }
    }
}

?> 