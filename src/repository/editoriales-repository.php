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

        public function addEditorial($editorial) {
            try {
                $sql = "INSERT INTO EDITORIAL (nombre, telefono, correo, activo) VALUES (";
                $sql .= "'" . $editorial->getNombre() . "', ";
                $sql .= "'" . $editorial->getTelefono() . "', ";
                $sql .= "'" . $editorial->getCorreo() . "', ";
                $sql .= "'" . $editorial->getEstado() . "')";
        
                $resultado = $this->conexion->query($sql);
        
                if (!$resultado) {
                    throw new Exception("Error en la consulta SQL: " . $this->conexion->error);
                }
        
                if ($this->conexion->affected_rows > 0) {
                    $id = $this->conexion->insert_id;

                    // Recuperar el registro completo
                    $sql = "SELECT * FROM EDITORIAL WHERE idEditorial = " . $id;
                    $resultado = $this->conexion->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        $editorialData = $resultado->fetch_assoc();
                        return new EditorialEntity(
                            $editorialData['idEditorial'],
                            $editorialData['nombre'],
                            $editorialData['telefono'],
                            $editorialData['correo'],
                            $editorialData['activo']
                        );
                    } else {
                        throw new Exception("No se pudo recuperar el registro recién insertado.");
                    }
                } else {
                    throw new Exception("No se afectaron filas en la base de datos.");
                }
            } catch (Exception $e) {
                // Registrar el error en el log
                error_log($e->getMessage());
                // Propagar la excepción para que el servicio la maneje
                throw $e;
            }
        }

    }
    
?>