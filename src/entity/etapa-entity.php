<?php
class EtapaEntity {
    private $idEtapa;
    private $nombre;

    public function __construct($idEtapa = null, $nombre = null) {
        $this->idEtapa = (int) $idEtapa;
        $this->nombre = $nombre;
    }

    public function getId() {
        return $this->idEtapa;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($idEtapa) {
        $this->idEtapa = (int) $idEtapa;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function toArray() {
        return [
            'id' => $this->idEtapa,
            'nombre' => $this->nombre
        ];
    }
} 