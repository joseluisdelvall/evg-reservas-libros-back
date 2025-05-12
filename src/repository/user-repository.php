<?php

    include '../src/entity/user-entity.php';

    class UserRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function isUserRegister(string $email): ?UserEntity {
            $sql = "SELECT * FROM USUARIO WHERE email = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                return new UserEntity($usuario['idUsuario'], $usuario['nombre'], $usuario['email']);
            } else {
                return null;
            }

        }
        
        public function isUserAuthorized(string $email): bool {
            // Valor del ID de la aplicación en la BD. Definido en config/configDB.php
            global $config;
            $idAplicacion = $config['id_aplicacion'];

            $sql = "SELECT true FROM USUARIO usu " .
            "INNER JOIN USUARIO_ROL usu_rol ON usu.idUsuario = usu_rol.idUsuario " .
            "INNER JOIN ROL ON ROL.idRol = usu_rol.idRol " .
            "WHERE usu.email = ? AND ROL.idAplicacion = ?;";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("si", $email, $idAplicacion);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->num_rows > 0;
        }

    }
?>













