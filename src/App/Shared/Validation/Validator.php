<?php

namespace App\Shared\Validation;

use Exception;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\QuimicosDTO;
use App\Domain\DTO\SolicitudesConsumoDTO;

class Validator
{
    public static function validateDTO(object $dto): void
    {
        switch (true) {
            case $dto instanceof AdministradoresDTO:
                self::validateAdministradoresDTO($dto);
                break;
            case $dto instanceof QuimicosDTO:
                self::validateQuimicosDTO($dto);
                break;
            case $dto instanceof SolicitudesConsumoDTO:
                self::validateSolicitudesConsumoDTO($dto);
                break;
            default:
                throw new Exception('No hay reglas de validación definidas para este DTO.');
        }
    }

    private static function validateAdministradoresDTO(AdministradoresDTO $dto): void
    {
        if ($dto->type == 'register') {
            if (empty($dto->cedula_administrador)) {
                throw new Exception('La cédula es obligatoria.');
            }
            if (empty($dto->nombre_administrador)) {
                throw new Exception('El nombre es obligatorio.');
            }
            if (empty($dto->apellidos_administrador)) {
                throw new Exception('Los apellidos son obligatorios.');
            }
        }
        if ($dto->type == 'login' || $dto->type == 'register') {
            if (empty($dto->correo_hwi_administrador)) {
                throw new Exception('El correo es obligatorio.');
            }
            if (!filter_var($dto->correo_hwi_administrador, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo no es válido.');
            }
            if (empty($dto->password_administrador)) {
                throw new Exception('La contraseña es obligatoria.');
            }
        }
    }

    public static function validateQuimicosDTO(QuimicosDTO $dto): void
    {
        if (empty($dto->descripcion_quimico)) {
            throw new Exception('El nombre del químico es obligatorio.');
        }
        if (empty($dto->fabricante_quimico)) {
            throw new Exception('El fabricante del químico es obligatorio.');
        }
        if (empty($dto->id_peligrosidad_quimico)) {
            throw new Exception('La peligrosidad del químico es obligatoria.');
        }
        if (empty($dto->uso_quimico)) {
            throw new Exception('El uso del químico es obligatorio.');
        }
        if (empty($dto->id_umb_quimico)) {
            throw new Exception('La Umb es obligatoria.');
        }
        if (empty($dto->cantidad_disponible_quimico)) {
            throw new Exception('La cantidad inicial es obligatoria.');
        }
        if (empty($dto->cantidad_maxima_retiro_quimico)) {
            throw new Exception('La cantidad máxima de retiro es obligatoria.');
        }
        if (empty($dto->tope_minimo_quimico)) {
            throw new Exception('El tope mínimo es obligatorio.');
        }
        if (empty($dto->precio_quimico)) {
            throw new Exception('El precio es obligatorio.');
        }
        if (empty($dto->url_etiqueta_emergencia_quimico)) {
            throw new Exception('La url de la etiqueta de emergencia es obligatoria.');
        }
        if (!is_array($dto->quimicosCelulasAreasDTO) || count($dto->quimicosCelulasAreasDTO) === 0) {
            throw new Exception('Debe autorizar al menos una célula.');
        }
    }

    public static function validateSolicitudesConsumoDTO(SolicitudesConsumoDTO $dto): void
    {
        if (empty($dto->id_celula_area_solicitud_consumo)) {
            throw new Exception('La célula es obligatoria.');
        }
        if (empty($dto->id_quimico_solicitud_consumo)) {
            throw new Exception('El químico es obligatorio.');
        }
        if (empty($dto->cantidad_solicitud_consumo)) {
            throw new Exception('La cantidad es obligatoria.');
        }
        if (empty($dto->cedula_solicitante)) {
            throw new Exception('La cédula es obligatoria.');
        }
        if (empty($dto->nombres_solicitante_consumo)) {
            throw new Exception('El nombre es obligatorio.');
        }
        if (empty($dto->apellidos_solicitante_consumo)) {
            throw new Exception('Los apellidos son obligatorios.');
        }
    }
}
