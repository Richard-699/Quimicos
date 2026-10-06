<?php

namespace App\Domain\DTO;

class LoginDTO {
    public function __construct(
        public ?AdministradoresDTO $administrador = null,
        public ?array $permisosAdministrador = null
    ) {}
}
