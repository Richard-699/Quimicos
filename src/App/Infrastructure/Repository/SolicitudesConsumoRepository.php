<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ISolicitudesConsumoRepository;
use App\Domain\Model\SolicitudesConsumo;
use PDO;

class SolicitudesConsumoRepository implements ISolicitudesConsumoRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(SolicitudesConsumo $solicitudesConsumo): bool
    {
        $data = $solicitudesConsumo->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_solicitudes_consumo ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
