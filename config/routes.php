<?php
    
    // Array asociativo de una ruta y su metodo y controlador correspondiente

    return [
        // Periodo de reservas
        'GET /api/periodo-reservas' => 'PeriodoReservasController@getPeriodoReservas',
        'PUT /api/periodo-reservas' => 'PeriodoReservasController@updatePeriodoReservas',

        'GET /api/crud/libros' => 'LibrosController@getLibros',
        'GET /api/crud/libros/:id' => 'LibrosController@getLibro',
        'POST /api/crud/libros/add' => 'LibrosController@addLibro',
        'PUT /api/crud/libros/:id' => 'LibrosController@updateLibro',
        'PUT /api/crud/libros/:id/estado' => 'LibrosController@cambiarEstadoLibro',
        
        'GET /api/crud/editoriales' => 'EditorialesController@getEditoriales',
        'GET /api/crud/editoriales/:id' => 'EditorialesController@getEditorial',
        'POST /api/crud/editoriales/add' => 'EditorialesController@addEditorial',
        'PUT /api/crud/editoriales/:id' => 'EditorialesController@updateEditorial',
        'PUT /api/crud/editoriales/:id/estado' => 'EditorialesController@cambiarEstadoEditorial',


        // Login
        'POST /api/login' => 'UserController@userLogin',
    ];
    
?>