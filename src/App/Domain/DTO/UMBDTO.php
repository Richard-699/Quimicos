<?php

namespace App\Domain\DTO;

class UMBDTO
{
    public function __construct(
        public ?int $id_umb = null,
        public ?string $descripcion_umb
    ) {}
}
