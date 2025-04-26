<?php
/**
 * Configuración simplificada de Doctrine para evitar problemas de compatibilidad
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar constantes de configuración de la base de datos
require_once __DIR__ . '/configDB.php';

// Configuración de la base de datos
$dbParams = [
    'driver'   => 'pdo_mysql',
    'user'     => DB_USER,
    'password' => DB_PASS,
    'dbname'   => DB_NAME,
    'host'     => DB_HOST,
    'port'     => DB_PORT,
    'charset'  => DB_CHARSET,
];

// Configuración de Doctrine usando el enfoque más básico y compatible
$config = new \Doctrine\ORM\Configuration();

// Configurar el driver de metadatos para anotaciones
$driverImpl = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
    new \Doctrine\Common\Annotations\AnnotationReader(),
    [__DIR__ . '/../src/entities']
);
$config->setMetadataDriverImpl($driverImpl);

// Configuración de caché simple
$cache = new \Doctrine\Common\Cache\ArrayCache();
$config->setMetadataCache($cache);
$config->setQueryCache($cache);

// Configuración de proxies
$config->setProxyDir(__DIR__ . '/../var/cache/proxies');
$config->setProxyNamespace('DoctrineProxies');
$config->setAutoGenerateProxyClasses(true);

// Crear directorio de proxies si no existe
$proxyDir = __DIR__ . '/../var/cache/proxies';
if (!is_dir($proxyDir)) {
    mkdir($proxyDir, 0777, true);
}

// Crear el EntityManager
try {
    $entityManager = \Doctrine\ORM\EntityManager::create($dbParams, $config);
    return $entityManager;
} catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
    exit();
}