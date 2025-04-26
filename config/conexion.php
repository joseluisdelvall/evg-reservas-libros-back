<?php
/**
 * Clase de conexión a la base de datos
 * 
 * Esta clase proporciona métodos para conectarse a la base de datos MySQL
 * y ejecutar consultas de forma segura.
 */

require_once __DIR__ . '/configDB.php';

class Conexion {
    private static $instancia = null;
    private $conexion;
    
    /**
     * Constructor privado para implementar patrón Singleton
     */
    private function __construct() {
        try {
            $this->conexion = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            exit;
        }
    }
    
    /**
     * Obtiene una instancia única de la conexión (Singleton)
     * Con esta función se asegura que solo haya una conexión a la base de datos
     * en toda la aplicación.
     * Esta función es estática y se puede llamar sin crear una instancia de la clase.
     * Evita la creación de múltiples conexiones a la base de datos.
     */
    public static function getInstancia() {
        if (self::$instancia == null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia;
    }
    
    /**
     * Obtiene el objeto PDO de la conexión
     */
    public function getConexion() {
        return $this->conexion;
    }
    
    /**
     * Ejecuta una consulta SQL preparada
     * 
     * @param string $sql Consulta SQL
     * @param array $parametros Parámetros para la consulta preparada
     * @return PDOStatement|false
     */
    public function ejecutarConsulta($sql, $parametros = []) {
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }
    
    /**
     * Obtiene un solo registro de una consulta
     * 
     * @param string $sql Consulta SQL
     * @param array $parametros Parámetros para la consulta preparada
     * @return array|false Un único registro o false si no hay resultados
     */
    public function obtenerRegistro($sql, $parametros = []) {
        $stmt = $this->ejecutarConsulta($sql, $parametros);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene múltiples registros de una consulta
     * 
     * @param string $sql Consulta SQL
     * @param array $parametros Parámetros para la consulta preparada
     * @return array Arreglo de registros
     */
    public function obtenerRegistros($sql, $parametros = []) {
        $stmt = $this->ejecutarConsulta($sql, $parametros);
        return $stmt->fetchAll();
    }
}
?>