<?php
    
    require_once '../src/entity/editorial-entity.php';
    class EditorialesRepository {
     
        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        /**
         * Obtiene todas las editoriales
         * 
         * @return array Lista de editoriales
         */
        public function getEditoriales() {
            $sql = "SELECT * FROM EDITORIAL";
            
            $resultado = $this->conexion->query($sql);

            if (!$resultado) {
                error_log("SQL Error: " . $this->conexion->error);
                return [];
            }

            $editoriales = [];
            
            if ($resultado->num_rows > 0) {
                while ($editorial = $resultado->fetch_assoc()) {
                    // Crear la entidad Editorial directamente con todos los campos
                    $editoriales[] = new EditorialEntity(
                        $editorial['idEditorial'],
                        $editorial['nombre'],
                        $editorial['telefono1'],
                        $editorial['telefono2'],
                        $editorial['telefono3'],
                        $editorial['correo1'],
                        $editorial['correo2'],
                        $editorial['correo3'],
                        $editorial['activo']
                    );
                }
            }
    
            return $editoriales;
        }

        /**
         * Convierte un valor vacío a NULL para uso en consultas SQL
         * 
         * @param string $value Valor a procesar
         * @return string Valor SQL seguro (NULL o string con comillas)
         */
        private function emptyToNull($value) {
            if ($value === null || trim($value) === '') {
                return "NULL";
            }
            return "'" . $this->conexion->real_escape_string($value) . "'";
        }

        /**
         * Agrega una nueva editorial
         * 
         * @param EditorialEntity $editorial Datos de la editorial a agregar
         * @return EditorialEntity Datos de la editorial agregada
         * @throws Exception Si hay errores en la operación
         */
        public function addEditorial($editorial) {
            try {
                // Preparar los valores con conversión de vacíos a NULL
                $nombre = "'" . $this->conexion->real_escape_string($editorial->getNombre()) . "'";
                $telefono1 = $this->emptyToNull($editorial->getTelefono1());
                $telefono2 = $this->emptyToNull($editorial->getTelefono2());
                $telefono3 = $this->emptyToNull($editorial->getTelefono3());
                $correo1 = $this->emptyToNull($editorial->getCorreo1());
                $correo2 = $this->emptyToNull($editorial->getCorreo2());
                $correo3 = $this->emptyToNull($editorial->getCorreo3());
                $activo = $editorial->getEstado() ? "1" : "0";

                // Construir la consulta SQL con valores que pueden ser NULL
                $sql = "INSERT INTO EDITORIAL (
                    nombre, 
                    telefono1, 
                    telefono2, 
                    telefono3, 
                    correo1, 
                    correo2, 
                    correo3, 
                    activo
                ) VALUES (
                    $nombre,
                    $telefono1,
                    $telefono2,
                    $telefono3,
                    $correo1,
                    $correo2,
                    $correo3,
                    $activo
                )";
        
                $resultado = $this->conexion->query($sql);
        
                if (!$resultado) {
                    throw new Exception("Error al insertar editorial: " . $this->conexion->error . " SQL: " . $sql);
                }
        
                if ($this->conexion->affected_rows > 0) {
                    $idEditorial = $this->conexion->insert_id;
                    
                    // Recuperar la editorial completa
                    return $this->getEditorialById($idEditorial);
                } else {
                    throw new Exception("No se pudo insertar la editorial.");
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                throw $e;
            }
        }
        
        /**
         * Obtiene una editorial por su ID
         * 
         * @param int $idEditorial ID de la editorial a buscar
         * @return EditorialEntity Datos de la editorial
         * @throws Exception Si no se encuentra la editorial
         */
        public function getEditorialById($idEditorial) {
            $sql = "SELECT * FROM EDITORIAL WHERE idEditorial = $idEditorial";
            $resultado = $this->conexion->query($sql);
            
            if ($resultado && $resultado->num_rows > 0) {
                $editorial = $resultado->fetch_assoc();
                
                return new EditorialEntity(
                    $editorial['idEditorial'],
                    $editorial['nombre'],
                    $editorial['telefono1'],
                    $editorial['telefono2'],
                    $editorial['telefono3'],
                    $editorial['correo1'],
                    $editorial['correo2'],
                    $editorial['correo3'],
                    $editorial['activo']
                );
            } else {
                throw new Exception("Editorial no encontrada con ID: $idEditorial");
            }
        }
    }
    
?>