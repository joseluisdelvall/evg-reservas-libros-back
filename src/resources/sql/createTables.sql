
-- Tabla ETAPA
CREATE TABLE IF NOT EXISTS ETAPA (
    idEtapa TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    CONSTRAINT etapa_pk PRIMARY KEY (idEtapa)
);

-- Tabla CURSO
CREATE TABLE IF NOT EXISTS CURSO (
    idCurso TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    idEtapa TINYINT NOT NULL,
    CONSTRAINT curso_pk PRIMARY KEY (idCurso),
    CONSTRAINT curso_etapa_fk FOREIGN KEY (idEtapa) REFERENCES ETAPA (idEtapa)
);

-- Tabla EDITORIAL
CREATE TABLE IF NOT EXISTS EDITORIAL (
    idEditorial TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(100) NULL,
    CONSTRAINT editorial_pk PRIMARY KEY (idEditorial),
    CONSTRAINT editorial_telefono_uq UNIQUE (telefono),
    CONSTRAINT editorial_correo_uq UNIQUE (correo)
);

-- Tabla LIBRO
CREATE TABLE IF NOT EXISTS LIBRO (
    idLibro INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    ISBN CHAR(13) NOT NULL,
    precio DECIMAL(5, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    idEditorial TINYINT NOT NULL,
    CONSTRAINT libro_pk PRIMARY KEY (idLibro),
    CONSTRAINT libro_isbn_uq UNIQUE (ISBN),
    CONSTRAINT libro_precio_chk CHECK (precio > 0),
    CONSTRAINT libro_editorial_fk FOREIGN KEY (idEditorial) REFERENCES EDITORIAL (idEditorial)
);

-- Tabla TM_ESTADO
CREATE TABLE IF NOT EXISTS TM_ESTADO (
    idEstado TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    descripcion VARCHAR(100) NOT NULL,
    CONSTRAINT estado_pk PRIMARY KEY (idEstado)
);

-- Tabla PERIODO_RESERVAS
CREATE TABLE IF NOT EXISTS PERIODO_RESERVAS (
    fechaIni DATE NOT NULL,
    fechaFin DATE NOT NULL
);

-- Tabla RESERVA
CREATE TABLE IF NOT EXISTS RESERVA (
    idReserva INT NOT NULL,
    nombreAlumno VARCHAR(60) NOT NULL,
    apellidosAlumno VARCHAR(120) NOT NULL,
    nombreTutorLegal VARCHAR(60) NULL,
    apellidosTutorLegal VARCHAR(120) NULL,
    correo VARCHAR(100) NOT NULL,
    dni CHAR(9) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    justificante VARCHAR(120) NOT NULL,
    fecha DATE NOT NULL,
    verificado BOOLEAN NOT NULL DEFAULT FALSE,
    totalPagado DECIMAL(5, 2) NOT NULL DEFAULT 0,
    idCurso TINYINT NOT NULL,
    CONSTRAINT reserva_pk PRIMARY KEY (idReserva),
    CONSTRAINT reserva_curso_fk FOREIGN KEY (idCurso) REFERENCES CURSO (idCurso)
);

-- Tabla RESERVA_LIBRO
CREATE TABLE IF NOT EXISTS RESERVA_LIBRO (
    idReserva INT NOT NULL,
    idLibro INT NOT NULL,
    fechaRecogida DATE NULL,
    precioPagado DECIMAL(5, 2) NOT NULL,
    idEstado TINYINT NOT NULL,
    CONSTRAINT reserva_libro_pk PRIMARY KEY (idReserva, idLibro),
    CONSTRAINT reserva_libro_reserva_fk FOREIGN KEY (idReserva) REFERENCES RESERVA (idReserva),
    CONSTRAINT reserva_libro_libro_fk FOREIGN KEY (idLibro) REFERENCES LIBRO (idLibro),
    CONSTRAINT reserva_libro_estado_fk FOREIGN KEY (idEstado) REFERENCES TM_ESTADO (idEstado)
);

-- Tabla PEDIDO
CREATE TABLE IF NOT EXISTS PEDIDO (
    idPedido INT AUTO_INCREMENT NOT NULL,
    fecha DATE NOT NULL,
    idEditorial TINYINT NOT NULL,
    CONSTRAINT pedido_pk PRIMARY KEY (idPedido),
    CONSTRAINT pedido_editorial_fk FOREIGN KEY (idEditorial) REFERENCES EDITORIAL (idEditorial)
);

-- Tabla LIBRO_PEDIDO
CREATE TABLE IF NOT EXISTS LIBRO_PEDIDO (
    idPedido INT NOT NULL,
    idLibro INT NOT NULL,
    unidades TINYINT UNSIGNED NOT NULL,
    CONSTRAINT libro_pedido_pk PRIMARY KEY (idPedido, idLibro),
    CONSTRAINT libro_pedido_pedido_fk FOREIGN KEY (idPedido) REFERENCES PEDIDO (idPedido),
    CONSTRAINT libro_pedido_libro_fk FOREIGN KEY (idLibro) REFERENCES LIBRO (idLibro)
);

-- Insertar estados básicos
INSERT INTO TM_ESTADO (nombre, descripcion) VALUES 
('Sin Verificar', 'La reserva aún no ha sido verificada'),
('Pendiente de pedir', 'El libro necesita ser pedido a la editorial'),
('Pedido', 'El libro ha sido solicitado a la editorial'),
('Recibido', 'El libro está en stock y disponible para recoger'),
('Recogido', 'El libro ha sido entregado al solicitante'),
('Anulado', 'La reserva o pedido ha sido cancelado');