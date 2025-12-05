<?php

namespace App\Domain\Model;

class Peligrosidad{
    public function __construct(
        public ?int $id_peligrosidad,
        public ?string $descripcion_peligrosidad
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_peligrosidad'] ?? null,
            $data['descripcion_peligrosidad'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_peligrosidad' => $this->id_peligrosidad,
            'descripcion_peligrosidad' => $this->descripcion_peligrosidad
        ];
    }
}

?>