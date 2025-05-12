<?php

    $prod = false;

    if ($prod) {
        
        return [

            /**
             * CONFIG PRODUCTION
             */

            // Cambiar en caso de traslado, con / al final
            'prod' => true,
            'base_name' => 'evg-reservas-libros-back/',
            'base_url' => 'https://12.2daw.esvirgua.com/evg-reservas-libros-back/',
            'base_url_front' => 'https://12.2daw.esvirgua.com/evg-reservas-libros-front/'
        ];
    } else {
        return [
            /**
             * CONFIG DEVELOPMENT
             */

            // Cambiar en caso de traslado, con / al final
            'prod' => false,
            'base_name' => 'evg-reservas-libros-back/',
            'base_url' => 'http://localhost:8000/',
            'base_url_front' => "http://localhost:4200"
        ];
    }

?>