<?php

class LibroCursoDto {
    private $idLibro;
    private $idCurso;
    private $libroNombre;
    private $cursoNombre;

    public function __construct($idLibro, $idCurso, $libroNombre, $cursoNombre) {
        $this->idLibro = $idLibro;
        $this->idCurso = $idCurso;
        $this->libroNombre = $libroNombre;
        $this->cursoNombre = $cursoNombre;
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