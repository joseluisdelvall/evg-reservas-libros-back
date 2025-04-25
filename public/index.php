<?php
    
    // Unico archivo publico desde la web

    // Cargar el autoload de composer
    //require_once __DIR__ . '/../vendor/autoload.php';

    // Cargar la configuración
    $config = require_once __DIR__ . '/../config/config.php';

    // Cargar las rutas de la API
    $routes = require_once __DIR__ . '/../config/routes.php';

    // Obtener la ruta y metodo de la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];

    // Limpiamos la ruta de headers
    $requestUri = explode('?', $path)[0];
    $requestUri = str_replace($config['base_name'], '', $requestUri);

    $routeKey = $method . ' ' . $requestUri;

    $route = $routes[$routeKey];

    if(isset($route)) {
        list($controller, $method) = explode('@', $route);
    }

    // Función para convertir el nombre del controlador
    $fileNameController = convertControllerName($controller);

    require_once __DIR__ . '/../src/controller/' . $fileNameController . '.php';

    echo $fileNameController;

    function convertControllerName($controllerName) {
        
        // Convertir mayúsculas a minúsculas con guión
        $result = '';
        for ($i = 0; $i < strlen($controllerName); $i++) {
            $char = $controllerName[$i];
            if (ctype_upper($char) && $i > 0) {
                $result .= '-' . strtolower($char);
            } else {
                $result .= strtolower($char);
            }
        }
        return $result;
    }

?>