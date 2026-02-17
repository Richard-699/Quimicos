<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IUMBRepository;
use App\Domain\Model\UMB;
use PDO;

class UMBRepository implements IUMBRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function onGet(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM quimicos_hwi_umbs ORDER BY ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([UMB::class, 'fromArray'], $rows);
    }
}
