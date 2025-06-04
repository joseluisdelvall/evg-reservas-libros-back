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
        
        // Cambio de estado a "Anulado" de un libro en X reserva
        'PUT /api/crud/libros/:idLibro/anular/:idReserva' => 'LibrosController@updateEstadoLibroReserva',

        // Editoriales
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
        'PUT /api/crud/reservas/:id/anular' => 'ReservasController@anularReservaById',
        'PUT /api/crud/reservas/:id' => 'ReservasController@updateReservaById',
        'GET /api/reservas/:id/justificante' => 'ReservasController@getJustificanteByReservaId',

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
        'GET /api/pedidos/:id' => 'PedidosController@getPedido',
        'PUT /api/pedidos/unidades-recibidas' => 'PedidosController@updateUnidadesRecibidas',

        // Etapas
        'GET /api/etapas' => 'EtapasController@getEtapas',
        'GET /api/libros/etapa/:id' => 'LibrosController@getLibrosByEtapa',

        // ENTREGAS DE LIBROS
        'GET /api/reservas/entregas' => 'ReservasController@getReservasEntrega', // Recogemos todas las reservas
        'POST /api/reserva/:id/entregar-libros' => 'ReservasController@entregarLibros', // deberemos recoger el idReserva y en el body un array con el id de los libros que queremos entregar
        
        // EMAIL
        'POST /api/email/test' => 'EmailController@sendTestEmail', // Enviar correo de prueba
    ];
    
?>