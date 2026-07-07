<?php

namespace App\Domain\Model;

class Cadencias {
    public function __construct(
        public ?int $id_cadencia,
        public ?int $cadencia
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_cadencia'] ?? null,
            $data['cadencia'] ?? null
        );
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}

?>