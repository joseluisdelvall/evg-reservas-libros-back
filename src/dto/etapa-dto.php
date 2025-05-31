<?php
class EtapaDto {
    private $id;
    private $nombre;

    public function __construct($id = null, $nombre = null) {
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