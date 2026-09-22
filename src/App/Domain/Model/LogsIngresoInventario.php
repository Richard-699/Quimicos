<?php

namespace App\Domain\Model;

class LogsIngresoInventario{
    public function __construct(
        public ?string $id_log_ingreso_inventario = null,
        public ?string $fecha_ingreso_inventario = null,
        public ?float $cantidad_ingreso_inventario = null,
        public ?string $id_quimico_ingreso_inventario = null
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_log_ingreso_inventario'] ?? null,
            $data['fecha_ingreso_inventario'] ?? null,
            $data['cantidad_ingreso_inventario'] ?? null,
            $data['id_quimico_ingreso_inventario'] ?? null
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

?>