<?php
    // Definir la ruta del directorio de logs
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/errors.log';

    // Crear el directorio de logs si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }

    // Crear el archivo de log si no existe
    if (!file_exists($logFile)) {
        touch($logFile);
        chmod($logFile, 0666);
    }

    // Configurar el archivo de log
    ini_set('error_log', $logFile);
    
    // Configurar el nivel de reporte de errores
    error_reporting(E_ALL);
    
    // Desactivar la visualización de errores en la salida
    ini_set('display_errors', 0);
    
    // Configurar el manejador de errores personalizado
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $mensaje = date('Y-m-d H:i:s') . " - Error [$errno] $errstr en $errfile:$errline\n";
        error_log($mensaje, 3, __DIR__ . '/../logs/errors.log');
        return true;
    });
?>