<?php

namespace App\Shared\Mapper;

use App\Domain\Model\Administradores;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\CelulasAreasDTO;
use App\Domain\DTO\QuimicosCelulasAreasDTO;
use App\Domain\DTO\QuimicosDTO;
use App\Domain\DTO\SolicitudesConsumoDTO;
use App\Domain\DTO\UMBDTO;
use App\Domain\Model\CelulasAreas;
use App\Domain\Model\Quimicos;
use App\Domain\Model\QuimicosCelulasAreas;
use App\Domain\Model\SolicitudesConsumo;
use App\Domain\Model\UMB;

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

    public static function modelToQuimicosDTO(array $models): array
    {
        if (empty($models)) {
            return [];
        }
        return array_map(function (Quimicos $model): QuimicosDTO {
            return new QuimicosDTO(
                id_quimico: $model->id_quimico,
                descripcion_quimico: $model->descripcion_quimico,
                id_umb_quimico: $model->id_umb_quimico,
                cantidad_disponible_quimico: $model->cantidad_disponible_quimico,
                cantidad_maxima_retiro_quimico: $model->cantidad_maxima_retiro_quimico,
                tope_minimo_quimico: $model->tope_minimo_quimico,
                precio_quimico: $model->precio_quimico,
                url_etiqueta_emergencia_quimico: $model->url_etiqueta_emergencia_quimico,
                id_estado_quimico: $model->id_estado_quimico
            );
        }, $models);
    }

    public static function modelToQuimicoDTO(Quimicos $model): QuimicosDTO
    {
        return new QuimicosDTO(
            id_quimico: $model->id_quimico,
            descripcion_quimico: $model->descripcion_quimico,
            id_umb_quimico: $model->id_umb_quimico,
            cantidad_disponible_quimico: $model->cantidad_disponible_quimico,
            cantidad_maxima_retiro_quimico: $model->cantidad_maxima_retiro_quimico,
            tope_minimo_quimico: $model->tope_minimo_quimico,
            precio_quimico: $model->precio_quimico,
            url_etiqueta_emergencia_quimico: $model->url_etiqueta_emergencia_quimico,
            id_estado_quimico: $model->id_estado_quimico
        );
    }

    public static function quimicosDTOToModel(QuimicosDTO $dto): Quimicos
    {
        return new Quimicos(
            $dto->id_quimico,
            $dto->descripcion_quimico,
            $dto->id_umb_quimico,
            $dto->cantidad_disponible_quimico,
            $dto->cantidad_maxima_retiro_quimico,
            $dto->tope_minimo_quimico,
            $dto->precio_quimico,
            $dto->url_etiqueta_emergencia_quimico,
            $dto->id_estado_quimico
        );
    }

    public static function modelToCelulasAreasDTO(array $models): array
    {
        if (empty($models)) {
            return [];
        }
        return array_map(function (CelulasAreas $model): CelulasAreasDTO {
            return new CelulasAreasDTO(
                id_celulas_areas: $model->id_celulas_areas,
                nombre_celula: $model->nombre_celula
            );
        }, $models);
    }

    public static function celulasAreasDTOToModel(CelulasAreasDTO $dto): CelulasAreas
    {
        return new CelulasAreas(
            $dto->id_celulas_areas,
            $dto->nombre_celula
        );
    }

    public static function modelToUmbsDTO(array $models): array
    {
        if (empty($models)) {
            return [];
        }
        return array_map(function (UMB $model): UMBDTO {
            return new UMBDTO(
                id_umb: $model->id_umb,
                descripcion_umb: $model->descripcion_umb
            );
        }, $models);
    }

    public static function umbsDTOToModel(UMBDTO $dto): UMB
    {
        return new UMB(
            $dto->id_umb,
            $dto->descripcion_umb
        );
    }

    public static function modelToQuimicosCelulasAreasDTO(array $models): array
    {
        if (empty($models)) {
            return [];
        }
        return array_map(function (QuimicosCelulasAreas $model): QuimicosCelulasAreasDTO {
            return new QuimicosCelulasAreasDTO(
                id_quimico_celula_area: $model->id_quimico_celula_area,
                id_quimico_quimicos: $model->id_quimico_quimicos,
                id_celulas_areas_quimicos: $model->id_celulas_areas_quimicos,
            );
        }, $models);
    }

    public static function quimicosCelulasAreasDTOToModel(QuimicosCelulasAreasDTO $dto): QuimicosCelulasAreas
    {
        return new QuimicosCelulasAreas(
            $dto->id_quimico_celula_area,
            $dto->id_quimico_quimicos,
            $dto->id_celulas_areas_quimicos
        );
    }

    public static function modelToSolicitudesConsumoDTO(SolicitudesConsumo $model): SolicitudesConsumoDTO
    {
        return new SolicitudesConsumoDTO(
            id_solicitud_consumo: $model->id_solicitud_consumo,
            fecha_solicitud_consumo: $model->fecha_solicitud_consumo,
            id_celula_area_solicitud_consumo: $model->id_celula_area_solicitud_consumo,
            id_quimico_solicitud_consumo: $model->id_quimico_solicitud_consumo,
            cantidad_solicitud_consumo: $model->cantidad_solicitud_consumo,
            cedula_solicitante: $model->cedula_solicitante,
            nombres_solicitante_consumo: $model->nombres_solicitante_consumo,
            apellidos_solicitante_consumo: $model->apellidos_solicitante_consumo,
            id_estado_solicitud_quimico: $model->id_estado_solicitud_quimico
        );
    }

    public static function solicitudesConsumoDTOToModel(SolicitudesConsumoDTO $dto): SolicitudesConsumo
    {
        return new SolicitudesConsumo(
            $dto->id_solicitud_consumo,
            $dto->fecha_solicitud_consumo,
            $dto->id_celula_area_solicitud_consumo,
            $dto->id_quimico_solicitud_consumo,
            $dto->cantidad_solicitud_consumo,
            $dto->cedula_solicitante,
            $dto->nombres_solicitante_consumo,
            $dto->apellidos_solicitante_consumo,
            $dto->id_estado_solicitud_quimico
        );
    }
}
