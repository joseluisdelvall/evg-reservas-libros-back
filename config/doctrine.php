<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once __DIR__ . '/../vendor/autoload.php';

// Configuración de la base de datos
$dbParams = [
    'driver'   => 'pdo_mysql', // Cambia esto si tu base de datos es diferente (ej., pdo_pgsql, pdo_sqlite)
    'user'     => 'root',
    'password' => '',
    'dbname'   => 'reservadelibros', // Cambia esto por el nombre de tu base de datos
    'host'     => 'localhost', // O la dirección de tu servidor de base de datos
];

// Configuración para las entidades (dónde Doctrine buscará tus clases)
$config = ORMSetup::createAnnotationMetadataConfiguration(
    [__DIR__ . '/../src/entidades'], // Directorio donde estarán tus clases de modelo (entidades)
    true // Modo desarrollador (pon false cuando tu proyecto esté terminado)
);

// Creación del EntityManager
$entityManager = EntityManager::create($dbParams, $config);

return $entityManager;