<?php

    include '../src/entity/user-entity.php';

    class UserRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getUsuarioByCorreo(string $correo): ?UserEntity {
            $sql = "SELECT * FROM USER WHERE correo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            if($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                return new UserEntity($usuario['id'], $usuario['nombre'], $usuario['correo']);
            } else {
                return null;
            }

        }

    }
?>