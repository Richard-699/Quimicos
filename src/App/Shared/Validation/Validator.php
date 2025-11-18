<?php

namespace App\Shared\Validation;

use Exception;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\AlmacenesDTO;
use App\Domain\DTO\ConteoDTO;
use App\Domain\DTO\ExactitudDTO;
use App\Domain\DTO\GruposDTO;
use App\Domain\DTO\LocalizacionesDTO;
use App\Domain\DTO\PartNumbersDTO;
use App\Domain\DTO\StockDTO;

class Validator
{
    public static function validateDTO(object $dto): void
    {
        switch (true) {
            case $dto instanceof AdministradoresDTO:
                self::validateAdministradoresDTO($dto);
                break;
            case $dto instanceof AlmacenesDTO:
                self::validateAlmacenesDTO($dto);
                break;
            case $dto instanceof LocalizacionesDTO:
                self::validateLocalizacionesDTO($dto);
                break;
            case $dto instanceof PartNumbersDTO:
                self::validatePartnumberDTO($dto);
                break;
            case $dto instanceof StockDTO:
                self::validateStockDTO($dto);
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

    public static function validateListaPermisos(array $listaPermisosDTO): void
    {
        if (empty($listaPermisosDTO)) {
            throw new Exception('Debes seleccionar al menos un permiso.');
        }
    }


    public static function validateAlmacenesDTO(AlmacenesDTO $dto): void
    {
        if (empty($dto->codigo_sap)) {
            throw new Exception('El código SAP es obligatorio.');
        }
        if (empty($dto->descripcion_almacen)) {
            throw new Exception('La descripcion es obligatoria.');
        }

        if (!is_array($dto->clasificacionesAlmacenesDTO) || count($dto->clasificacionesAlmacenesDTO) === 0) {
            throw new Exception('Debe seleccionar al menos una clasificación.');
        }
    }

    public static function validateGruposDTO(GruposDTO $dto): void
    {
        if (empty($dto->descripcion_grupo)) {
            throw new Exception('La descripción es obligatoria');
        }

        if (empty($dto->fecha_programacion_grupo)) {
            throw new Exception('La fecha de conteo es obligatoria');
        }
    }

    public static function validateLocalizacionesDTO(LocalizacionesDTO $dto): void
    {
        if (empty($dto->id_tipo_localizacion_localizaciones)) {
            throw new Exception('El tipo es obligatorio.');
        }
        if (empty($dto->descripcion_localizacion)) {
            throw new Exception('La descripcion es obligatoria.');
        }
        if (empty($dto->id_tipo_almacenamientos_localizaciones)) {
            throw new Exception('El tipo de almacenamiento es obligatorio.');
        }
    }

    public static function validatePartnumberDTO(PartNumbersDTO $dto): void
    {
        if (empty($dto->partnumber)) {
            throw new Exception('El partnumber es obligatorio.');
        }
        if (empty($dto->descripcion_breve)) {
            throw new Exception('El Texto breve de material SAP es obligatorio.');
        }
        if (empty($dto->id_umb_partnumber)) {
            throw new Exception('La UMB es obligatoria.');
        }
        if (empty($dto->nombre_interno)) {
            throw new Exception('El nombre interno es obligatorio.');
        }
        if (empty($dto->id_plataforma_partnumber)) {
            throw new Exception('La plataforma es obligatoria.');
        }
    }

    public static function validateArchivosSAP(array $files): void
    {
        $camposEsperados = ['mb52', 'wm', '0016'];

        foreach ($camposEsperados as $campo) {
            if (
                !isset($files[$campo]) ||
                $files[$campo]['error'] !== UPLOAD_ERR_OK ||
                empty($files[$campo]['tmp_name'])
            ) {
                throw new Exception("El archivo '{$campo}' no se ha subido correctamente.");
            }

            $ext = strtolower(pathinfo($files[$campo]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['xls', 'xlsx'])) {
                throw new Exception("El archivo '{$campo}' debe ser .xls o .xlsx.");
            }
        }
    }

    public static function validateStockDTO(StockDTO $dto): void
    {
        if (empty($dto->id_partnumber_stock)) {
            throw new Exception('El partnumber es obligatorio');
        }

        if (empty($dto->id_almacen_stock)) {
            throw new Exception('El almacén es obligatorio');
        }

        if (empty($dto->id_localizacion_stock)) {
            throw new Exception('La localización es obligatoria');
        }

        if (empty($dto->cantidad_stock)) {
            throw new Exception('La cantidad es obligatoria');
        }
    }

    public static function validateExactitudDTO(ExactitudDTO $dto): void
    {
        if (empty($dto->coincide_exactitud)) {
            throw new Exception('Seleccione algo en el campo "¿Hay exactitud en esta ubicación?"');
        }

        if (empty($dto->novedad_exactitud)) {
            throw new Exception('La novedad es obligatoria');
        }
    }


        public static function validateConteoDTO(ConteoDTO $dto): void
    {
        if (empty($dto->observacion_final_conteo)) {
            throw new Exception('Debe registrar una observación');
        }
    }
}
