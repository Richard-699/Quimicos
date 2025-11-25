<?php

namespace App\Domain\DTO;

class QuimicosCelulasAreasDTO
{
    public function __construct(
        public ?int $id_quimico_celula_area = null,
        public ?string $id_quimico_quimicos = null,
        public ?int $id_celulas_areas_quimicos = null
    ) {}
}
