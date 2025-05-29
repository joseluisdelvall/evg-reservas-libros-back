<?php
class PedidoEntity {
    private $idPedido;
    private $fecha;
    private $idEditorial;
    private $libros; // array de ['idLibro' => int, 'cantidad' => int]

    public function __construct($idPedido = null, $fecha = null, $idEditorial = null, $libros = []) {
        $this->idPedido = $idPedido;
        $this->fecha = $fecha;
        $this->idEditorial = $idEditorial;
        $this->libros = $libros;
    }

    public function getIdPedido() {
        return $this->idPedido;
    }
    public function getFecha() {
        return $this->fecha;
    }
    public function getIdEditorial() {
        return $this->idEditorial;
    }
    public function getLibros() {
        return $this->libros;
    }
    public function toArray() {
        return [
            'idPedido' => $this->idPedido,
            'fecha' => $this->fecha,
            'idEditorial' => $this->idEditorial,
            'libros' => $this->libros
        ];
    }
} 