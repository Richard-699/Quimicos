<?php

namespace App\Domain\DTO;

class SolicitudesConsumoDTO {
    public function __construct(
        public ?int $id_solicitud_consumo = null,
        public ?string $fecha_solicitud_consumo = null,
        public ?int $id_celula_area_solicitud_consumo = null,
        public ?string $id_quimico_solicitud_consumo = null,
        public ?float $cantidad_solicitud_consumo = null,
        public ?int $cedula_solicitante = null,
        public ?string $nombres_solicitante_consumo = null,
        public ?string $apellidos_solicitante_consumo = null,
        public ?int $id_estado_solicitud_quimico = null
    ) {}
}
