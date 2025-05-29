<?php
    
    // Array asociativo de una ruta y su metodo y controlador correspondiente

    return [
        // Periodo de reservas
        'GET /api/periodo-reservas' => 'PeriodoReservasController@getPeriodoReservas',
        'PUT /api/periodo-reservas' => 'PeriodoReservasController@updatePeriodoReservas',

        'GET /api/crud/libros' => 'LibrosController@getLibros',
        'GET /api/crud/libros/:id' => 'LibrosController@getLibro',
        'POST /api/crud/libros/add' => 'LibrosController@addLibro',

        // Libros por curso
        'GET /api/libros/curso/:id' => 'LibrosController@getLibrosByCurso',
        
        'PUT /api/crud/libros/:id' => 'LibrosController@updateLibro',
        'PUT /api/crud/libros/:id/estado' => 'LibrosController@cambiarEstadoLibro',
        
        'GET /api/crud/editoriales' => 'EditorialesController@getEditoriales',
        'GET /api/crud/editoriales/:id' => 'EditorialesController@getEditorial',
        'POST /api/crud/editoriales/add' => 'EditorialesController@addEditorial',
        'PUT /api/crud/editoriales/:id' => 'EditorialesController@updateEditorial',
        'PUT /api/crud/editoriales/:id/estado' => 'EditorialesController@cambiarEstadoEditorial',


        // Login
        'POST /api/login' => 'UserController@userLogin',

        // Cursos
        'GET /api/cursos' => 'CursosController@getCursos',
        'GET /api/cursos/:id' => 'CursosController@getCursoById',
        
        // Reservas
        'POST /api/reservas' => 'ReservasController@createReserva',
        'GET /api/crud/reservas' => 'ReservasController@getReservas',
        'GET /api/crud/reservas/:id' => 'ReservasController@getReservaById',
        'GET /api/crud/reservas/:id/libros' => 'ReservasController@getLibrosByReservaId',
        'DELETE /api/crud/reservas/:id' => 'ReservasController@deleteReserva',
        'PUT /api/crud/reservas/:id/estado' => 'ReservasController@cambiarEstadoReserva',
    ];
    
?>