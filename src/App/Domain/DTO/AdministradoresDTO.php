<?php

namespace App\Domain\DTO;

use App\Domain\DTO\PermisosAdministradoresDTO;
use App\Domain\DTO\PermisosDTO;

class AdministradoresDTO {
    /**
     * @param PermisosAdministradoresDTO[]|null $permisosAdministradoresDTO
     * @param PermisosDTO[]|null $permisosDTO
     */
    public function __construct(
        public ?string $id_administrador = null,
        public ?int $cedula_administrador = null,
        public ?string $nombre_administrador = null,
        public ?string $apellidos_administrador = null,
        public ?string $correo_hwi_administrador,
        public ?string $password_administrador,
        public ?int $password_is_temporal = null,
        public ?int $estado_administrador = null,
        public ?array $permisosAdministradoresDTO = null,
        public ?string $type = null,
        public ?array $permisosDTO = null
    ) {}
}
