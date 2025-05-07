<?php

    require_once '../src/entity/libro-entity.php';
    require_once '../src/entity/editorial-entity.php';

    class LibrosRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getLibros() {
            // Obtener todos los libros
            $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo 
                    FROM LIBRO AS lb 
                    INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial";
            $resultado = $this->conexion->query($sql);
        
            if (!$resultado) {
                // Log the SQL error for debugging
                error_log("SQL Error: " . $this->conexion->error);
                return [];
            }
        
            if ($resultado->num_rows > 0) {
                $libros = [];
                while ($libro = $resultado->fetch_assoc()) {
                    // Crear la entidad Editorial
                    $editorial = new EditorialEntity(
                        $libro['idEditorial'],
                        $libro['editorialNombre']
                    );
                    // Crear la entidad Libro con la entidad Editorial
                    $libros[] = new LibroEntity(
                        $libro['idLibro'],
                        $libro['libroNombre'], // Usar el alias correcto
                        $libro['ISBN'],
                        $editorial,
                        $libro['precio'],
                        $libro['stock'],
                        $libro['activo']
                    );
                }
                return $libros;
            } else {
                return [];
            }
        }

        /**
         * Agrega un nuevo libro
         * 
         * @param LibroEntity $libro Entidad del libro a agregar
         * @return LibroEntity|null Libro creado o null si no se pudo crear
         */
        public function addLibro($libro) {
            try {
                $sql = "INSERT INTO LIBRO (nombre, ISBN, idEditorial, precio, stock, activo) VALUES (";
                $sql .= "'" . $libro->getNombre() . "', ";
                $sql .= "'" . $libro->getIsbn() . "', ";
                $sql .= "'" . $libro->getEditorial()->getId() . "', ";
                $sql .= "'" . $libro->getPrecio() . "', ";
                $sql .= "'" . $libro->getStock() . "', ";
                $sql .= "'" . $libro->getEstado() . "')";
        
                $resultado = $this->conexion->query($sql);
        
                if (!$resultado) {
                    throw new Exception("Error en la consulta SQL: " . $this->conexion->error);
                }
        
                if ($this->conexion->affected_rows > 0) {
                    $id = $this->conexion->insert_id;

                    // Recuperar el registro completo con la información de la editorial
                    $sql = "SELECT idLibro, lb.nombre AS libroNombre, ISBN, precio, stock, lb.idEditorial, ed.nombre AS editorialNombre, lb.activo 
                            FROM LIBRO AS lb 
                            INNER JOIN EDITORIAL AS ed ON lb.idEditorial = ed.idEditorial 
                            WHERE idLibro = " . $id;
                    $resultado = $this->conexion->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        $libroData = $resultado->fetch_assoc();
                        $editorial = new EditorialEntity(
                            $libroData['idEditorial'],
                            $libroData['editorialNombre']
                        );
                        return new LibroEntity(
                            $libroData['idLibro'],
                            $libroData['libroNombre'],
                            $libroData['ISBN'],
                            $editorial,
                            $libroData['precio'],
                            $libroData['stock'],
                            $libroData['activo']
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