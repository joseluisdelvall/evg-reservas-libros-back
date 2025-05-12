CREATE DATABASE IF NOT EXISTS reservalibros 
    DEFAULT CHARACTER SET utf8;

USE reservalibros;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT,
    google_id VARCHAR(255) NULL,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    CONSTRAINT user_ema_uq UNIQUE (email),
    CONSTRAINT user_gog_id_uq UNIQUE (google_id),
    CONSTRAINT user_pk PRIMARY KEY (id)
);

INSERT INTO users (email, nombre) VALUES ('celiamorunoherrojo.guadalupe@alumnado.fundacionloyola.net', 'Celia Moruno Herrojo');