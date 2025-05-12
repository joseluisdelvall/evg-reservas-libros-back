<?php
    include '../config/configdb.php';
    //Conecta con la base de datos del servidor($conexión)
    $this->conexion = new mysqli(SERVIDOR, USUARIO, PASSWORD, BBDD); //Conecta con la base de datos
    $this->conexion->set_charset("utf8"); //Usa juego caracteres UTF8
    //Desactivar errores
    $this->controlador = new mysqli_driver();
    $this->controlador->report_mode = MYSQLI_REPORT_OFF;
?>