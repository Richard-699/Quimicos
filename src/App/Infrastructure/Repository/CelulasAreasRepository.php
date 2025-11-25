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
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_celulas_areas");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([CelulasAreas::class, 'fromArray'], $rows);
    }
}
