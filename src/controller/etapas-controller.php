<?php

require_once '../src/service/etapas-service.php';
require_once '../src/dto/etapa-dto.php';
require_once '../src/utils/response.php';

class EtapasController {
    private $etapasService;

    public function __construct() {
        $this->etapasService = new EtapasService();
    }

    /**
     * Obtiene todas las etapas
     * 
     * @return array Respuesta con el estado y los datos de las etapas
     */
    public function getEtapas() {
        try {
            $etapas = $this->etapasService->getEtapas();

            if (empty($etapas)) {
                return response('error', 'No se han encontrado etapas', null, 404);
            }

            $etapasDto = array_map(function($etapa) {
                return new EtapaDto(
                    $etapa->getId(),
                    $etapa->getNombre()
                );
            }, $etapas);

            $etapasArray = array_map(function($dto) {
                return $dto->toArray();
            }, $etapasDto);

            return response('success', 'Etapas obtenidas correctamente.', $etapasArray);
        } catch (Exception $e) {
            return response('error', $e->getMessage(), null, 500);
        }
    }
} 