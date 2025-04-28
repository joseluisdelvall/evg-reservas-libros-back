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
    }
    
?>