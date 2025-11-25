<?php

namespace App\Domain\Model;

class CelulasAreas{
    public function __construct(
        public ?int $id_celulas_areas = null,
        public ?string $nombre_celula
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_celulas_areas'] ?? null,
            $data['nombre_celula'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_celulas_areas' => $this->id_celulas_areas,
            'nombre_celula' => $this->nombre_celula
        ];
    }
}

?>