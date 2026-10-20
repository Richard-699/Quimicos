<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ICelulasAreasRepository;
use App\Domain\Model\CelulasAreas;
use PDO;

class CelulasAreasRepository implements ICelulasAreasRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGet(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM gestion_ambiental_hwi_celulas_areas ORDER BY nombre_celula ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([CelulasAreas::class, 'fromArray'], $rows);
    }

    public function findById(int $id): ?CelulasAreas
    {
        $stmt = $this->db->prepare("SELECT * FROM gestion_ambiental_hwi_celulas_areas WHERE id_celulas_areas = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? CelulasAreas::fromArray($row) : null;
    }
}
