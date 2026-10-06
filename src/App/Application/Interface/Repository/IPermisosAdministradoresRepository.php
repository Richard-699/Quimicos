<?php

namespace App\Application\Interface\Repository;

interface IPermisosAdministradoresRepository {
    public function findPermisosByAdministradorId(string $id_administrador): array;
    public function removeAllPermisosFromAdministradorByAdministradorId(string $id_administrador): int;
    public function assignPermisosAdministradores(int $id_permiso, string $id_administrador): bool;
}

?>