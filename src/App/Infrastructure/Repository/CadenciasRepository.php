<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\ICadenciasRepository;
use App\Domain\Model\Cadencias;
use PDO;
use Exception;

class CadenciasRepository implements ICadenciasRepository
{
    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_cadencias ORDER BY cadencia ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([Cadencias::class, 'fromArray'], $rows);
    }
}
