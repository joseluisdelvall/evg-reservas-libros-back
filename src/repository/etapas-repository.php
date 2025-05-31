<?php
require_once '../src/entity/etapa-entity.php';

class EtapasRepository {
    private $conexion;

    public function __construct() {
        require_once '../conexionTmpBD/conexion.php';
    }

    /**
     * Obtiene todas las etapas
     * @return array Lista de etapas
     */
    public function getEtapas() {
        $sql = "SELECT * FROM ETAPA";
        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            error_log("SQL Error: " . $this->conexion->error);
            return [];
        }

        $etapas = [];
        if ($resultado->num_rows > 0) {
            while ($etapa = $resultado->fetch_assoc()) {
                $etapas[] = new EtapaEntity(
                    $etapa['idEtapa'],
                    $etapa['nombre']
                );
            }
        }
        return $etapas;
    }
}