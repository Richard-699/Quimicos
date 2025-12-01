<?php

namespace App\Application\Service;

use App\Application\Interface\Service\ISolicitudesQuimicosService;
use Exception;
use App\Infrastructure\Database\Connection;
use App\Domain\DTO\SolicitudesConsumoDTO;
use App\Infrastructure\Repository\SolicitudesConsumoRepository;
use App\Shared\Mapper\Mapper;

class SolicitudesConsumoService implements ISolicitudesQuimicosService {

    private $db;
    private $solicitudesConsumoRepository;

    public function __construct() {
        $this->db = (new Connection())->dbQuimicosHwi;

        $this->solicitudesConsumoRepository = new SolicitudesConsumoRepository($this->db);
    }

    public function saveSolicitudQuimico(SolicitudesConsumoDTO $solicitudesConsumoDTO): bool {
        try {
            $this->db->beginTransaction();

            $solicitudesConsumo = Mapper::solicitudesConsumoDTOToModel($solicitudesConsumoDTO);

            $saveSolicitudConsumo = $this->solicitudesConsumoRepository->save($solicitudesConsumo);
            if (!$saveSolicitudConsumo) {
                throw new Exception("No se pudo registrar la solicitud");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error al guardar la solicitud: ' . $e->getMessage());
            return false;
        }
    }
   
}
?>