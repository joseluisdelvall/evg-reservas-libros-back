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
    idRol TINYINT NOT NULL,
    CONSTRAINT usuario_pk PRIMARY KEY (idUsuario),
    CONSTRAINT usuario_email_uq UNIQUE (email),
    CONSTRAINT usuario_rol_fk FOREIGN KEY (idRol) REFERENCES ROL (idRol)
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
