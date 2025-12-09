<?php

namespace App\Domain\DTO;

class CelulasAreasDTO
{
    public function __construct(
        public ?int $id_celulas_areas = null,
        public ?string $nombre_celula
    ) {}
}
