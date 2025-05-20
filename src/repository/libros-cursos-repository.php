<?php

require_once '../src/entity/libro-curso-entity.php';

class LibrosCursosRepository {

    private $conexion;
    
    public function __construct() {
        require_once '../conexionTmpBD/conexion.php';
    }

    /**
     * Obtiene todas las asignaciones de libros a cursos
     * 
     * @return array Lista de asignaciones
     */
    public function getLibrosCursos() {
        $sql = "SELECT cl.idLibro, cl.idCurso, l.nombre AS libroNombre, c.nombre AS cursoNombre
                FROM CURSO_LIBRO cl
                INNER JOIN LIBRO l ON cl.idLibro = l.idLibro
                INNER JOIN CURSO c ON cl.idCurso = c.idCurso
                WHERE l.activo = 1";
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            error_log("SQL Error: " . $this->conexion->error);
            return [];
        }
        
        $asignaciones = [];
        if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $asignaciones[] = new LibroCursoEntity(
                    null, // No hay ID separado, usamos null
                    $row['idLibro'],
                    $row['idCurso'],
                    $row['libroNombre'],
                    $row['cursoNombre']
                );
            }
        }
        
        return $asignaciones;
    }

    /**
     * Obtiene los libros asignados a un curso específico
     * 
     * @param int $idCurso ID del curso
     * @return array Lista de asignaciones libro-curso
     */
    public function getLibrosByCurso($idCurso) {
        $sql = "SELECT cl.idLibro, cl.idCurso, l.nombre AS libroNombre, c.nombre AS cursoNombre
                FROM CURSO_LIBRO cl
                INNER JOIN LIBRO l ON cl.idLibro = l.idLibro
                INNER JOIN CURSO c ON cl.idCurso = c.idCurso
                WHERE cl.idCurso = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idCurso);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if (!$resultado) {
            error_log("SQL Error: " . $this->conexion->error);
            return [];
        }
        
        $asignaciones = [];
        if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $asignaciones[] = new LibroCursoEntity(
                    null, // No hay ID separado, usamos null
                    $row['idLibro'],
                    $row['idCurso'],
                    $row['libroNombre'],
                    $row['cursoNombre']
                );
            }
        }
        
        return $asignaciones;
    }

    /**
     * Verifica si un libro existe en la base de datos
     * @param int $idLibro ID del libro
     * @return bool True si existe, False en caso contrario
     */
    private function verificarLibroExiste($idLibro) {
        $sql = "SELECT idLibro FROM LIBRO WHERE idLibro = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idLibro);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Verifica si un curso existe en la base de datos
     * @param int $idCurso ID del curso
     * @return bool True si existe, False en caso contrario
     */
    private function verificarCursoExiste($idCurso) {
        $sql = "SELECT idCurso FROM CURSO WHERE idCurso = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idCurso);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Asigna un libro a un curso
     * 
     * @param int $idLibro ID del libro
     * @param int $idCurso ID del curso
     * @return LibroCursoEntity|null La entidad creada o null si hay error
     */
    public function asignarLibroACurso($idLibro, $idCurso) {
        try {
            // Registro de los valores recibidos para diagnóstico
            error_log("Intentando asignar - idLibro: " . $idLibro . " (tipo: " . gettype($idLibro) . "), idCurso: " . $idCurso . " (tipo: " . gettype($idCurso) . ")");
            
            // Convertir a enteros para asegurar el tipo correcto
            $idLibro = (int) $idLibro;
            $idCurso = (int) $idCurso;
            
            // Verificar que el libro existe
            if (!$this->verificarLibroExiste($idLibro)) {
                throw new Exception("El libro con ID " . $idLibro . " no existe");
            }
            
            // Verificar que el curso existe
            if (!$this->verificarCursoExiste($idCurso)) {
                throw new Exception("El curso con ID " . $idCurso . " no existe");
            }
            
            // Verificar si ya existe la asignación
            $sqlCheck = "SELECT * FROM CURSO_LIBRO WHERE idLibro = ? AND idCurso = ?";
            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bind_param("ii", $idLibro, $idCurso);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows > 0) {
                throw new Exception("Esta asignación ya existe");
            }
            
            // Insertar la nueva asignación
            $sqlInsert = "INSERT INTO CURSO_LIBRO (idLibro, idCurso) VALUES (?, ?)";
            $stmt = $this->conexion->prepare($sqlInsert);
            
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
            }
            
            $bindResult = $stmt->bind_param("ii", $idLibro, $idCurso);
            if (!$bindResult) {
                throw new Exception("Error al vincular parámetros: " . $stmt->error);
            }
            
            $executeResult = $stmt->execute();
            if (!$executeResult) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            if ($stmt->affected_rows <= 0) {
                throw new Exception("No se pudo crear la asignación: No se afectaron filas");
            }
            
            // Obtener los datos completos (sin usar un ID auto-incremental)
            $sqlGet = "SELECT cl.idLibro, cl.idCurso, l.nombre AS libroNombre, c.nombre AS cursoNombre
                       FROM CURSO_LIBRO cl
                       INNER JOIN LIBRO l ON cl.idLibro = l.idLibro
                       INNER JOIN CURSO c ON cl.idCurso = c.idCurso
                       WHERE cl.idLibro = ? AND cl.idCurso = ?";
            $stmtGet = $this->conexion->prepare($sqlGet);
            $stmtGet->bind_param("ii", $idLibro, $idCurso);
            $stmtGet->execute();
            $resultGet = $stmtGet->get_result();
            
            if ($resultGet->num_rows > 0) {
                $row = $resultGet->fetch_assoc();
                return new LibroCursoEntity(
                    null, // No hay ID separado, usamos null
                    $row['idLibro'],
                    $row['idCurso'],
                    $row['libroNombre'],
                    $row['cursoNombre']
                );
            } else {
                throw new Exception("No se pudo recuperar la asignación creada");
            }
        } catch (Exception $e) {
            error_log("Error en asignarLibroACurso: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Elimina una asignación de libro a curso
     * 
     * @param int $idLibro ID del libro
     * @param int $idCurso ID del curso
     * @return bool True si se eliminó correctamente, False en caso contrario
     */
    public function eliminarAsignacion($idLibro, $idCurso) {
        try {
            // Convertir a enteros para asegurar el tipo correcto
            $idLibro = (int) $idLibro;
            $idCurso = (int) $idCurso;
            
            $sql = "DELETE FROM CURSO_LIBRO WHERE idLibro = ? AND idCurso = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idLibro, $idCurso);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                return true;
            } else {
                throw new Exception("No se encontró la asignación a eliminar");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}

?> 