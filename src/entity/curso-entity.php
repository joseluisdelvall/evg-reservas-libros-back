<?php

    class CursoEntity {
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
                'idCurso' => $this->getId(),
                'nombre' => $this->getNombre(),
                'etapa' => $this->getEtapa()
            ];
        }
    }

?> 