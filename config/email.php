<?php

/**
 * Configuración para el servicio de correo electrónico
 * 
 * IMPORTANTE: Para usar Gmail, necesitarás:
 * 1. Activar la autenticación de 2 factores en tu cuenta de Gmail
 * 2. Generar una "Contraseña de aplicación" específica
 * 3. Usar esa contraseña de aplicación en lugar de tu contraseña normal
 * 
 * Pasos para generar contraseña de aplicación en Gmail:
 * 1. Ve a tu cuenta de Google -> Seguridad
 * 2. Activa la verificación en 2 pasos si no la tienes
 * 3. En "Contraseñas de aplicaciones", genera una nueva
 * 4. Selecciona "Correo" y "Otro" como dispositivo
 * 5. Usa la contraseña generada de 16 caracteres
 */

return [
    // Configuración del servidor SMTP
    'smtp' => [
        'host' => 'smtp.gmail.com',           // Para Gmail
        'port' => 587,                        // Puerto TLS para Gmail
        'username' => 'albertosanchezdiaz.guadalupe@alumnado.fundacionloyola.net', // Tu email completo (ej: tuusuario@gmail.com)
        'password' => 'wfdy weng psmv vqus',                     // Contraseña de aplicación de Gmail
        'encryption' => 'tls',                // Tipo de encriptación
    ],
    
    // Configuración del remitente
    'from' => [
        'email' => 'albertosanchezdiaz.guadalupe@alumnado.fundacionloyola.net',  // El mismo email que username
        'name' => 'Sistema de Reservas de Libros',
    ],
    
    // Configuraciones adicionales
    'settings' => [
        'charset' => 'UTF-8',
        'timeout' => 30,                      // Timeout en segundos
        'debug' => false,                     // Activar para debug (solo en desarrollo)
    ],
    
    // Plantillas de correo
    'templates' => [
        'reservation_confirmation' => [
            'subject' => 'Confirmación de Reserva de Libro',
        ],
        'return_reminder' => [
            'subject' => 'Recordatorio: Devolución de Libro Próxima',
        ],
        'overdue_notice' => [
            'subject' => 'URGENTE: Libro con Retraso en Devolución',
        ],
    ],
    
    // Configuración para otros proveedores de correo
    'providers' => [
        'outlook' => [
            'host' => 'smtp.live.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'yahoo' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'mailtrap' => [
            'host' => 'sandbox.smtp.mailtrap.io',
            'port' => 2525,
            'encryption' => 'tls',
        ],
    ],
]; 