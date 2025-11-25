<?php

namespace App\Domain\DTO;

class QuimicosDTO {
    public function __construct(
        public ?string $id_quimico = null,
        public ?string $descripcion_quimico = null,
        public ?int $id_umb_quimico = null,
        public ?string $umb_quimico = null,
        public ?float $cantidad_disponible_quimico = null,
        public ?float $cantidad_maxima_retiro_quimico = null,
        public ?float $tope_minimo_quimico = null,
        public ?float $precio_quimico = null,
        public ?int $id_estado_quimico = null,
        public ?array $quimicosCelulasAreasDTO = null
    ) {}
}
