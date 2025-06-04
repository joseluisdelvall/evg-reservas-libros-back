<?php

class EmailModel {
    private $emailDestino;
    private $asunto;
    private $plantilla;
    private $datos;
    
    public function __construct($emailDestino = '', $asunto = '', $plantilla = '', $datos = []) {
        $this->emailDestino = $emailDestino;
        $this->asunto = $asunto;
        $this->plantilla = $plantilla;
        $this->datos = $datos;
    }
    
    // Getters
    public function getEmailDestino() {
        return $this->emailDestino;
    }
    
    public function getAsunto() {
        return $this->asunto;
    }
    
    public function getPlantilla() {
        return $this->plantilla;
    }
    
    public function getDatos() {
        return $this->datos;
    }
    
    // Setters
    public function setEmailDestino($emailDestino) {
        $this->emailDestino = $emailDestino;
    }
    
    public function setAsunto($asunto) {
        $this->asunto = $asunto;
    }
    
    public function setPlantilla($plantilla) {
        $this->plantilla = $plantilla;
    }
    
    public function setDatos($datos) {
        $this->datos = $datos;
    }
}

?>
