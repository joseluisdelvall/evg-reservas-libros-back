<?php

    $prod = false;

    if ($prod) {
        
        return [

            /**
             * CONFIG PRODUCTION
             */

            // Cambiar en caso de traslado, con / al final
            'prod' => true,
            'base_name' => '/reservas-libros/:NOMBRECARPETA/',
            'base_url' => 'https://aplicaciones.esvirgua.com/reservas-libros/:NOMBRECARPETA/',
            'base_url_front' => 'https://aplicaciones.esvirgua.com/evg-reservas-libros-front-demo/',
            'google_client_id' => '660176374148-klpm52u3brlqsmpjvqci3ruk5qk1ofnl.apps.googleusercontent.com',
            'id_aplicacion' => '1',
            'dominio_correo' => 'alumnado.fundacionloyola.net;fundacionloyola.es',
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
            'base_url_front' => "http://localhost:4200",
            'google_client_id' => '660176374148-klpm52u3brlqsmpjvqci3ruk5qk1ofnl.apps.googleusercontent.com',
            'id_aplicacion' => '1',
            'dominio_correo' => 'alumnado.fundacionloyola.net',
        ];
    }

?>