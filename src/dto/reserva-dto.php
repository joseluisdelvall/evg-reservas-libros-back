<?php

class ReservaDto {
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
    private $curso;
    private $libros;

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
        $curso = null,
        $libros = []
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
        $this->fecha = $fecha;
        $this->verificado = $verificado;
        $this->totalPagado = $totalPagado;
        $this->curso = $curso;
        $this->libros = $libros;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nombreAlumno' => $this->nombreAlumno,
            'apellidosAlumno' => $this->apellidosAlumno,
            'nombreTutorLegal' => $this->nombreTutorLegal,
            'apellidosTutorLegal' => $this->apellidosTutorLegal,
            'correo' => $this->correo,
            'dni' => $this->dni,
            'telefono' => $this->telefono,
            'justificante' => $this->justificante,
            'fecha' => $this->fecha,
            'verificado' => $this->verificado,
            'totalPagado' => $this->totalPagado,
            'curso' => $this->curso,
            'libros' => $this->libros
        ];
    }

    // Getters
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

    public function getCurso() {
        return $this->curso;
    }

    public function getLibros() {
        return $this->libros;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombreAlumno($nombreAlumno) {
        $this->nombreAlumno = $nombreAlumno;
    }

    public function setApellidosAlumno($apellidosAlumno) {
        $this->apellidosAlumno = $apellidosAlumno;
    }

    public function setNombreTutorLegal($nombreTutorLegal) {
        $this->nombreTutorLegal = $nombreTutorLegal;
    }

    public function setApellidosTutorLegal($apellidosTutorLegal) {
        $this->apellidosTutorLegal = $apellidosTutorLegal;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setDni($dni) {
        $this->dni = $dni;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setJustificante($justificante) {
        $this->justificante = $justificante;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setVerificado($verificado) {
        $this->verificado = $verificado;
    }

    public function setTotalPagado($totalPagado) {
        $this->totalPagado = $totalPagado;
    }

    public function setCurso($curso) {
        $this->curso = $curso;
    }

    public function setLibros($libros) {
        $this->libros = $libros;
    }
}
?>