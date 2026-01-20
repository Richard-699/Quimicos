<?php

namespace App\Domain\DTO;

class LogsPreciosDTO
{
    public function __construct(
        public ?int $id_log_precio = null,
        public ?string $fecha_log_precio = null,
        public ?string $id_quimico_log_precio = null,
        public ?float $precio_quimico = null
    ) {}
}
