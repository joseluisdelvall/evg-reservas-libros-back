<?php
class EtapaDto {
    private $id;
    private $nombre;

    public function __construct($id, $nombre) {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre
        ];
    }
} 