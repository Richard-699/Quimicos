<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ITanquesAbastecimientoAguaRepository;
use App\Domain\DTO\TanquesAbastecimientoAguaDTO;
use PDO;

class TanquesAbastecimientoAguaRepository implements ITanquesAbastecimientoAguaRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGetTanques(): array
    {
        $query = "SELECT * FROM gestion_ambiental_hwi_tanques_abastecimiento_agua ORDER BY tanque_abastecimiento_agua ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($rows as $row) {
            $result[] = new TanquesAbastecimientoAguaDTO(
                $row['id_tanque_abastecimiento_agua'],
                $row['tanque_abastecimiento_agua']
            );
        }
        
        return $result;
    }
}
