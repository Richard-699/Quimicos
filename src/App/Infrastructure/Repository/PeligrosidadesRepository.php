<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IPeligrosidadesRepository;
use App\Domain\Model\Peligrosidad;
use PDO;

class PeligrosidadesRepository implements IPeligrosidadesRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGet(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_peligrosidad");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Peligrosidad::class, 'fromArray'], $rows);
    }
}
