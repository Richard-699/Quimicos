<?php

namespace App\Shared\Mapper;

use App\Domain\Model\Administradores;
use App\Domain\DTO\AdministradoresDTO;
use App\Domain\DTO\AlmacenesClasificacionesAlmacenesDTO;
use App\Domain\DTO\AlmacenesDTO;
use App\Domain\DTO\LocalizacionesDTO;
use App\Domain\Model\Permisos;
use App\Domain\DTO\PermisosDTO;
use App\Domain\Model\PermisosAdministradores;
use App\Domain\DTO\PermisosAdministradoresDTO;
use App\Domain\Model\Almacenes;
use App\Domain\Model\Localizaciones;
use App\Domain\Model\AlmacenesLocalizaciones;
use App\Domain\DTO\AlmacenesLocalizacionesDTO;
use App\Domain\DTO\ConteoDTO;
use App\Domain\DTO\CronogramaDTO;
use App\Domain\DTO\ExactitudDTO;
use App\Domain\DTO\PartNumbersDTO;
use App\Domain\Model\PartNumbers;
use App\Domain\DTO\GruposDTO;
use App\Domain\DTO\HistoricoStockDTO;
use App\Domain\DTO\InformacionSapMb52DTO;
use App\Domain\DTO\InformacionSapWMDTO;
use App\Domain\Model\ClasificacionAlmacenes;
use App\Domain\Model\Cronograma;
use App\Domain\Model\Grupos;
use App\Domain\Model\AlmacenesClasificacionesAlmacenes;
use App\Domain\Model\HistoricoStock;
use App\Domain\Model\InformacionSapMb52;
use App\Domain\Model\InformacionSapWM;
use App\Domain\Model\HistoricoWM;
use App\Domain\DTO\HistoricoWMDTO;
use App\Domain\Model\HistoricoMB52;
use App\Domain\DTO\HistoricoMB52DTO;
use App\Domain\DTO\StockDTO;
use App\Domain\Model\Conteo;
use App\Domain\Model\Exactitud;
use App\Domain\Model\Stock;

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

    public static function modelToPermisosDTO(Permisos $model): PermisosDTO
    {
        return new PermisosDTO(
            id_permiso: $model->id_permiso,
            tipo_permiso: $model->tipo_permiso
        );
    }

    public static function permisosDTOToModel(PermisosDTO $dto): Permisos
    {
        return new Permisos(
            $dto->id_permiso,
            $dto->tipo_permiso
        );
    }

    public static function modelToPermisosAdministradoresDTO(PermisosAdministradores $model): PermisosAdministradoresDTO
    {
        return new PermisosAdministradoresDTO(
            id_permisos_administradores: $model->id_permisos_administradores,
            id_permiso_permisos: $model->id_permiso_permisos,
            id_administrador_permisos: $model->id_administrador_permisos
        );
    }

    public static function permisosAdministradoresDTOToModel(PermisosAdministradoresDTO $dto): PermisosAdministradores
    {
        return new PermisosAdministradores(
            $dto->id_permisos_administradores,
            $dto->id_permiso_permisos,
            $dto->id_administrador_permisos
        );
    }

    public static function modelToAlmacenesDTO(Almacenes $model): AlmacenesDTO
    {
        return new AlmacenesDTO(
            id_almacen: $model->id_almacen,
            codigo_sap: $model->codigo_sap,
            descripcion_almacen: $model->descripcion_almacen
        );
    }

    public static function AlmacenesDTOToModel(AlmacenesDTO $dto): Almacenes
    {
        return new Almacenes(
            $dto->id_almacen,
            $dto->codigo_sap,
            $dto->descripcion_almacen
        );
    }

    public static function modelToLocalizacionesDTO(Localizaciones $model): LocalizacionesDTO
    {
        return new LocalizacionesDTO(
            id_localizacion: $model->id_localizacion,
            id_tipo_localizacion_localizaciones: $model->id_tipo_localizacion_localizaciones,
            descripcion_localizacion: $model->descripcion_localizacion,
            id_tipo_almacenamientos_localizaciones: $model->id_tipo_almacenamientos_localizaciones
        );
    }

    public static function LocalizacionesDTOToModel(LocalizacionesDTO $dto): Localizaciones
    {
        return new Localizaciones(
            $dto->id_localizacion,
            $dto->id_tipo_localizacion_localizaciones,
            $dto->descripcion_localizacion,
            $dto->id_tipo_almacenamientos_localizaciones
        );
    }

    public static function modelToAlmacenesLocalizacionesDTO(AlmacenesLocalizaciones $model): AlmacenesLocalizacionesDTO
    {
        return new AlmacenesLocalizacionesDTO(
            id_localizaciones_almacenes: $model->id_localizaciones_almacenes,
            id_almacen: $model->id_almacen,
            id_localizacion_localizaciones: $model->id_localizacion_localizaciones
        );
    }

    public static function AlmacenesLocalizacionesDTOToModel(AlmacenesLocalizacionesDTO $dto): AlmacenesLocalizaciones
    {
        return new AlmacenesLocalizaciones(
            $dto->id_localizaciones_almacenes,
            $dto->id_almacen,
            $dto->id_localizacion_localizaciones
        );
    }

    public static function modelToPartNumbersDTO(PartNumbers $model): PartNumbersDTO
    {
        return new PartNumbersDTO(
            id_partnumber: $model->id_partnumber,
            partnumber: $model->partnumber,
            descripcion_breve: $model->descripcion_breve,
            id_umb_partnumber: $model->id_umb_partnumber,
            nombre_interno: $model->nombre_interno,
            id_grupo_partnumber: $model->id_grupo_partnumber,
            id_plataforma_partnumber: $model->id_plataforma_partnumber
        );
    }

    public static function partnumbersDTOToModel(PartNumbersDTO $dto): PartNumbers
    {
        return new PartNumbers(
            $dto->id_partnumber,
            $dto->partnumber,
            $dto->descripcion_breve,
            $dto->id_umb_partnumber,
            $dto->nombre_interno,
            $dto->id_grupo_partnumber,
            $dto->id_plataforma_partnumber
        );
    }

    public static function modelToGruposDTO(Grupos $model): GruposDTO
    {
        return new GruposDTO(
            id_grupo: $model->id_grupo,
            descripcion_grupo: $model->descripcion_grupo,
            fecha_programacion_grupo: $model->fecha_programacion_grupo,
            informacion_migrada_sap_grupo: $model->informacion_migrada_sap_grupo
        );
    }

    public static function GruposDTOToModel(GruposDTO $dto): Grupos
    {
        return new Grupos(
            $dto->id_grupo,
            $dto->descripcion_grupo,
            $dto->fecha_programacion_grupo,
            $dto->informacion_migrada_sap_grupo
        );
    }


    public static function modelToCronogramaDTO(Cronograma $model): CronogramaDTO
    {
        return new CronogramaDTO(
            id_cronograma: $model->id_cronograma,
            fecha_cronograma: $model->fecha_cronograma,
            id_grupo_cronograma: $model->id_grupo_cronograma,
            id_estado_cronograma: $model->id_estado_cronograma,
            id_administrador_cronograma: $model->id_administrador_cronograma
        );
    }

    public static function CronogramaDTOToModel(CronogramaDTO $dto): Cronograma
    {
        return new Cronograma(
            $dto->id_cronograma,
            $dto->fecha_cronograma,
            $dto->id_grupo_cronograma,
            $dto->id_estado_cronograma,
            $dto->id_administrador_cronograma
        );
    }

    public static function modelToAlmacenesClasificacionesAlmacenesDTO(AlmacenesClasificacionesAlmacenes $model): AlmacenesClasificacionesAlmacenesDTO
    {
        return new AlmacenesClasificacionesAlmacenesDTO(
            id_almacenes_clasificaciones_almacenes: $model->id_almacenes_clasificaciones_almacenes,
            id_almacen_almacenes_clasificaciones_almacenes: $model->id_almacen_almacenes_clasificaciones_almacenes,
            id_clasificacion_almacenes_almacenes_clasificaciones_almacenes: $model->id_clasificacion_almacenes_almacenes_clasificaciones_almacenes
        );
    }

    public static function ClasificacionesAlmacenesDTOToModel(AlmacenesClasificacionesAlmacenesDTO $dto): AlmacenesClasificacionesAlmacenes
    {
        return new AlmacenesClasificacionesAlmacenes(
            $dto->id_almacenes_clasificaciones_almacenes,
            $dto->id_almacen_almacenes_clasificaciones_almacenes,
            $dto->id_clasificacion_almacenes_almacenes_clasificaciones_almacenes
        );
    }

    public static function modelToInformacionSapMB52DTO(InformacionSapMB52 $model): InformacionSapMB52DTO
    {
        return new InformacionSapMB52DTO(
            id_informacion_sap_mb52: $model->id_informacion_sap_mb52,
            fecha_registro_informacion_sap_mb52: $model->fecha_registro_informacion_sap_mb52,
            id_part_number_informacion_sap_mb52: $model->id_part_number_informacion_sap_mb52,
            cantidad_informacion_sap_mb52: $model->cantidad_informacion_sap_mb52,
            id_almacen_informacion_sap_mb52: $model->id_almacen_informacion_sap_mb52,
            id_grupo_informacion_sap_mb52: $model->id_grupo_informacion_sap_mb52
        );
    }

    public static function InformacionSapMB52DTOToModel(InformacionSapMB52DTO $dto): InformacionSapMB52
    {
        return new InformacionSapMB52(
            $dto->id_informacion_sap_mb52,
            $dto->fecha_registro_informacion_sap_mb52,
            $dto->id_part_number_informacion_sap_mb52,
            $dto->cantidad_informacion_sap_mb52,
            $dto->id_almacen_informacion_sap_mb52,
            $dto->id_grupo_informacion_sap_mb52
        );
    }

    public static function modelToInformacionSapWMDTO(InformacionSapWM $model): InformacionSapWMDTO
    {
        return new InformacionSapWMDTO(
            id_informacion_sap_wm: $model->id_informacion_sap_wm,
            id_part_number_informacion_sap_wm: $model->id_part_number_informacion_sap_wm,
            id_localizacion_informacion_sap_wm: $model->id_localizacion_informacion_sap_wm,
            id_grupo_informacion_sap_wm: $model->id_grupo_informacion_sap_wm,
            id_informacion_sap_mb52_informacion_sap_wm: $model->id_informacion_sap_mb52_informacion_sap_wm,
            stock_disponible_sap_informacion_sap_wm: $model->stock_disponible_sap_informacion_sap_wm,
            stock_entrada_sap_informacion_sap_wm: $model->stock_entrada_sap_informacion_sap_wm,
            stock_salida_sap_informacion_sap_wm: $model->stock_salida_sap_informacion_sap_wm
        );
    }

    public static function InformacionSapWMDTOToModel(InformacionSapWMDTO $dto): InformacionSapWM
    {
        return new InformacionSapWM(
            $dto->id_informacion_sap_wm,
            $dto->id_part_number_informacion_sap_wm,
            $dto->id_localizacion_informacion_sap_wm,
            $dto->id_grupo_informacion_sap_wm,
            $dto->id_informacion_sap_mb52_informacion_sap_wm,
            $dto->stock_disponible_sap_informacion_sap_wm,
            $dto->stock_entrada_sap_informacion_sap_wm,
            $dto->stock_salida_sap_informacion_sap_wm
        );
    }

    public static function modelToHistoricoStockDTO(HistoricoStock $model): HistoricoStockDTO
    {
        return new HistoricoStockDTO(
            id_historico_stock: $model->id_historico_stock,
            fecha_historico_stock: $model->fecha_historico_stock,
            cantidad_historico_stock: $model->cantidad_historico_stock,
            id_almacen_historico_stock: $model->id_almacen_historico_stock,
            id_localizacion_historico_stock: $model->id_localizacion_historico_stock,
            id_informacion_sap_mb52_historico_stock: $model->id_informacion_sap_mb52_historico_stock,
            id_partnumber_historico_stock: $model->id_partnumber_historico_stock,
            id_novedad_historico_stock: $model->id_novedad_historico_stock,
            observaciones_novedad_historico_stock: $model->observaciones_novedad_historico_stock,
            id_grupo_historico_stock: $model->id_grupo_historico_stock,
            id_conteo_stock: $model->id_conteo_stock,
            fecha_hora_stock: $model->fecha_hora_stock,
            id_administrador_stock: $model->id_administrador_stock

        );
    }

    public static function HistoricoStockDTOToModel(HistoricoStockDTO $dto): HistoricoStock
    {
        return new HistoricoStock(
            $dto->id_historico_stock,
            $dto->fecha_historico_stock,
            $dto->cantidad_historico_stock,
            $dto->id_almacen_historico_stock,
            $dto->id_localizacion_historico_stock,
            $dto->id_informacion_sap_mb52_historico_stock,
            $dto->id_partnumber_historico_stock,
            $dto->id_novedad_historico_stock,
            $dto->observaciones_novedad_historico_stock,
            $dto->id_grupo_historico_stock,
            $dto->id_conteo_stock,
            $dto->fecha_hora_stock,
            $dto->id_administrador_stock,
        );
    }

    public static function modelToHistoricoWMDTO(HistoricoWM $model): HistoricoWMDTO
    {
        return new HistoricoWMDTO(
            id_historico_wm: $model->id_historico_wm,
            fecha_historico_wm: $model->fecha_historico_wm,
            stock_disponible_historico_wm: $model->stock_disponible_historico_wm,
            stock_entrada_historico_wm: $model->stock_entrada_historico_wm,
            stock_salida_historico_wm: $model->stock_salida_historico_wm,
            id_localizacion_historico_wm: $model->id_localizacion_historico_wm,
            id_partnumber_historico_wm: $model->id_partnumber_historico_wm,
            id_grupo_historico_wm: $model->id_grupo_historico_wm,
            id_informacion_sap_mb52_historico_wm: $model->id_informacion_sap_mb52_historico_wm
        );
    }

    public static function HistoricoWMDTOToModel(HistoricoWMDTO $dto): HistoricoWM
    {
        return new HistoricoWM(
            $dto->id_historico_wm,
            $dto->fecha_historico_wm,
            $dto->stock_disponible_historico_wm,
            $dto->stock_entrada_historico_wm,
            $dto->stock_salida_historico_wm,
            $dto->id_localizacion_historico_wm,
            $dto->id_partnumber_historico_wm,
            $dto->id_grupo_historico_wm,
            $dto->id_informacion_sap_mb52_historico_wm
        );
    }

    public static function modelToHistoricoMB52DTO(HistoricoMB52 $model): HistoricoMB52DTO
    {
        return new HistoricoMB52DTO(
            id_historico_mb52: $model->id_historico_mb52,
            id_informacion_sap_mb52_historico_mb52: $model->id_informacion_sap_mb52_historico_mb52,
            fecha_historico_mb52: $model->fecha_historico_mb52,
            cantidad_historico_mb52: $model->cantidad_historico_mb52,
            fechaRegistro_historico_mb52: $model->fechaRegistro_historico_mb52,
            id_part_number_historico_mb52: $model->id_part_number_historico_mb52,
            id_almacen_historico_mb52: $model->id_almacen_historico_mb52,
            id_grupo_historico_mb52: $model->id_grupo_historico_mb52
        );
    }

    public static function HistoricoMB52DTOToModel(HistoricoMB52DTO $dto): HistoricoMB52
    {
        return new HistoricoMB52(
            $dto->id_historico_mb52,
            $dto->id_informacion_sap_mb52_historico_mb52,
            $dto->fecha_historico_mb52,
            $dto->cantidad_historico_mb52,
            $dto->fechaRegistro_historico_mb52,
            $dto->id_part_number_historico_mb52,
            $dto->id_almacen_historico_mb52,
            $dto->id_grupo_historico_mb52
        );
    }

    public static function modelTOStockDTO(Stock $model): StockDTO
    {
        return new StockDTO(
            id_stock: $model->id_stock,
            id_partnumber_stock: $model->id_partnumber_stock,
            id_almacen_stock: $model->id_almacen_stock,
            id_localizacion_stock: $model->id_localizacion_stock,
            cantidad_stock: $model->cantidad_stock,
            id_informacion_sap_mb52_stock: $model->id_informacion_sap_mb52_stock,
            id_novedad_stock: $model->id_novedad_stock,
            observaciones_novedad_stock: $model->observaciones_novedad_stock,
            id_grupo_stock: $model->id_grupo_stock,
            id_conteo_stock: $model->id_conteo_stock,
            fecha_hora_stock: $model->fecha_hora_stock,
            id_administrador_stock: $model->id_administrador_stock
        );
    }

    public static function StockDTOToModel(StockDTO $dto): Stock
    {
        return new Stock(
            $dto->id_stock,
            $dto->id_partnumber_stock,
            $dto->id_almacen_stock,
            $dto->id_localizacion_stock,
            $dto->cantidad_stock,
            $dto->id_informacion_sap_mb52_stock,
            $dto->id_novedad_stock,
            $dto->observaciones_novedad_stock,
            $dto->id_grupo_stock,
            $dto->id_conteo_stock,
            $dto->fecha_hora_stock,
            $dto->id_administrador_stock
        );
    }

    public static function modelTOConteoDTO(Conteo $model): ConteoDTO
    {
        return new ConteoDTO(
            id_conteo: $model->id_conteo,
            id_grupo_conteo: $model->id_grupo_conteo,
            id_encargado_conteo: $model->id_encargado_conteo,
            fecha_hora_inicio_conteo: $model->fecha_hora_inicio_conteo,
            fecha_hora_final_conteo: $model->fecha_hora_final_conteo,
            observaciones_conteo: $model->observaciones_conteo,
            estado_conteo: $model->estado_conteo,
            observacion_final_conteo: $model->observacion_final_conteo,
        );
    }

    public static function ConteoDTOToModel(ConteoDTO $dto): Conteo
    {
        return new Conteo(
            $dto->id_conteo,
            $dto->id_grupo_conteo,
            $dto->id_encargado_conteo,
            $dto->fecha_hora_inicio_conteo,
            $dto->fecha_hora_final_conteo,
            $dto->observaciones_conteo,
            $dto->estado_conteo,
            $dto->observacion_final_conteo
        );
    }


    public static function modelTOExactitudDTO(Exactitud $model): ExactitudDTO
    {
        return new ExactitudDTO(
            id_exactitud: $model->id_exactitud,
            partnumber_exactitud: $model->partnumber_exactitud,
            descripcion_partnumber_exactitud: $model->descripcion_partnumber_exactitud,
            tipo_almacen_exactitud: $model->tipo_almacen_exactitud,
            area_almacenamiento_exactitud: $model->area_almacenamiento_exactitud,
            localizacion_exactitud: $model->localizacion_exactitud,
            coincide_exactitud: $model->coincide_exactitud,
            novedad_exactitud: $model->novedad_exactitud,
            descripcion_novedad_exactitud: $model->descripcion_novedad_exactitud,
            fecha_hora_migracion_exactitud: $model->fecha_hora_migracion_exactitud,
            id_administrador: $model->id_administrador
        );
    }

    public static function ExactitudDTOToModel(ExactitudDTO $dto): Exactitud
    {
        return new Exactitud(
            $dto->id_exactitud,
            $dto->partnumber_exactitud,
            $dto->descripcion_partnumber_exactitud,
            $dto->tipo_almacen_exactitud,
            $dto->area_almacenamiento_exactitud,
            $dto->localizacion_exactitud,
            $dto->coincide_exactitud,
            $dto->novedad_exactitud,
            $dto->descripcion_novedad_exactitud,
            $dto->fecha_hora_migracion_exactitud,
            $dto->id_administrador
        );
    }
}
