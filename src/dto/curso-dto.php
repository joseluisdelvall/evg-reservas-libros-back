<?php

    class CursoDto {
        private $id;
        private $nombre;
        private $etapa;

        public function __construct($id = null, $nombre = null, $etapa = null) {
            $this->id = $id;
            $this->nombre = $nombre;
            $this->etapa = $etapa;
        }

        public function getId() {
            return $this->id;
        }

        public function getNombre() {
            return $this->nombre;
        }

        public function getEtapa() {
            return $this->etapa;
        }

        public function toArray() {
            return [
                'id' => $this->id,
                'nombre' => $this->nombre,
                'etapa' => $this->etapa
            ];
        }

        public function toJson() {
            return json_encode($this->toArray());
        }
    }

?> 