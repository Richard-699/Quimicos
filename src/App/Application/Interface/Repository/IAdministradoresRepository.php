<?php

namespace App\Application\Interface\Repository;

use App\Domain\DTO\AdministradoresDTO;
use App\Domain\Model\Administradores;

interface IAdministradoresRepository {
    public function onGet(): array;
    public function onGet_By__Id($id): ?Administradores;
    public function onGet_By__Email(string $correo_hwi_administrador): ?Administradores;
    public function save(Administradores $administradores): bool;
    public function update_password(Administradores $administradores): bool;
    public function delete(string $id);
    public function updateStatusAdministrador($id, $id_estado): bool;
}

?>