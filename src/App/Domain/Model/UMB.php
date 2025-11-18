<?php

namespace App\Domain\Model;

class UMB{
    public function __construct(
        public ?int $id_umb = null,
        public ?string $descripcion_umb
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_umb'] ?? null,
            $data['descripcion_umb'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_umb' => $this->id_umb,
            'descripcion_umb' => $this->descripcion_umb
        ];
    }
}

?>