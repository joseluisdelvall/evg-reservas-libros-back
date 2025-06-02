<?php

require_once '../src/repository/libros-cursos-repository.php';
require_once '../src/entity/libro-curso-entity.php';

class LibrosCursosService {
    
    private $librosCursosRepository;
    
    public function __construct() {
        $this->librosCursosRepository = new LibrosCursosRepository();
    }
    
    /**
     * Obtiene todas las asignaciones de libros a cursos
     * 
     * @return array Lista de asignaciones
     */
    public function getLibrosCursos() {
        return $this->librosCursosRepository->getLibrosCursos();
    }
    
    /**
     * Obtiene los libros asignados a un curso específico
     * 
     * @param int $idCurso ID del curso
     * @return array Lista de asignaciones libro-curso
     */
    public function getLibrosByCurso($idCurso) {
        if (!is_numeric($idCurso)) {
            throw new Exception("El ID del curso debe ser numérico");
        }
        
        return $this->librosCursosRepository->getLibrosByCurso($idCurso);
    }
    
    /**
     * Asigna un libro a un curso
     * 
     * @param array $data Datos de la asignación (idLibro, idCurso)
     * @return LibroCursoEntity La entidad creada
     * @throws Exception Si hay un error en la asignación
     */
    public function asignarLibroACurso($data) {
        if (!isset($data['idLibro']) || !isset($data['idCurso'])) {
            throw new Exception("Falta ID del libro o ID del curso");
        }
        
        $idLibro = $data['idLibro'];
        $idCurso = $data['idCurso'];
        
        // Validaciones básicas
        if (!is_numeric($idLibro) || !is_numeric($idCurso)) {
            throw new Exception("Los IDs deben ser numéricos");
        }
        
        return $this->librosCursosRepository->asignarLibroACurso($idLibro, $idCurso);
    }
    
    /**
     * Elimina una asignación de libro a curso
     * 
     * @param array $data Datos para identificar la asignación (idLibro, idCurso)
     * @return bool True si se eliminó correctamente, False en caso contrario
     * @throws Exception Si hay un error en la eliminación
     */
    public function eliminarAsignacion($data) {
        if (!isset($data['idLibro']) || !isset($data['idCurso'])) {
            throw new Exception("Falta ID del libro o ID del curso para eliminar la asignación");
        }
        
        $idLibro = $data['idLibro'];
        $idCurso = $data['idCurso'];
        
        if (!is_numeric($idLibro) || !is_numeric($idCurso)) {
            throw new Exception("Los IDs deben ser numéricos");
        }
        
        return $this->librosCursosRepository->eliminarAsignacion($idLibro, $idCurso);
    }
}

?> 