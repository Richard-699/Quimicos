<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\SolicitudesConsumoDTO;

interface ISolicitudesQuimicosService {
    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool;
}

?>