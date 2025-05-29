<?php

class LibroPendienteDto {
    private $idLibro;
    private $nombre;
    private $isbn;
    private $precio;
    private $unidadesPendientes;

    public function __construct($idLibro, $nombre, $isbn, $precio, $unidadesPendientes) {
        $this->idLibro = $idLibro;
        $this->nombre = $nombre;
        $this->isbn = $isbn;
        $this->precio = $precio;
        $this->unidadesPendientes = $unidadesPendientes;
    }

    public function getIdLibro() {
        return $this->idLibro;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getIsbn() {
        return $this->isbn;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getUnidadesPendientes() {
        return $this->unidadesPendientes;
    }

    public function toArray() {
        return [
            'idLibro' => $this->getIdLibro(),
            'nombre' => $this->getNombre(),
            'isbn' => $this->getIsbn(),
            'precio' => $this->getPrecio(),
            'unidadesPendientes' => $this->getUnidadesPendientes(),
        ];
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}

?> 