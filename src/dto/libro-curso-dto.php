<?php

class LibroCursoDto {
    private $id;
    private $idLibro;
    private $idCurso;
    private $libroNombre;
    private $cursoNombre;

    public function __construct($id, $idLibro, $idCurso, $libroNombre, $cursoNombre) {
        $this->id = $id;
        $this->idLibro = $idLibro;
        $this->idCurso = $idCurso;
        $this->libroNombre = $libroNombre;
        $this->cursoNombre = $cursoNombre;
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