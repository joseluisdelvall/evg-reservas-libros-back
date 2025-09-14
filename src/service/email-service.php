<?php

    class EmailService {
        // private $endpointUrl = "https://script.google.com/macros/s/AKfycbx6sFapTH0N2KbDX0gfCIjPN2aGdyGEj_lss9h4TFhc4ht_5QjWA5Bl0drSS12INrce0A/exec"; // ENDPOINT proy******os@gmail.com
        private $endpointUrl = "https://script.google.com/macros/s/AKfycbzkXVczz9CbcLtRzTfzBr00Gdtzj5lLroKnGz13gtbshPNuEO0el2vl-KQUGIWslKBJ/exec"; // ENDPOINT cuenta Aplicaciones Guadalupe
        public function __construct($endpointUrl = null) {
            if ($endpointUrl) {
                $this->endpointUrl = $endpointUrl;
            }
        }

        /**
         * Envía un correo electrónico utilizando una plantilla HTML a través del endpoint GAS.
         */
        public function sendEmail($emailDestino, $asunto, $plantilla, $datos, $nombreDestino = '') {
            return $this->callGASEndpoint([
                "emailDestino" => $emailDestino,
                "asunto" => $asunto,
                "nombreDestino" => $nombreDestino,
                "plantilla" => $plantilla,   // <-- AÑADIDO AQUÍ
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
