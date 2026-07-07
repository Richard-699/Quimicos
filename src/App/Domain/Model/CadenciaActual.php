<?php

namespace App\Domain\Model;

class CadenciaActual {
    public function __construct(
        public ?int $id_cadencia_actual,
        public ?string $fecha_hora_registro_cadencia_actual,
        public ?int $id_cadencias_cadencia_actual,
        public ?string $id_administrador_cadencia_actual
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_cadencia_actual'] ?? null,
            $data['fecha_registro_cadencia_actual'] ?? null,
            $data['id_cadencias_cadencia_actual'] ?? null,
            $data['id_administrador_cadencia_actual'] ?? null
        );
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}

?>