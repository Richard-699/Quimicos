<?php

namespace App\Domain\DTO;

class LogsRetornoSolicitudesConsumoDTO
{
    public function __construct(
        public SolicitudesConsumoDTO $solicitudesConsumoDTO,
        public ?string $id_log_retorno_solicitud_consumo = null,
        public ?int $id_solicitud_consumo = null,
        public ?string $fecha_log_retorno = null,
        public ?float $cantidad_retorno = null
    ) {}
}
