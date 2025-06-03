<?php

namespace ProyectoFinal\ReservasLibros\service;

use ProyectoFinal\ReservasLibros\utils\ConfigLoader;

class EmailServiceFactory
{
    /**
     * Crea una instancia de EmailService con la configuración por defecto
     */
    public static function create(): EmailService
    {
        $config = ConfigLoader::load('email');
        
        return new EmailService(
            $config['smtp']['host'],
            $config['smtp']['port'],
            $config['smtp']['username'],
            $config['smtp']['password'],
            $config['from']['email'],
            $config['from']['name']
        );
    }

    /**
     * Crea una instancia de EmailService con un proveedor específico
     */
    public static function createWithProvider(string $provider): EmailService
    {
        $config = ConfigLoader::load('email');
        
        if (!isset($config['providers'][$provider])) {
            throw new \InvalidArgumentException("Proveedor de correo no configurado: {$provider}");
        }
        
        $providerConfig = $config['providers'][$provider];
        
        return new EmailService(
            $providerConfig['host'],
            $providerConfig['port'],
            $config['smtp']['username'], // Mantiene las credenciales del usuario
            $config['smtp']['password'],
            $config['from']['email'],
            $config['from']['name']
        );
    }

    /**
     * Crea una instancia de EmailService para testing (usando Mailtrap)
     */
    public static function createForTesting(): EmailService
    {
        return self::createWithProvider('mailtrap');
    }
} 