<?php
    
    class EditorialesRepository {
     
        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getEditoriales() {
            $query = "SELECT * FROM EDITORIAL";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            
            $editoriales = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $editorial = new EditorialEntity($row['idEditorial'], $row['nombre']);
                array_push($editoriales, $editorial->toArray());
            }
            
            return $editoriales;
        }

    }
    
?>