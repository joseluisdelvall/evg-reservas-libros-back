<?php

class ReservaDto {
    private $id;
    private $nombreAlumno;
    private $apellidosAlumno;
    private $correo;
    private $fecha;
    private $verificado;
    private $curso;
    private $libros;

    public function __construct(
        $id = null,
        $nombreAlumno = null, 
        $apellidosAlumno = null,
        $correo = null,
        $fecha = null,
        $verificado = false,
        $curso = null,
        $libros = []
    ) {
        $this->id = $id;
        $this->nombreAlumno = $nombreAlumno;
        $this->apellidosAlumno = $apellidosAlumno;
        $this->correo = $correo;
        $this->fecha = $fecha;
        $this->verificado = $verificado;
        $this->curso = $curso;
        $this->libros = $libros;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nombreAlumno' => $this->nombreAlumno,
            'apellidosAlumno' => $this->apellidosAlumno,
            'correo' => $this->correo,
            'fecha' => $this->fecha,
            'verificado' => $this->verificado,
            'curso' => $this->curso,
            'libros' => $this->libros
        ];
    }
}
?> 