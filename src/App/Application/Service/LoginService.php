<?php

namespace App\Application\Service;

use App\Application\Interface\Service\ILoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\LoginDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use Exception;
use App\Shared\Mapper\Mapper;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\PermisosAdministradoresRepository;

class LoginService implements ILoginService {

    private $db;
    private $administradoresRepository;
    private $permisosAdministradoresRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->administradoresRepository = new AdministradoresRepository($this->db);
        $this->permisosAdministradoresRepository = new PermisosAdministradoresRepository($this->db);
    }

    public function login(AdministradoresDTO $administradoresDTO): LoginDTO {
        $administrador = $this->administradoresRepository->onGet_By__Email($administradoresDTO->correo_hwi_administrador);

        if (!$administrador || !password_verify($administradoresDTO->password_administrador, $administrador->password_administrador)) {
            throw new Exception("Credenciales inválidas.");
        }

        $administradoresDTO = Mapper::modelToAdministradoresDTO($administrador);
        $id = $administrador->id_administrador;

        $permisosAdministradores = $this->permisosAdministradoresRepository->findPermisosByAdministradorId($id);

        return new LoginDTO(
            administrador: $administradoresDTO,
            permisosAdministrador: $permisosAdministradores
        );
    }

    public function validar_email_registrado($email): bool{
        $administrador = $this->administradoresRepository->onGet_By__Email($email);
        if($administrador){
            return true;
        }else{
            return false;
        }
    }

    public function guardar_administrador(AdministradoresDTO $administradoresDTO): bool{
        $administrador = Mapper::administradoresDTOToModel($administradoresDTO);
        return $this->administradoresRepository->save($administrador);
    }

    public function actualizar_password(AdministradoresDTO $administradoresDTO): bool{
        $administrador = $this->administradoresRepository->onGet_By__Email($administradoresDTO->correo_hwi_administrador);
        $id_administrador = $administrador->id_administrador;
        $administradoresDTO->id_administrador = $id_administrador;
        $administrador = Mapper::administradoresDTOToModel($administradoresDTO);
        return $this->administradoresRepository->update_Password($administrador);
    }
}


?>