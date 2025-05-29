<?php

class ReservaCursoDto {
    private $id;
    private $nombreAlumno;
    private $apellidosAlumno;
    private $correo;
    private $telefono;
    private $fecha;
    private $verificado;
    private $totalPagado;
    private $curso;

    public function __construct(
        $id = null,
        $nombreAlumno = null, 
        $apellidosAlumno = null,
        $correo = null,
        $telefono = null,
        $fecha = null,
        $verificado = false,
        $totalPagado = 0,
        $curso = null
    ) {
        $this->id = $id;
        $this->nombreAlumno = $nombreAlumno;
        $this->apellidosAlumno = $apellidosAlumno;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->fecha = $fecha;
        $this->verificado = $verificado;
        $this->totalPagado = $totalPagado;
        $this->curso = $curso;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nombreAlumno' => $this->nombreAlumno,
            'apellidosAlumno' => $this->apellidosAlumno,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'fecha' => $this->fecha,
            'verificado' => $this->verificado,
            'totalPagado' => $this->totalPagado,
            'curso' => $this->curso
        ];
    }
}
?> 