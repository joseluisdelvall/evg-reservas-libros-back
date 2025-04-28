<?php
    
    // Array asociativo de una ruta y su metodo y controlador correspondiente

    return [
        // Periodo de reservas
        'GET /api/periodo-reservas' => 'PeriodoReservasController@getPeriodoReservas',
        'PUT /api/periodo-reservas' => 'PeriodoReservasController@updatePeriodoReservas',
        'GET /api/crud/libros' => 'LibrosController@getLibros',
        
        'GET /api/crud/editoriales' => 'EditorialesController@getEditoriales',
    ];
    
?>