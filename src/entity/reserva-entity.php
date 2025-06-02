<?php

class ReservaEntity {
    private $id;
    private $nombreAlumno;
    private $apellidosAlumno;
    private $nombreTutorLegal;
    private $apellidosTutorLegal;
    private $correo;
    private $dni;
    private $telefono;
    private $justificante;
    private $fecha;
    private $verificado;
    private $totalPagado;
    private $idCurso;
    private $nombreCurso;
    private $libros; // Array de IDs de libros

    public function __construct(
        $id = null,
        $nombreAlumno = null, 
        $apellidosAlumno = null, 
        $nombreTutorLegal = null,
        $apellidosTutorLegal = null,
        $correo = null,
        $dni = null,
        $telefono = null,
        $justificante = null,
        $fecha = null,
        $verificado = false,
        $totalPagado = 0,
        $idCurso = null,
        $libros = [],
        $nombreCurso = null
    ) {
        $this->id = $id;
        $this->nombreAlumno = $nombreAlumno;
        $this->apellidosAlumno = $apellidosAlumno;
        $this->nombreTutorLegal = $nombreTutorLegal;
        $this->apellidosTutorLegal = $apellidosTutorLegal;
        $this->correo = $correo;
        $this->dni = $dni;
        $this->telefono = $telefono;
        $this->justificante = $justificante;
        $this->fecha = $fecha ?: date('Y-m-d');
        $this->verificado = $verificado;
        $this->totalPagado = $totalPagado;
        $this->idCurso = $idCurso;
        $this->libros = $libros;
        $this->nombreCurso = $nombreCurso;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombreAlumno() {
        return $this->nombreAlumno;
    }

    public function getApellidosAlumno() {
        return $this->apellidosAlumno;
    }

    public function getNombreTutorLegal() {
        return $this->nombreTutorLegal;
    }

    public function getApellidosTutorLegal() {
        return $this->apellidosTutorLegal;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getDni() {
        return $this->dni;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getJustificante() {
        return $this->justificante;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getVerificado() {
        return $this->verificado;
    }

    public function getTotalPagado() {
        return $this->totalPagado;
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function getNombreCurso() {
        return $this->nombreCurso;
    }

    public function getLibros() {
        return $this->libros;
    }
}
?> 