<?php
    // Set the specific origin for development
    // Limpiar la URL del frontend de caracteres escapados
    $origin = $config['base_url_front'];
    
    // Eliminar barras invertidas escapadas
    $cleanOrigin = str_replace("\/", "/", $origin);
    $cleanOrigin = str_replace('\/', '/', $cleanOrigin);
    $cleanOrigin = str_replace("\\", "", $cleanOrigin);
    $cleanOrigin = rtrim(trim($cleanOrigin), '/');
    
    // Configurar encabezado CORS
    header("Access-Control-Allow-Origin: " . $cleanOrigin);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
    header("Access-Control-Max-Age: 3600");
    header("Content-Type: application/json; charset=UTF-8");

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // Return early for preflight request
        http_response_code(204);
        exit(0);
    }
?> 