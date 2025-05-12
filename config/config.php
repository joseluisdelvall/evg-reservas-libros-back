<?php

    $prod = true;

    if ($prod) {
        return [

            /**
             * CONFIG PRODUCTION
             */

            // Cambiar en caso de traslado, con / al final
            'base_name' => 'evg-reservas-libros-back/',
            'base_url' => 'https://12.2daw.esvirgua.com/',
            'base_url_front' => 'https://12.2daw.esvirgua.com/'
        ];
    } else {
        return [
            /**
             * CONFIG DEVELOPMENT
             */

            // Cambiar en caso de traslado, con / al final
            'base_name' => 'evg-reservas-libros-back/',
            'base_url' => 'http://localhost:8000/',
            'base_url_front' => 'http://localhost:4200/'
        ];
    }

?>