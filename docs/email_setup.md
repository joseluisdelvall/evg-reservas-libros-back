# Configuración de Correo Electrónico con PHPMailer

## 📧 Introducción

Este proyecto incluye un sistema completo de correo electrónico usando PHPMailer que permite:
- Enviar correos de confirmación de reservas
- Enviar recordatorios de devolución
- Enviar correos personalizados
- Soporte para archivos adjuntos
- Plantillas HTML y texto plano

## 🚀 Instalación

PHPMailer ya está instalado en el proyecto. Si necesitas reinstalarlo:

```bash
composer require phpmailer/phpmailer
```

## ⚙️ Configuración

### 1. Configurar credenciales de correo

Edita el archivo `config/email.php` y completa los campos:

```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'tu-email@gmail.com',    // ← Cambia esto
    'password' => 'tu-contraseña-app',     // ← Cambia esto
    'encryption' => 'tls',
],

'from' => [
    'email' => 'tu-email@gmail.com',       // ← Cambia esto
    'name' => 'Sistema de Reservas de Libros',
],
```

### 2. Configuración para Gmail

Para usar Gmail necesitas:

1. **Activar la autenticación de 2 factores:**
   - Ve a [myaccount.google.com](https://myaccount.google.com)
   - Seguridad → Verificación en 2 pasos

2. **Generar contraseña de aplicación:**
   - Seguridad → Contraseñas de aplicaciones
   - Selecciona "Correo" y "Otro (nombre personalizado)"
   - Usa la contraseña de 16 caracteres generada

3. **Configurar en email.php:**
   ```php
   'username' => 'tuemail@gmail.com',
   'password' => 'abcd efgh ijkl mnop',  // Contraseña de aplicación
   ```

### 3. Otros proveedores de correo

El archivo de configuración incluye settings para:
- **Outlook/Hotmail:** `smtp.live.com:587`
- **Yahoo:** `smtp.mail.yahoo.com:587`
- **Mailtrap** (testing): `sandbox.smtp.mailtrap.io:2525`

## 📝 Uso Básico

### 1. Crear instancia del servicio

```php
use ProyectoFinal\ReservasLibros\service\EmailServiceFactory;

$emailService = EmailServiceFactory::create();
```

### 2. Enviar correo de confirmación de reserva

```php
$bookDetails = [
    'title' => 'Cien años de soledad',
    'author' => 'Gabriel García Márquez',
    'isbn' => '978-84-376-0494-7',
    'reservation_date' => date('d/m/Y'),
    'pickup_deadline' => date('d/m/Y', strtotime('+7 days'))
];

$success = $emailService->sendReservationConfirmation(
    'usuario@ejemplo.com',
    'Juan Pérez',
    $bookDetails
);
```

### 3. Enviar recordatorio de devolución

```php
$success = $emailService->sendReturnReminder(
    'usuario@ejemplo.com',
    'Juan Pérez',
    $bookDetails,
    '25/12/2024' // Fecha límite de devolución
);
```

### 4. Enviar correo personalizado

```php
$success = $emailService->sendEmail(
    'destinatario@ejemplo.com',
    'Nombre del destinatario',
    'Asunto del correo',
    '<h1>Contenido HTML</h1><p>Este es el cuerpo del correo.</p>',
    'Contenido en texto plano' // Opcional
);
```

### 5. Enviar correo con archivo adjunto

```php
$attachments = [
    [
        'path' => '/ruta/al/archivo.pdf',
        'name' => 'Documento.pdf'
    ]
];

$success = $emailService->sendEmail(
    'destinatario@ejemplo.com',
    'Nombre',
    'Correo con adjunto',
    '<p>Este correo incluye un archivo adjunto.</p>',
    null,
    $attachments
);
```

## 🔧 Integración con controladores

### Ejemplo en un controlador de reservas:

```php
<?php

namespace ProyectoFinal\ReservasLibros\controller;

use ProyectoFinal\ReservasLibros\service\EmailServiceFactory;

class ReservationController
{
    private $emailService;
    
    public function __construct()
    {
        $this->emailService = EmailServiceFactory::create();
    }
    
    public function createReservation($bookId, $userId)
    {
        // Lógica para crear la reserva...
        
        // Obtener datos del usuario y libro
        $user = $this->getUserById($userId);
        $book = $this->getBookById($bookId);
        
        // Preparar detalles para el correo
        $bookDetails = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'reservation_date' => date('d/m/Y'),
            'pickup_deadline' => date('d/m/Y', strtotime('+7 days'))
        ];
        
        // Enviar correo de confirmación
        $emailSent = $this->emailService->sendReservationConfirmation(
            $user->getEmail(),
            $user->getName(),
            $bookDetails
        );
        
        if (!$emailSent) {
            error_log('Error al enviar correo: ' . $this->emailService->getLastError());
        }
        
        return ['success' => true, 'email_sent' => $emailSent];
    }
}
```

## 🧪 Testing

### Usar Mailtrap para pruebas

1. Regístrate en [mailtrap.io](https://mailtrap.io)
2. Obtén las credenciales de tu inbox
3. Crea un servicio para testing:

```php
$emailService = EmailServiceFactory::createForTesting();
```

### Ejemplo de prueba

```php
// Ejecutar ejemplo
php examples/email_example.php
```

## 🐛 Troubleshooting

### Errores comunes

1. **"SMTP connect() failed"**
   - Verifica credenciales
   - Comprueba conexión a internet
   - Revisa firewall/antivirus

2. **"Invalid login"**
   - Usa contraseña de aplicación (Gmail)
   - Verifica usuario y contraseña

3. **"Could not authenticate"**
   - Activa acceso para aplicaciones menos seguras
   - O mejor, usa contraseña de aplicación

### Debug

Activa el modo debug en `config/email.php`:

```php
'settings' => [
    'debug' => true,  // Solo en desarrollo
],
```

## 📊 Logs

Los errores se registran automáticamente usando `error_log()`. Para verlos:

### En XAMPP:
- `C:\xampp\php\logs\php_error_log`

### En sistemas Unix:
- `/var/log/php_error.log`

## 🔒 Seguridad

1. **Nunca** commits credenciales en el repositorio
2. Usa variables de entorno para producción
3. Restringe acceso al archivo `config/email.php`
4. Usa contraseñas de aplicación en lugar de contraseñas principales

## 📚 Recursos adicionales

- [PHPMailer GitHub](https://github.com/PHPMailer/PHPMailer)
- [Configuración Gmail SMTP](https://support.google.com/accounts/answer/185833)
- [Contraseñas de aplicación Google](https://support.google.com/accounts/answer/185833) 