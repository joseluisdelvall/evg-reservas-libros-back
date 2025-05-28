<?php

    include '../src/entity/periodo-reservas-entity.php';

    class PeriodoReservasRepository {

        private $conexion;
        private $controlador;
        
        public function __construct() {
            require_once '../conexionTmpBD/conexion.php';
        }

        public function getPeriodoActual() {
            // Solo tiene 1 registro que lo podemos obtener o modificar
            $sql = "SELECT * FROM PERIODO_RESERVAS";
            $resultado = $this->conexion->query($sql);

            if($resultado->num_rows > 0) {
                $periodo = $resultado->fetch_assoc();
                return new PeriodoReservasEntity($periodo['fechaIni'], $periodo['fechaFin']);
            } else {
                return null;
            }
        }

        /**
         * Actualiza el período de reservas
         * 
         * @param string $fechaInicio Fecha de inicio del nuevo período
         * @param string $fechaFin Fecha de fin del nuevo período
         * @return PeriodoReservasEntity Nuevo período de reservas
         */
        public function updatePeriodoReservas($fechaInicio, $fechaFin) {
            try {
                // Comenzar transacción
                $this->conexion->begin_transaction();

                // Primero eliminamos el período actual si existe
                $sqlDelete = "DELETE FROM PERIODO_RESERVAS";
                $stmtDelete = $this->conexion->prepare($sqlDelete);
                $stmtDelete->execute();

                // Insertamos el nuevo período
                $sqlInsert = "INSERT INTO PERIODO_RESERVAS (fechaIni, fechaFin) VALUES (?, ?)";
                $stmtInsert = $this->conexion->prepare($sqlInsert);
                $stmtInsert->bind_param("ss", $fechaInicio, $fechaFin);
                $stmtInsert->execute();

                if ($stmtInsert->affected_rows <= 0) {
                    throw new Exception("No se pudo actualizar el período de reservas");
                }

                // Confirmar la transacción
                $this->conexion->commit();

                // Devolver el nuevo período
                return new PeriodoReservasEntity($fechaInicio, $fechaFin);

            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $this->conexion->rollback();
                throw new Exception("Error al actualizar el período de reservas: " . $e->getMessage());
            }
        }
    }
?>