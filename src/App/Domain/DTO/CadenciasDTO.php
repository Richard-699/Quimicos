<?php
namespace App\Domain\DTO;

class CadenciasDTO {
    public function __construct(
        public ?int $id_cadencia,
        public ?int $cadencia
    ) {}
}

?>
