<?php
    
    require_once '../src/entity/curso-entity.php';
    class CursosRepository {
    
        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getCursos() {
            $sql = "SELECT * FROM CURSO";
            
            $resultado = $this->conexion->query($sql);
            
            if (!$resultado) {
                return [];
            }

            $cursos = [];
            if ($resultado->num_rows > 0) {
                while ($row = $resultado->fetch_assoc()) {
                    $cursos[] = new CursoEntity(
                        $row['idCurso'],
                        $row['nombre'],
                        $row['idEtapa']
                    );
                }
            } else {
                return [];
            }
    
            return $cursos;
        }

    }
    
?>