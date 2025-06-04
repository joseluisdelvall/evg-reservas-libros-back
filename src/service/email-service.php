<?php

    class EmailService {
        private $endpointUrl = "https://script.google.com/macros/s/AKfycbx55JJkU3oCwRAsM8J9RuTlH_nHX1k9_BBt3ACzN6X2eibMacs46z6D7rTThjVsNzWo/exec"; // Pon aquí la URL del endpoint de Google Apps Script

        public function __construct($endpointUrl = null) {
            if ($endpointUrl) {
                $this->endpointUrl = $endpointUrl;
            }
        }

        /**
         * Envía un correo electrónico utilizando una plantilla HTML a través del endpoint GAS.
         */
        public function sendEmail($emailDestino, $asunto, $plantilla, $datos, $nombreDestino = '') {
            // El parámetro $plantilla queda para compatibilidad, pero el endpoint decide qué plantilla usar (o lo puedes enviar en $datos si lo necesitas)
            return $this->callGASEndpoint([
                "emailDestino" => $emailDestino,
                "asunto" => $asunto,
                "nombreDestino" => $nombreDestino,
                "datos" => $datos
            ]);
        }

        /**
         * Envía un correo personalizado (HTML directo) a través del endpoint GAS.
         * Puedes modificar el endpoint para permitir htmlBody si lo necesitas.
         */
        public function sendCustomEmail($toEmail, $toName, $subject, $htmlBody, $altBody = '') {
            // Si quieres soportar emails 100% custom, deberías adaptar el endpoint para recibir htmlBody y procesarlo.
            // Aquí se usa el campo datos['htmlBody'], y el endpoint debe entenderlo.
            return $this->callGASEndpoint([
                "emailDestino" => $toEmail,
                "asunto" => $subject,
                "nombreDestino" => $toName,
                "datos" => [
                    "htmlBody" => $htmlBody,
                    "altBody" => $altBody
                ]
            ]);
        }

        // El método renderPlantilla y demás ya no son necesarios, toda la lógica la lleva Google Apps Script.

        /**
         * Llama al endpoint de Google Apps Script usando cURL.
         */
        private function callGASEndpoint($payload) {
            $ch = curl_init($this->endpointUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            if ($result === false) {
                throw new Exception('Error conectando con el servicio de email: ' . curl_error($ch));
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = json_decode($result, true);
            if ($statusCode !== 200 || !isset($json['status']) || $json['status'] !== 'ok') {
                throw new Exception('Error enviando correo: ' . ($json['error'] ?? 'Respuesta inesperada del endpoint'));
            }
            return true;
        }
    }
?>
