<?php

namespace App\Domain\Model;

class Quimicos {
    public function __construct(
        public ?string $id_quimico,
        public ?string $descripcion_quimico,
        public ?string $fabricante_quimico,
        public ?int $id_peligrosidad_quimico,
        public ?string $uso_quimico,
        public ?int $id_umb_quimico,
        public ?float $cantidad_disponible_quimico,
        public ?float $cantidad_maxima_retiro_quimico,
        public ?float $tope_minimo_quimico,
        public ?float $cantidad_minima_almacenamiento_quimico = null,
        public ?float $cantidad_maxima_almacenamiento_quimico = null,
        public ?int $tiempo_entrega_minimo_quimico = null,
        public ?int $tiempo_entrega_maximo_quimico = null,
        public ?float $precio_quimico,
        public ?string $url_etiqueta_emergencia_quimico,
        public ?int $id_estado_quimico
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_quimico'] ?? null,
            $data['descripcion_quimico'] ?? null,
            $data['fabricante_quimico'] ?? null,
            $data['id_peligrosidad_quimico'] ?? null,
            $data['uso_quimico'] ?? null,
            $data['id_umb_quimico'] ?? null,
            $data['cantidad_disponible_quimico'] ?? null,
            $data['cantidad_maxima_retiro_quimico'] ?? null,
            $data['tope_minimo_quimico'] ?? null,
            $data['cantidad_minima_almacenamiento_quimico'] ?? null,
            $data['cantidad_maxima_almacenamiento_quimico'] ?? null,
            $data['tiempo_entrega_minimo_quimico'] ?? null,
            $data['tiempo_entrega_maximo_quimico'] ?? null,
            $data['precio_quimico'] ?? null,
            $data['url_etiqueta_emergencia_quimico'] ?? null,
            $data['id_estado_quimico'] ?? null
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

?>