<?php

namespace App\Domain\DTO;

class LogsIngresoInventarioDTO
{
    public function __construct(
        public QuimicosDTO $quimicosDTO,
        public ?string $id_log_ingreso_inventario = null,
        public ?string $fecha_ingreso_inventario = null,
        public ?float $cantidad_ingreso_inventario = null,
        public ?string $id_quimico_ingreso_inventario = null
    ) {}
}
