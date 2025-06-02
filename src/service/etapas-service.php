<?php
require_once '../src/repository/etapas-repository.php';
require_once '../src/entity/etapa-entity.php';

class EtapasService {
    private $etapasRepository;

    public function __construct() {
        $this->etapasRepository = new EtapasRepository();
    }

    public function getEtapas() {
        return $this->etapasRepository->getEtapas();
    }
} 