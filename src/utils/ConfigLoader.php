<?php

namespace ProyectoFinal\ReservasLibros\utils;

class ConfigLoader
{
    private static $configCache = [];
    private static $configPath = null;

    /**
     * Establece la ruta base de configuración
     */
    public static function setConfigPath(string $path): void
    {
        self::$configPath = rtrim($path, '/') . '/';
    }

    /**
     * Carga un archivo de configuración
     */
    public static function load(string $configName): array
    {
        // Si ya está cacheado, lo devolvemos
        if (isset(self::$configCache[$configName])) {
            return self::$configCache[$configName];
        }

        // Determinar la ruta del archivo de configuración
        $configFile = self::getConfigFilePath($configName);

        // Verificar que el archivo existe
        if (!file_exists($configFile)) {
            throw new \Exception("Archivo de configuración no encontrado: {$configFile}");
        }

        // Cargar y cachear la configuración
        $config = require $configFile;
        
        if (!is_array($config)) {
            throw new \Exception("El archivo de configuración debe retornar un array: {$configFile}");
        }

        self::$configCache[$configName] = $config;
        
        return $config;
    }

    /**
     * Obtiene un valor específico de la configuración usando notación de punto
     */
    public static function get(string $configName, string $key = null, $default = null)
    {
        $config = self::load($configName);
        
        if ($key === null) {
            return $config;
        }

        return self::getNestedValue($config, $key, $default);
    }

    /**
     * Limpia el cache de configuración
     */
    public static function clearCache(): void
    {
        self::$configCache = [];
    }

    /**
     * Obtiene la ruta completa del archivo de configuración
     */
    private static function getConfigFilePath(string $configName): string
    {
        if (self::$configPath === null) {
            // Ruta por defecto relativa al directorio raíz del proyecto
            $rootPath = dirname(__DIR__, 2);
            self::$configPath = $rootPath . '/config/';
        }

        return self::$configPath . $configName . '.php';
    }

    /**
     * Obtiene valores anidados usando notación de punto (ej: "smtp.host")
     */
    private static function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
} 