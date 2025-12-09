<?php

namespace App\Application\Service;

use App\Application\Interface\Service\ILoginService;
use App\Domain\DTO\AdministradoresDTO;
use App\Infrastructure\Repository\AdministradoresRepository;
use Exception;
use App\Shared\Mapper\Mapper;
use App\Infrastructure\Database\Connection;

class LoginService implements ILoginService {

    private $db;
    private $administradoresRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->administradoresRepository = new AdministradoresRepository($this->db);
    }

    public function login(AdministradoresDTO $administradoresDTO): AdministradoresDTO {
        $administrador = $this->administradoresRepository->onGet_By__Email($administradoresDTO->correo_hwi_administrador);

        if (!$administrador || !password_verify($administradoresDTO->password_administrador, $administrador->password_administrador)) {
            throw new Exception("Credenciales inválidas.");
        }

        $administradoresDTO = Mapper::modelToAdministradoresDTO($administrador);

        return $administradoresDTO;
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