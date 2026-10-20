<?php

namespace App\Application\Service;

use App\Application\Interface\Service\ITanquesAbastecimientoAguaService;
use App\Infrastructure\Repository\TanquesAbastecimientoAguaRepository;
use App\Infrastructure\Database\Connection;

class TanquesAbastecimientoAguaService implements ITanquesAbastecimientoAguaService
{
    private $db;
    private $tanquesRepository;

    public function __construct()
    {
        $this->db = (new Connection())->dbQuimicosHwi;
        $this->tanquesRepository = new TanquesAbastecimientoAguaRepository($this->db);
    }

    public function onGetTanques(): array
    {
        return $this->tanquesRepository->onGetTanques();
    }
}
