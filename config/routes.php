<?php
    
    // Array asociativo de una ruta y su metodo y controlador correspondiente

    return [
        // Periodo de reservas
        'GET /api/periodo-reservas' => 'PeriodoReservasController@getPeriodoReservas',
        'PUT /api/periodo-reservas' => 'PeriodoReservasController@updatePeriodoReservas',
        'GET /api/crud/libros' => 'LibrosController@getLibros',
        'POST /api/crud/libros/add' => 'LibrosController@addLibro',
        
        'GET /api/crud/editoriales' => 'EditorialesController@getEditoriales',
        'GET /api/crud/editoriales/{id}' => 'EditorialesController@getEditorial',
        'POST /api/crud/editoriales/add' => 'EditorialesController@addEditorial',
    ];
    
?>