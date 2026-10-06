<?php

namespace App\Domain\Model;

class TanquesAbastecimientoAgua
{
    public function __construct(
        public ?int $id_tanque_abastecimiento_agua = null,
        public ?string $tanque_abastecimiento_agua = null
    ) {}
}
