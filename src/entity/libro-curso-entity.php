<?php

class LibroCursoEntity {
    private $idLibro;
    private $idCurso;
    private $libroNombre;
    private $cursoNombre;

    public function __construct($idLibro = null, $idCurso = null, $libroNombre = null, $cursoNombre = null) {
        $this->idLibro = $idLibro;
        $this->idCurso = $idCurso;
        $this->libroNombre = $libroNombre;
        $this->cursoNombre = $cursoNombre;
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
            'idLibro' => $this->idLibro,
            'idCurso' => $this->idCurso,
            'libroNombre' => $this->libroNombre,
            'cursoNombre' => $this->cursoNombre
        ];
    }
}

?> 