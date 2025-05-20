<?php

class LibroCursoEntity {
    private $id;
    private $idLibro;
    private $idCurso;
    private $libroNombre;
    private $cursoNombre;

    public function __construct($id = null, $idLibro = null, $idCurso = null, $libroNombre = null, $cursoNombre = null) {
        $this->id = $id;
        $this->idLibro = $idLibro;
        $this->idCurso = $idCurso;
        $this->libroNombre = $libroNombre;
        $this->cursoNombre = $cursoNombre;
    }

    public function getId() {
        return $this->id;
    }

    public function getIdLibro() {
        return $this->idLibro;
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function getLibroNombre() {
        return $this->libroNombre;
    }

    public function getCursoNombre() {
        return $this->cursoNombre;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'idLibro' => $this->idLibro,
            'idCurso' => $this->idCurso,
            'libroNombre' => $this->libroNombre,
            'cursoNombre' => $this->cursoNombre
        ];
    }
}

?> 