<?php

    include '../src/repository/periodo-reservas-repository.php';

    class PeriodoReservasService {
        
        private $periodoReservasRepository;

        public function __construct() {
            $this->periodoReservasRepository = new PeriodoReservasRepository();
        }

        public function getPeriodoActual() {
            return $this->periodoReservasRepository->getPeriodoActual();
        }

        public function updatePeriodoReservas($periodo) {
            return $this->periodoReservasRepository->updatePeriodoReservas($periodo);
        }
    }
?>