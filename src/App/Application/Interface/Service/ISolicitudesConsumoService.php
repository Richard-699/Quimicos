<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\CadenciaActualDTO;
use App\Domain\DTO\LogsRetornoSolicitudesConsumoDTO;
use App\Domain\DTO\SolicitudesConsumoDTO;

interface ISolicitudesConsumoService {
    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool;
    public function updateEstadoSolicitud(int $id_solicitud, int $id_estado, ?float $cantidad_solicitud, ?string $id_quimico): bool;
    public function guardarCadenciaActual(CadenciaActualDTO $cadenciaActualDTO): bool;
    public function onGetSolicitudes(string $fecha_minima): array;
    public function obtenerCadencias(): array;
    public function obtenerCadenciaActual(): ?CadenciaActualDTO;
    public function retornarSolicitudConsumo(LogsRetornoSolicitudesConsumoDTO $logsRetornoSolicitudesConsumoDTO): bool;
}

?>