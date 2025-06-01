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
        
        // Reservas
        'POST /api/reservas' => 'ReservasController@createReserva',

        // Libros-Cursos (Asignación de libros a cursos)
        'GET /api/crud/libros-cursos' => 'LibrosCursosController@getLibrosCursos',
        'GET /api/libros-cursos/curso/:id' => 'LibrosCursosController@getLibrosByCurso',
        'POST /api/crud/libros-cursos/add' => 'LibrosCursosController@asignarLibroACurso',
        'POST /api/crud/libros-cursos/delete' => 'LibrosCursosController@eliminarAsignacion',

        // PEDIDOS DE LIBROS
        'GET /api/pedidos/editoriales-con-libros-pendientes' => 'EditorialesController@getEditorialesConLibrosPendientes',
        'GET /api/pedidos/editoriales/:id/libros-pendientes' => 'EditorialesController@getLibrosPendientesPorEditorial',
        'POST /api/pedidos/add' => 'PedidosController@addPedido',
        'GET /api/pedidos/editoriales-con-pedidos' => 'PedidosController@getEditorialesConPedidos',
        'GET /api/pedidos/editoriales/:id/pedidos' => 'PedidosController@getPedidosByEditorial',

        // Etapas
        'GET /api/etapas' => 'EtapasController@getEtapas',
        'GET /api/libros/etapa/:id' => 'LibrosController@getLibrosByEtapa',
    ];
    
?>