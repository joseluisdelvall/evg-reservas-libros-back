<?php
    
    // Array asociativo de una ruta y su metodo y controlador correspondiente

    return [
        // Periodo de reservas
        'GET /api/periodo-reservas' => 'PeriodoReservasController@getPeriodoReservas',
        'PUT /api/periodo-reservas' => 'PeriodoReservasController@updatePeriodoReservas',

        'GET /api/crud/libros' => 'LibrosController@getLibros',
        'POST /api/crud/libros/add' => 'LibrosController@addLibro',

        // Libros por curso
        'GET /api/libros/curso/:id' => 'LibrosController@getLibrosByCurso',
        
        'GET /api/crud/editoriales' => 'EditorialesController@getEditoriales',
        'POST /api/crud/editoriales/add' => 'EditorialesController@addEditorial',

        // Login
        'POST /api/login' => 'UserController@userLogin',

        // Cursos
        'GET /api/cursos' => 'CursosController@getCursos',
        
        // Reservas
        'POST /api/reservas' => 'ReservasController@createReserva',
    ];
    
?>