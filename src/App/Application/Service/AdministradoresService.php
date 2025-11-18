<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IAdministradoresService;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\PermisosAdministradoresDTO;
use App\Infrastructure\Repository\PermisosAdministradoresRepository;
use App\Infrastructure\Repository\AdministradoresRepository;
use App\Infrastructure\Repository\PermisosRepository;
use Exception;
use App\Shared\Mapper\Mapper;
use App\Infrastructure\Database\Connection;

class AdministradoresService implements IAdministradoresService {

    private $db;
    private $administradoresRepository;
    private $permisosAdministradoresRepository;
    private $permisosRepository;

    public function __construct() {
        $this->db = (new Connection())->dbInventarioHwi;

        $this->administradoresRepository = new AdministradoresRepository($this->db);
        $this->permisosAdministradoresRepository = new PermisosAdministradoresRepository($this->db);
        $this->permisosRepository = new PermisosRepository($this->db);
    }

    public function onGetAdministradores(): array{
        $administradores = $this->administradoresRepository->onGet();
        return $administradores;
    }

    public function deleteAdministrador($id): bool{
        try {
            $this->db->beginTransaction();

            $delete_permisos_administrador = $this->permisosAdministradoresRepository->delete($id);
            if (!$delete_permisos_administrador) {
                throw new Exception("Error al eliminar los permisos del administrador con ID '$id'.");
            }

            $delete_administrador = $this->administradoresRepository->delete($id);
            if ($delete_administrador === 0) {
                throw new Exception("No se eliminó ningún administrador. El ID '$id' no existe o ya fue eliminado.");
            }

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function onGetPermisos(): array{
        $permisos = $this->permisosRepository->onGet();
        return $permisos;
    }

    public function aprobarAdministrador(AdministradoresDTO $administradoresDTO): bool
    {
        try {
            $this->db->beginTransaction();

            foreach ($administradoresDTO->permisosAdministradoresDTO as $permisoAdministradorDTO) {
                $dto = new PermisosAdministradoresDTO();
                $dto->id_permiso_permisos = $permisoAdministradorDTO->id_permiso_permisos;
                $dto->id_administrador_permisos = $administradoresDTO->id_administrador;
                
                $permisos_administradores = Mapper::permisosAdministradoresDTOToModel($dto);
                $this->permisosAdministradoresRepository->save($permisos_administradores);
            }

            $id = $administradoresDTO->id_administrador;
            $id_estado = $administradoresDTO->estado_administrador;
            $actualizado = $this->administradoresRepository->updateStatusAdministrador($id, $id_estado);

            if (!$actualizado) {
                throw new \Exception("No se pudo actualizar el estado del administrador con ID '$id'.");
            }

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updatePermisosAdministrador(AdministradoresDTO $administradoresDTO): bool
    {
        try {
            $this->db->beginTransaction();

            $id = $administradoresDTO->id_administrador;
            $this->permisosAdministradoresRepository->delete($id);

            foreach ($administradoresDTO->permisosAdministradoresDTO as $permisoAdministradorDTO) {
                $dto = new PermisosAdministradoresDTO();
                $dto->id_permiso_permisos = $permisoAdministradorDTO->id_permiso_permisos;
                $dto->id_administrador_permisos = $administradoresDTO->id_administrador;
                
                $permisos_administradores = Mapper::permisosAdministradoresDTOToModel($dto);
                $this->permisosAdministradoresRepository->save($permisos_administradores);
            }

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function onGetPermisosAdministrador($id): array
    {
        $permisosAdministrador = $this->permisosAdministradoresRepository->onGet_By__Id_Administrador($id);
        return $permisosAdministrador;
    }
}


?>