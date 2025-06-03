<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProyectoFinal\ReservasLibros\service\EmailServiceFactory;

// Ejemplo de uso del servicio de correo

try {
    // 1. CONFIGURAR LAS CREDENCIALES DE CORREO
    // Antes de ejecutar este ejemplo, debes configurar tus credenciales en config/email.php
    
    // 2. Crear instancia del servicio de correo
    $emailService = EmailServiceFactory::create();
    
    // 3. Ejemplo: Enviar correo de confirmación de reserva
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
    
    if ($success) {
        echo "✅ Correo de confirmación enviado exitosamente\n";
    } else {
        echo "❌ Error al enviar el correo: " . $emailService->getLastError() . "\n";
    }
    
    // 4. Ejemplo: Enviar recordatorio de devolución
    $success = $emailService->sendReturnReminder(
        'usuario@ejemplo.com',
        'Juan Pérez',
        $bookDetails,
        date('d/m/Y', strtotime('+2 days'))
    );
    
    if ($success) {
        echo "✅ Recordatorio de devolución enviado exitosamente\n";
    } else {
        echo "❌ Error al enviar el recordatorio: " . $emailService->getLastError() . "\n";
    }
    
    // 5. Ejemplo: Enviar correo personalizado
    $htmlContent = "
    <h2>Correo personalizado</h2>
    <p>Este es un ejemplo de correo personalizado.</p>
    <p>Puedes incluir cualquier contenido HTML aquí.</p>
    ";
    
    $textContent = "
    Correo personalizado
    
    Este es un ejemplo de correo personalizado.
    Esta es la versión en texto plano.
    ";
    
    $success = $emailService->sendEmail(
        'usuario@ejemplo.com',
        'Juan Pérez',
        'Asunto del correo personalizado',
        $htmlContent,
        $textContent
    );
    
    if ($success) {
        echo "✅ Correo personalizado enviado exitosamente\n";
    } else {
        echo "❌ Error al enviar el correo personalizado: " . $emailService->getLastError() . "\n";
    }
    
    // 6. Ejemplo: Enviar correo con archivo adjunto
    $attachments = [
        [
            'path' => __DIR__ . '/sample_attachment.pdf',
            'name' => 'Documento.pdf'
        ]
    ];
    
    /*
    $success = $emailService->sendEmail(
        'usuario@ejemplo.com',
        'Juan Pérez',
        'Correo con archivo adjunto',
        '<p>Este correo incluye un archivo adjunto.</p>',
        'Este correo incluye un archivo adjunto.',
        $attachments
    );
    
    if ($success) {
        echo "✅ Correo con adjunto enviado exitosamente\n";
    } else {
        echo "❌ Error al enviar el correo con adjunto: " . $emailService->getLastError() . "\n";
    }
    */
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "1. Configura tus credenciales de correo en config/email.php\n";
    echo "2. Si usas Gmail, asegúrate de usar una contraseña de aplicación\n";
    echo "3. Verifica que tu servidor tenga acceso a internet\n";
    echo "4. Revisa que no haya firewall bloqueando el puerto SMTP\n";
}

echo "\n📧 Ejemplo de uso del servicio de correo completado.\n"; 