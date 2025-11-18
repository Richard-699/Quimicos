<?php

namespace App\Application\Interface\Service;

use App\Domain\DTO\AdministradoresDTO;

interface ILoginService {
    public function login(AdministradoresDTO $administradoresDTO): AdministradoresDTO;
    public function validar_email_registrado($email): bool;
    public function guardar_administrador(AdministradoresDTO $administradoresDTO): bool;
    public function actualizar_password(AdministradoresDTO $administradoresDTO): bool;
}

?>