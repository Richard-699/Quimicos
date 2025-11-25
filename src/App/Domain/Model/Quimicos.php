<?php

namespace App\Domain\Model;

class Quimicos {
    public function __construct(
        public ?string $id_quimico,
        public ?string $descripcion_quimico,
        public ?int $id_umb_quimico,
        public ?float $cantidad_disponible_quimico,
        public ?float $cantidad_maxima_retiro_quimico,
        public ?float $tope_minimo_quimico,
        public ?float $precio_quimico,
        public ?int $id_estado_quimico
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_quimico'] ?? null,
            $data['descripcion_quimico'] ?? null,
            $data['id_umb_quimico'] ?? null,
            $data['cantidad_disponible_quimico'] ?? null,
            $data['cantidad_maxima_retiro_quimico'] ?? null,
            $data['tope_minimo_quimico'] ?? null,
            $data['precio_quimico'] ?? null,
            $data['id_estado_quimico'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_quimico' => $this->id_quimico,
            'descripcion_quimico' => $this->descripcion_quimico,
            'id_umb_quimico' => $this->id_umb_quimico,
            'cantidad_disponible_quimico' => $this->cantidad_disponible_quimico,
            'cantidad_maxima_retiro_quimico' => $this->cantidad_maxima_retiro_quimico,
            'tope_minimo_quimico' => $this->tope_minimo_quimico,
            'precio_quimico' => $this->precio_quimico,
            'id_estado_quimico' => $this->id_estado_quimico
        ];
    }
}

?>