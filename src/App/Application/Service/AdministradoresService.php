<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IAdministradoresService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use Exception;
use App\Shared\Mapper\Mapper;
use App\Infrastructure\Database\Connection;

class AdministradoresService implements IAdministradoresService {

    private $db;
    private $administradoresRepository;
    private $permisosAdministradoresRepository;
    private $permisosRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->administradoresRepository = new AdministradoresRepository($this->db);
    }

    public function onGetAdministradores(): array{
        $administradores = $this->administradoresRepository->onGet();
        return $administradores;
    }

    public function deleteAdministrador($id): bool{
        try {

            $delete_administrador = $this->administradoresRepository->delete($id);
            
            if ($delete_administrador === 0) {
                throw new Exception("No se eliminó ningún administrador. El ID '$id' no existe o ya fue eliminado.");
            }

            return true;

        } catch (\Throwable $e) {
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

            $id = $administradoresDTO->id_administrador;
            $id_estado = $administradoresDTO->estado_administrador;
            $actualizar = $this->administradoresRepository->updateStatusAdministrador($id, $id_estado);

            if (!$actualizar) {
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

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}


?>