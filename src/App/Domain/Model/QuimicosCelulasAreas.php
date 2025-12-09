<?php

namespace App\Domain\Model;

class QuimicosCelulasAreas{
    public function __construct(
        public ?int $id_quimico_celula_area = null,
        public ?string $id_quimico_quimicos = null,
        public ?int $id_celulas_areas_quimicos = null,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_quimico_celula_area'] ?? null,
            $data['id_quimico_quimicos'] ?? null,
            $data['id_celulas_areas_quimicos'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_quimico_celula_area' => $this->id_quimico_celula_area,
            'id_quimico_quimicos' => $this->id_quimico_quimicos,
            'id_celulas_areas_quimicos' => $this->id_celulas_areas_quimicos
        ];
    }
}

?>