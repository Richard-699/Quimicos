<?php

namespace App\Domain\Model;

class SolicitudesConsumo {
    public function __construct(
        public ?int $id_solicitud_consumo,
        public ?string $fecha_solicitud_consumo,
        public ?int $id_celula_area_solicitud_consumo,
        public ?string $id_quimico_solicitud_consumo,
        public ?float $cantidad_solicitud_consumo,
        public ?int $cedula_solicitante,
        public ?string $nombres_solicitante_consumo,
        public ?string $apellidos_solicitante_consumo,
        public ?int $id_estado_solicitud_quimico
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_solicitud_consumo'] ?? null,
            $data['fecha_solicitud_consumo'] ?? null,
            $data['id_celula_area_solicitud_consumo'] ?? null,
            $data['id_quimico_solicitud_consumo'] ?? null,
            $data['cantidad_solicitud_consumo'] ?? null,
            $data['cedula_solicitante'] ?? null,
            $data['nombres_solicitante_consumo'] ?? null,
            $data['apellidos_solicitante_consumo'] ?? null,
            $data['id_estado_solicitud_quimico'] ?? null
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

?>