<?php
    function response($status, $message, $data = null) {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data->toArray() ?? null
            // 'data' => $data ? $data->toArray() : null
        ];
    }
?>