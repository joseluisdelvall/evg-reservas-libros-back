<?php
    
    require_once '../src/entity/editorial-entity.php';
    class EditorialesRepository {
     
        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getEditoriales() {
            $sql = "SELECT * FROM EDITORIAL";

            $resultado = $this->conexion->query($sql);

            if (!$resultado) {
                // Log the SQL error for debugging
                error_log("SQL Error: " . $this->conexion->error);
                return [];
            }

            if ($resultado->num_rows > 0) {
                $editoriales = [];
                while ($editorial = $resultado->fetch_assoc()) {
                    // Crear la entidad Editorial
                    $editoriales[] = new EditorialEntity(
                        $editorial['idEditorial'],
                        $editorial['nombre'],
                        $editorial['telefono'],
                        $editorial['correo'],
                        $editorial['activo']
                    );
                }
            } else {
                return [];
            }
    
            return $editoriales;
        }

    }
    
?>