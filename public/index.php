<?php
    
    // Unico archivo publico desde la web

    // Cargar el autoload de composer
    //require_once __DIR__ . '/../vendor/autoload.php';

    // Cargar las rutas de la API
    $routes = require_once __DIR__ . '/../config/routes.php';

    // Obtener la ruta y metodo de la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];

    // Limpiamos la ruta de headers
    $requestUri = explode('?', $path)[0];

    echo $requestUri;
    

?>