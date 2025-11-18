<?php

namespace App\Shared\Mapper;

use App\Domain\Model\Administradores;
use App\Domain\DTO\AdministradoresDTO;

class Mapper
{
    public static function modelToAdministradoresDTO(Administradores $model): AdministradoresDTO
    {
        return new AdministradoresDTO(
            id_administrador: $model->id_administrador,
            cedula_administrador: $model->cedula_administrador,
            nombre_administrador: $model->nombre_administrador,
            apellidos_administrador: $model->apellidos_administrador,
            correo_hwi_administrador: $model->correo_hwi_administrador,
            password_administrador: $model->password_administrador,
            password_is_temporal: $model->password_is_temporal,
            estado_administrador: $model->estado_administrador
        );
    }

    public static function administradoresDTOToModel(AdministradoresDTO $dto): Administradores
    {
        return new Administradores(
            $dto->id_administrador,
            $dto->cedula_administrador,
            $dto->nombre_administrador,
            $dto->apellidos_administrador,
            $dto->correo_hwi_administrador,
            $dto->password_administrador,
            $dto->password_is_temporal,
            $dto->estado_administrador
        );
    }
}
