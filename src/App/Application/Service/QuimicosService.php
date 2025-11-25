<?php

namespace App\Application\Service;

use App\Application\Interface\Service\IQuimicosService;
use Exception;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\QuimicosRepository;
use App\Infrastructure\Repository\UMBRepository;
use App\Domain\DTO\QuimicosDTO;
use App\Domain\DTO\UMBDTO;
use App\Infrastructure\Repository\CelulasAreasRepository;
use App\Shared\Mapper\Mapper;

class QuimicosService implements IQuimicosService {

    private $db;
    private $quimicosRepository;
    private $umbRepository;
    private $celulasAreasRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->quimicosRepository = new QuimicosRepository($this->db);
        $this->umbRepository = new UMBRepository($this->db);
        $this->celulasAreasRepository = new CelulasAreasRepository($this->db);
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

    public function onGetUmbs(): array
    {
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
}


?>