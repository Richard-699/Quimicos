<?php

namespace App\Domain\Model;

class Administradores {
    public function __construct(
        public ?string $id_administrador,
        public ?int $cedula_administrador,
        public ?string $nombre_administrador,
        public ?string $apellidos_administrador,
        public ?string $correo_hwi_administrador,
        public ?string $password_administrador,
        public ?int $password_is_temporal,
        public ?string $estado_administrador
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            $data['id_administrador'] ?? null,
            $data['cedula_administrador'] ?? null,
            $data['nombre_administrador'] ?? null,
            $data['apellidos_administrador'] ?? null,
            $data['correo_hwi_administrador'] ?? null,
            $data['password_administrador'] ?? null,
            $data['password_is_temporal'] ?? null,
            $data['estado_administrador'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id_administrador' => $this->id_administrador,
            'cedula_administrador' => $this->cedula_administrador,
            'nombre_administrador' => $this->nombre_administrador,
            'apellidos_administrador' => $this->apellidos_administrador,
            'correo_hwi_administrador' => $this->correo_hwi_administrador,
            'password_administrador' => $this->password_administrador,
            'password_is_temporal' => $this->password_is_temporal,
            'estado_administrador' => $this->estado_administrador
        ];
    }
}

?>