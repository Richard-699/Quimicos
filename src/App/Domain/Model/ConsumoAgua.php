<?php

namespace App\Domain\Model;

class ConsumoAgua
{
    public function __construct(
        public ?int $id_consumo_agua = null,
        public ?string $fecha_consumo_agua = null,
        public ?int $id_celula_consumo_agua = null,
        public ?int $id_tanque_abastecimiento_consumo_agua = null,
        public ?float $consumo_inicial_agua = null,
        public ?float $consumo_final_agua = null
    ) {}
}
