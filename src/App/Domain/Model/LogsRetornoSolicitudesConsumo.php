<?php

namespace App\Domain\Model;

class LogsRetornoSolicitudesConsumo{
    public function __construct(
        public ?string $id_log_retorno_solicitud_consumo = null,
        public ?int $id_solicitud_consumo = null,
        public ?string $fecha_log_retorno = null,
        public ?float $cantidad_retorno = null
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_log_retorno_solicitud_consumo'] ?? null,
            $data['id_solicitud_consumo'] ?? null,
            $data['fecha_log_retorno'] ?? null,
            $data['cantidad_retorno'] ?? null
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

?>