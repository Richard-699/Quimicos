<?php

namespace App\Shared\Validation;

use Exception;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\CadenciaActualDTO;
use App\Domain\DTO\LogsIngresoInventarioDTO;
use App\Domain\DTO\LogsRetornoSolicitudesConsumoDTO;
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
            case $dto instanceof CadenciaActualDTO:
                self::validateCadenciaActualDTO($dto);
                break;
            case $dto instanceof LogsRetornoSolicitudesConsumoDTO:
                self::validateLogsRetornoSolicitudesConsumoDTO($dto);
                break;
            case $dto instanceof LogsIngresoInventarioDTO:
                self::validateLogsIngresoInventarioDTO($dto);
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
        if (empty($dto->cantidad_minima_almacenamiento_quimico)) {
            throw new Exception('La cantidad mínima de almacenamiento es obligatoria.');
        }
        if (empty($dto->cantidad_maxima_almacenamiento_quimico)) {
            throw new Exception('La cantidad máxima de almacenamiento es obligatoria.');
        }
        if (empty($dto->tiempo_entrega_minimo_quimico)) {
            throw new Exception('El tiempo de entrega mínimo es obligatorio.');
        }
        if (empty($dto->tiempo_entrega_maximo_quimico)) {
            throw new Exception('El tiempo de entrega máximo es obligatorio.');
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

    private static function validateCadenciaActualDTO(CadenciaActualDTO $dto): void
    {
        if (empty($dto->fecha_hora_registro_cadencia_actual)) {
            throw new Exception('Error al generar la fecha y la hora.');
        }
        if (empty($dto->id_cadencias_cadencia_actual)) {
            throw new Exception('La cadencia es obligatoria.');
        }
        if (empty($dto->id_administrador_cadencia_actual)) {
            throw new Exception('Error al obtener el Id del administrador.');
        }
    }

    private static function validateLogsRetornoSolicitudesConsumoDTO(LogsRetornoSolicitudesConsumoDTO $dto): void
    {
        if (empty($dto->id_log_retorno_solicitud_consumo)) {
            throw new Exception('Error al generar el ID del log de retorno.');
        }
        if (empty($dto->id_solicitud_consumo)) {
            throw new Exception('Error al obtener el id de la solicitud.');
        }
        if (empty($dto->fecha_log_retorno)) {
            throw new Exception('Error al generar la fecha y la hora.');
        }
        if (empty($dto->cantidad_retorno)) {
            throw new Exception('La cantidad de retorno es obligatoria.');
        }else{
            if ($dto->cantidad_retorno <= 0) {
                throw new Exception("La cantidad a retornar debe ser mayor a cero.");
            }
        }
        if ($dto->solicitudesConsumoDTO !== null) {
            $nuevaCantidad = $dto->solicitudesConsumoDTO->cantidad_solicitud_consumo;

            if ($nuevaCantidad < 0) {
                $retorno = $dto->cantidad_retorno;
                $original = $nuevaCantidad + $retorno;

                throw new Exception("La cantidad a retornar ({$retorno}) no puede ser mayor a la cantidad solicitada ({$original}).");
            }
        }
    }

    private static function validateLogsIngresoInventarioDTO(LogsIngresoInventarioDTO $dto): void
    {
        if (empty($dto->cantidad_ingreso_inventario)) {
            throw new Exception('La cantidad de ingreso es obligatoria.');
        } else {
            if ($dto->cantidad_ingreso_inventario <= 0) {
                throw new Exception("La cantidad a ingresar debe ser mayor a cero.");
            }
        }
        if (empty($dto->id_quimico_ingreso_inventario)) {
            throw new Exception('Error al obtener el id del químico.');
        }
    }
}
