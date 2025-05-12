<?php
    
    // Unico archivo publico desde la web
    // Cargar la configuración
    $config = require_once __DIR__ . '/../config/config.php';

    include __DIR__ . '/../config/cors.php';

    //include __DIR__ . '/../config/wanings.php';

    include __DIR__ . '/../src/utils/response.php';
    // Cargar el autoload de composer
    //require_once __DIR__ . '/../vendor/autoload.php';

    // Cargar las rutas de la API
    $routes = require_once __DIR__ . '/../config/routes.php';

    // Obtener la ruta y metodo de la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];

    // Limpiamos la ruta de headers
    $requestUri = explode('?', $path)[0];
    $requestUri = str_replace($config['base_name'], '', $requestUri);

    // Variable para almacenar parámetros de la ruta
    $routeParams = [];
    $matchedRoute = null;

    // Buscar la ruta en las rutas definidas
    foreach ($routes as $routePattern => $handler) {
        list($routeMethod, $routePath) = explode(' ', $routePattern, 2);
        
        // Verificar si el método coincide
        if ($routeMethod !== $method) {
            continue;
        }
        
        // Convertir patrones de ruta como '/api/libros/curso/:id' a expresiones regulares
        $pattern = preg_replace('/:([^\/]+)/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '@^' . $pattern . '$@';
        
        if (preg_match($pattern, $requestUri, $matches)) {
            $matchedRoute = $routePattern;
            
            // Extraer los parámetros de la ruta
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $routeParams[$key] = $value;
                }
            }
            break;
        }
    }

    if (!$matchedRoute) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ruta no encontrada: ' . $method . ' ' . $requestUri
        ]);
        exit;
    }

    $route = $routes[$matchedRoute];
    list($controller, $method) = explode('@', $route);

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

    // Función para convertir el nombre del controlador
    $fileNameController = convertControllerName($controller);
    $filePath = __DIR__ . '/../src/controller/' . $fileNameController . '.php';

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Controlador no encontrado: ' . $fileNameController
        ]);
        exit;
    }

    require_once $filePath;

    $controller = new $controller();
    
    // Si hay parámetros de ruta, pasarlos al método del controlador
    if (!empty($routeParams)) {
        // Pasar los parámetros al método del controlador
        if (isset($routeParams['id'])) {
            $response = $controller->$method($routeParams['id']);
        } else {
            $response = $controller->$method($routeParams);
        }
    } else {
        $response = $controller->$method();
    }

    echo json_encode($response);
    
?>