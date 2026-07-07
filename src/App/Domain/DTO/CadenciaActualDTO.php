<?php
namespace App\Domain\DTO;

class CadenciaActualDTO {
    public function __construct(
        public ?int $id_cadencia_actual,
        public ?string $fecha_hora_registro_cadencia_actual,
        public ?int $id_cadencias_cadencia_actual,
        public ?string $cadencia,
        public ?string $id_administrador_cadencia_actual
    ) {}
}

?>
