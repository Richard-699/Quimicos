<?php

namespace App\Domain\Model;

class LogsPrecios{
    public function __construct(
        public ?int $id_log_precio = null,
        public ?string $fecha_log_precio = null,
        public ?string $id_quimico_log_precio = null,
        public ?float $precio_quimico = null
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_log_precio'] ?? null,
            $data['fecha_log_precio'] ?? null,
            $data['id_quimico_log_precio'] ?? null,
            $data['precio_quimico'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_log_precio' => $this->id_log_precio,
            'fecha_log_precio' => $this->fecha_log_precio,
            'id_quimico_log_precio' => $this->id_quimico_log_precio,
            'precio_quimico' => $this->precio_quimico
        ];
    }
}

?>