<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\SolicitudesConsumoDTO;

interface ISolicitudesConsumoService {
    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool;
    public function updateEstadoSolicitud(int $id_solicitud, int $id_estado, ?float $cantidad_solicitud, ?string $id_quimico): bool;
}

?>