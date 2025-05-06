-- 
-- -- Tabla APLICACION
CREATE TABLE IF NOT EXISTS APLICACION (
    idAplicacion TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    ruta VARCHAR(255) NULL,
    icono VARCHAR(255) NULL,
    CONSTRAINT aplicacion_pk PRIMARY KEY (idAplicacion)
);

--
-- -- Tabla ROL
CREATE TABLE IF NOT EXISTS ROL (
    idRol TINYINT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    idAplicacion TINYINT NOT NULL,
    CONSTRAINT rol_aplicacion_fk FOREIGN KEY (idAplicacion) REFERENCES APLICACION (idAplicacion),
    CONSTRAINT rol_nombre_uq UNIQUE (nombre),
    CONSTRAINT rol_pk PRIMARY KEY (idRol)
);

--
-- Tabla USUARIO
CREATE TABLE IF NOT EXISTS USUARIO (
    idUsuario INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    CONSTRAINT usuario_pk PRIMARY KEY (idUsuario),
    CONSTRAINT usuario_email_uq UNIQUE (email)
);

--
-- Tabla USUARIO_ROL
CREATE TABLE IF NOT EXISTS USUARIO_ROL (
    idUsuario INT NOT NULL,
    idRol TINYINT NOT NULL,
    CONSTRAINT usuario_rol_pk PRIMARY KEY (idUsuario, idRol),
    CONSTRAINT usuario_rol_usuario_fk FOREIGN KEY (idUsuario) REFERENCES USUARIO (idUsuario),
    CONSTRAINT usuario_rol_rol_fk FOREIGN KEY (idRol) REFERENCES ROL (idRol)
);

--
-- Tabla APLICACION
INSERT INTO APLICACION (nombre, descripcion, ruta, icono) VALUES ('Reserva de Libros', 'Aplicación para la reserva de libros', '/reservas', '/iconos/reserva.png');
INSERT INTO APLICACION (nombre, descripcion, ruta, icono) VALUES ('Aula Matinal', 'Aplicación para la gestión del aula matinal', '/aula-matinal', '/iconos/aula-matinal.png');
INSERT INTO APLICACION (nombre, descripcion, ruta, icono) VALUES ('Torneo Olímpico', 'Aplicación para la gestión del torneo olímpico', '/torneo-olimpico', '/iconos/torneo-olimpico.png');

--
-- Tabla ROL
INSERT INTO ROL (nombre, descripcion, idAplicacion) VALUES ('RL', 'Reserva Libros', 1);
INSERT INTO ROL (nombre, descripcion, idAplicacion) VALUES ('AM', 'Aula Matinal', 2);
INSERT INTO ROL (nombre, descripcion, idAplicacion) VALUES ('TO', 'Torneo Olímpico', 3);

-- Tabla USUARIO
INSERT INTO USUARIO (nombre, email) VALUES ('Celia Moruno Herrojo', 'celiamorunoherrojo.guadalupe@alumnado.fundacionloyola.net');
INSERT INTO USUARIO (nombre, email) VALUES ('Alberto Sánchez Díaz', 'albertosanchezdiaz.guadalupe@alumnado.fundacionloyola.net');
INSERT INTO USUARIO (nombre, email) VALUES ('José Luís del Valle del Pino', 'joseluisdelvalledelpino.guadalupe@alumnado.fundacionloyola.net');

-- Tabla USUARIO_ROL
INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (1, 1);
INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (1, 2);

INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (2, 2);
INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (2, 3);

INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (3, 3);
INSERT INTO USUARIO_ROL (idUsuario, idRol) VALUES (3, 1);

