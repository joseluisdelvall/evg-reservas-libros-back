<?php
    /**
     * Genera una respuesta estandarizada para las API
     * 
     * @param string|int $status Código o estado de la respuesta
     * @param string $message Mensaje descriptivo de la respuesta
     * @param mixed $data Datos adicionales de la respuesta (opcional)
     * @return array Respuesta formateada
     */
    function response($status, $message, $data = null) {
        if (is_array($data)) {
            return [
                'status' => $status,
                'message' => $message,
                'data' => $data
            ];
        }
        
        // Si $data es un objeto, intentar convertirlo a array
        if (is_object($data) && method_exists($data, 'toArray')) {
            $dataArray = $data->toArray();
        } else {
            $dataArray = $data;
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'data' => $dataArray
        ];
    }
?>