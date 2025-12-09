<?php

namespace App\Domain\DTO;

class PeligrosidadDTO
{
    public function __construct(
        public ?int $id_peligrosidad,
        public ?string $descripcion_peligrosidad
    ) {}
}
