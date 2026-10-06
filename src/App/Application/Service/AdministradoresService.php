<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IAdministradoresService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use App\Infrastructure\Repository\PermisosAdministradoresRepository;
use App\Infrastructure\Repository\PermisosRepository;
use Exception;
use App\Infrastructure\Database\Connection;
use App\Shared\Mapper\Mapper;

class AdministradoresService implements IAdministradoresService {

    private $db;
    private $administradoresRepository;
    private $permisosAdministradoresRepository;
    private $permisosRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->administradoresRepository = new AdministradoresRepository($this->db);
        $this->permisosRepository = new PermisosRepository($this->db);
        $this->permisosAdministradoresRepository = new PermisosAdministradoresRepository($this->db);
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

    public function obtenerPermisos(): ?array {
        return $this->permisosRepository->findAll();
    }

    public function obtenerPermisosAdministradorById(string $id): ?array {
        return $this->permisosAdministradoresRepository->findPermisosByAdministradorId($id);
    }

    public function aprobarAdministrador(AdministradoresDTO $administradorDTO): ?AdministradoresDTO {
        try {
            $this->db->beginTransaction();

            foreach ($administradorDTO->permisosAdministrador as $p) {
                if (!$this->permisosAdministradoresRepository->assignPermisosAdministradores($p, $administradorDTO->id_administrador)) {
                    throw new Exception('No se pudo guardar el permiso: ' . $p);
                }
            }

            if (!$this->administradoresRepository->updateStatusAdministrador($administradorDTO->id_administrador, 1)) {
                throw new Exception('No se pudo actualizar el estado del administrador.');
            }

            if (!$this->administradoresRepository->updateCelulaConsumoAgua($administradorDTO->id_administrador, $administradorDTO->id_celula_consumo_agua)) {
                throw new Exception('No se pudo actualizar la célula de consumo de agua.');
            }

            $administrador = $this->administradoresRepository->onGet_By__Id($administradorDTO->id_administrador);
            $administradorDTO = Mapper::modelToAdministradoresDTO($administrador);

            $this->db->commit();
            return $administradorDTO;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updatePermisosAdministrador(AdministradoresDTO $administradorDTO): ?bool {
        try {
            $this->db->beginTransaction();

            $permisosActualesAdministrador = $this->permisosAdministradoresRepository->findPermisosByAdministradorId($administradorDTO->id_administrador);
            if (count($permisosActualesAdministrador) > 0) {
                if (!$this->permisosAdministradoresRepository->removeAllPermisosFromAdministradorByAdministradorId($administradorDTO->id_administrador)) {
                    throw new Exception('No se pudo eliminar los permisos del administrador');
                }
            }

            foreach ($administradorDTO->permisosAdministrador as $p) {
                if (!$this->permisosAdministradoresRepository->assignPermisosAdministradores($p, $administradorDTO->id_administrador)) {
                    throw new Exception('No se pudo guardar el permiso: ' . $p);
                }
            }

            if (!$this->administradoresRepository->updateCelulaConsumoAgua($administradorDTO->id_administrador, $administradorDTO->id_celula_consumo_agua)) {
                throw new Exception('No se pudo actualizar la célula de consumo de agua.');
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}


?>