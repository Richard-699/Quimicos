<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IQuimicosService;
use Exception;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\QuimicosRepository;
use App\Infrastructure\Repository\UMBRepository;
use App\Domain\DTO\QuimicosDTO;
use App\Domain\Model\QuimicosCelulasAreas;
use App\Infrastructure\Repository\CelulasAreasRepository;
use App\Infrastructure\Repository\PeligrosidadesRepository;
use App\Infrastructure\Repository\QuimicosCelulasAreasRepository;
use App\Shared\Mapper\Mapper;

class QuimicosService implements IQuimicosService {

    private $db;
    private $quimicosRepository;
    private $umbRepository;
    private $celulasAreasRepository;
    private $quimicosCelulasAreasRepository;
    private $peligrosidadesRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->quimicosRepository = new QuimicosRepository($this->db);
        $this->umbRepository = new UMBRepository($this->db);
        $this->celulasAreasRepository = new CelulasAreasRepository($this->db);
        $this->quimicosCelulasAreasRepository = new QuimicosCelulasAreasRepository($this->db);
        $this->peligrosidadesRepository = new PeligrosidadesRepository($this->db);
    }

    public function ongetQuimicos(): array{
        try{
            $this->db->beginTransaction();
            $id_estado = 4; //Activos
            $quimicosBd = $this->quimicosRepository->onget_By__Id_Estado($id_estado);
            $quimicos = Mapper::modelToQuimicosDTO($quimicosBd);

            if(!$quimicos){
                throw new Exception("No se encontraron químicos");
            }

            $umbs = $this->umbRepository->onGet();

            if(!$umbs){
                throw new Exception("No se encontraron umbs");
            }

            foreach ($umbs as $umb) {
                $id = $umb->id_umb ?? null;
                foreach ($quimicos as $quimico) {
                    if ($id == $quimico->id_umb_quimico) {
                        $quimico->umb_quimico = $umb->descripcion_umb;
                    }
                }
            }

            $peligrosidades = $this->peligrosidadesRepository->onGet();

            if(!$peligrosidades){
                throw new Exception("No se encontraron peligrosidades");
            }

            foreach ($peligrosidades as $peligrosidad) {
                $id = $peligrosidad->id_peligrosidad ?? null;
                foreach ($quimicos as $quimico) {
                    if ($id == $quimico->id_peligrosidad_quimico) {
                        $quimico->peligrosidad_quimico = $peligrosidad->descripcion_peligrosidad;
                    }
                }
            }

            $this->db->commit();
            return $quimicos;
        }catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteQuimico($id_quimico): bool{
        try {

            $id_estado = 5;
            $delete_quimico = $this->quimicosRepository->delete_By__Id_Quimico($id_quimico, $id_estado);
            
            if ($delete_quimico === 0) {
                throw new Exception("No se eliminó ningún químico. El ID '$id_quimico' no existe o ya fue eliminado.");
            }

            return true;

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetCelulas(): array{
        try{
            $celulasBd = $this->celulasAreasRepository->onGet();
            $celulas = Mapper::modelToCelulasAreasDTO($celulasBd);

            if(!$celulas){
                throw new Exception("No se encontraron células");
            }
            return $celulas;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetPeligrosidades(): array{
        try{
            $peligrosidadesDb = $this->peligrosidadesRepository->onGet();
            $peligrosidades = Mapper::modelToPeligrosidadesDTO($peligrosidadesDb);

            if(!$peligrosidades){
                throw new Exception("No se encontraron peligrosidades");
            }
            return $peligrosidades;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetQuimicosCelulasAreas_By__Id_Quimico($id): array{
        try{
            $quimicosCelulasAreasBd = $this->quimicosCelulasAreasRepository->onGet_By__Id_Quimico($id);
            $quimicosCelulasAreas = Mapper::modelToQuimicosCelulasAreasDTO($quimicosCelulasAreasBd);

            if(!$quimicosCelulasAreas){
                throw new Exception("No se encontraron datos de células para este quimico");
            }
            return $quimicosCelulasAreas;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetQuimicos_By__Id($id): QuimicosDTO{
        try{
            $quimicobd = $this->quimicosRepository->onGet_By__Id($id);
            $quimico = Mapper::modelToQuimicoDTO($quimicobd);

            if(!$quimico){
                throw new Exception("No se encontró el químico.");
            }

            $umbs = $this->umbRepository->onGet();

            if(!$umbs){
                throw new Exception("No se encontraron umbs");
            }

            foreach ($umbs as $umb) {
                $id = $umb->id_umb ?? null;
                if ($id == $quimico->id_umb_quimico) {
                    $quimico->umb_quimico = $umb->descripcion_umb;
                }
            }

            return $quimico;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetQuimicos_By__Id_Celula($id): array{
        try{
            $quimicosCelulasAreasbd = $this->quimicosCelulasAreasRepository->onGet_By__Id_Celula($id);
            $quimicosCelulasAreas = Mapper::modelToQuimicosCelulasAreasDTO($quimicosCelulasAreasbd);

            if (empty($quimicosCelulasAreas)) {
                throw new Exception("No se encontraron químicos para esta célula.");
            }

            $quimicos = [];

            foreach ($quimicosCelulasAreas as $quimicoCelulaArea) {
                $id_quimico = $quimicoCelulaArea->id_quimico_quimicos;
                
                $quimico = $this->onGetQuimicos_By__Id($id_quimico);
                if (!$quimico) {
                    throw new Exception("No se encontró el químico");
                }
                if($quimico->id_estado_quimico == 4){
                    $quimicos[] = $quimico;
                }
            }

            return $quimicos;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function onGetUmbs(): array{
        try{
            $umbsBd = $this->umbRepository->onGet();
            $umbs = Mapper::modelToUmbsDTO($umbsBd);

            if(!$umbs){
                throw new Exception("No se encontraron umbs");
            }
            return $umbs;
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    public function saveQuimicos(QuimicosDTO $quimicosDTO): bool {
        try {
            $this->db->beginTransaction();

            $quimicos = Mapper::quimicosDTOToModel($quimicosDTO);

            $saveQuimico = $this->quimicosRepository->save($quimicos);
            if (!$saveQuimico) {
                throw new Exception("No se pudo guardar el químico");
            }

            $idQuimico = $quimicosDTO->id_quimico;

            foreach ($quimicosDTO->quimicosCelulasAreasDTO as $idcelulaArea) {
                $QuimicosCelulasAreasModel = new QuimicosCelulasAreas(
                    id_quimico_celula_area: null,
                    id_quimico_quimicos: (string)$idQuimico,
                    id_celulas_areas_quimicos: (int)$idcelulaArea
                );

                $guardarRelacion = $this->quimicosCelulasAreasRepository->save($QuimicosCelulasAreasModel);

                if (!$guardarRelacion) {
                    throw new Exception("No se pudo asociar célula al químico");
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error al guardar el químico con las células: ' . $e->getMessage());
            return false;
        }
    }

    public function updateQuimicos(QuimicosDTO $quimicosDTO): bool
    {
        try {
            $this->db->beginTransaction();

            $quimicos = Mapper::quimicosDTOToModel($quimicosDTO);

            $updateQuimico = $this->quimicosRepository->update_By__Id($quimicos);
            if (!$updateQuimico) {
                throw new Exception("No se pudo actualizar el químico");
            }

            $celulas_areas = $quimicosDTO->quimicosCelulasAreasDTO ?? [];

            if (!empty($celulas_areas)) {

                $idQuimico = $quimicosDTO->id_quimico;

                $delete_quimicosCelulasAreas = $this->quimicosCelulasAreasRepository->delete_By__Id_Quimico($idQuimico);
                if ($delete_quimicosCelulasAreas == 0) {
                    throw new Exception("Error al eliminar las células relacionadas a los químicos");
                }

                foreach ($quimicosDTO->quimicosCelulasAreasDTO as $idcelulaArea) {
                    $QuimicosCelulasAreasModel = new QuimicosCelulasAreas(
                        id_quimico_celula_area: null,
                        id_quimico_quimicos: (string)$idQuimico,
                        id_celulas_areas_quimicos: (int)$idcelulaArea
                    );

                    $guardarRelacion = $this->quimicosCelulasAreasRepository->save($QuimicosCelulasAreasModel);

                    if (!$guardarRelacion) {
                        throw new Exception("No se pudo asociar célula al químico");
                    }
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error al actualizar el químico con las células: ' . $e->getMessage());
            return false;
        }
        throw new \Exception('Not implemented');
    }    
}
?>