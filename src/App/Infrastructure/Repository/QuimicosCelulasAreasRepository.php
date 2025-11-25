<?php

namespace App\Infrastructure\Repository;

use App\Application\Interface\Repository\IQuimicosCelulasAreasRepository;
use App\Domain\Model\Quimicos;
use App\Domain\Model\QuimicosCelulasAreas;
use PDO;

class QuimicosCelulasAreasRepository implements IQuimicosCelulasAreasRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(QuimicosCelulasAreas $quimicosCelulasAreas): bool {
        $data = $quimicosCelulasAreas->toArray();
        $columnas = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO quimicos_hwi_quimicos_celulas_areas ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        foreach ($data as $campo => $valor) {
            $stmt->bindValue(":$campo", $valor);
        }
        return $stmt->execute();
    }
}
