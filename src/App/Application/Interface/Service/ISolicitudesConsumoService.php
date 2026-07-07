<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\CadenciaActualDTO;
use App\Domain\DTO\SolicitudesConsumoDTO;

interface ISolicitudesConsumoService {
    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool;
    public function updateEstadoSolicitud(int $id_solicitud, int $id_estado, ?float $cantidad_solicitud, ?string $id_quimico): bool;
    public function guardarCadenciaActual(CadenciaActualDTO $cadenciaActualDTO): bool;
    public function onGetSolicitudes(): array;
    public function obtenerCadencias(): array;
    public function obtenerCadenciaActual(): ?CadenciaActualDTO;
}

?>